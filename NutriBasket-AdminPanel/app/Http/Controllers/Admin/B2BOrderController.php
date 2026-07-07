<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Item;
use App\Models\User;
use App\Models\DeliveryMan;
use App\Models\B2BPackager;
use App\Models\B2BClient;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use App\Exports\StockDifferenceExport;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\CentralLogics\Helpers;

class B2BOrderController extends Controller
{
    public function list($status, Request $request)
    {
        $processing_view_type = $request->get('processing_view_type', 'by_customer');

        // Get orders with detailed information like normal orders
        $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('users.user_type', 'b2b')
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'store', 'details.item'])
            ->when($status !== 'all' && $status !== 'on_the_way', function($q) use ($status) {
                $q->where('order_status', $status);
            })
            ->when($status == 'pending', function ($query) {
                return $query->Pending();
            })
            ->when($status == 'processing', function ($query) {
                return $query->where('order_status', 'processing');
            })
            ->when($status == 'accepted', function ($query) {
                return $query->where('order_status', 'accepted');
            })
            ->when($status == 'packaging', function ($query) {
                return $query->where('order_status', 'packaging');
            })
            ->when($status == 'delivered', function ($query) {
                return $query->Delivered();
            })
            ->when($status == 'acknowledged', function ($query) {
                return $query->Acknowledged();
            })
            ->when($status == 'canceled', function ($query) {
                return $query->Canceled();
            })
            ->when($status == 'failed', function ($query) {
                return $query->failed();
            })
            ->when($status == 'on_the_way', function ($query) {
                return $query->where('order_status', 'picked_up');
            })
            ->orderBy('orders.created_at', 'desc')
            ->select('orders.*')
            ->paginate(20);

        $total = $orders->total();
        
        // Initialize arrays for different views
        $stock_diff_items = [];
        $total_items = [];
        $customer_items = [];
        $inventory_items = [];
        $delivery_men = DeliveryMan::active()->get();
        $packagers = B2BPackager::active()->get();

        // Processing stage data - now handles both inventory management and customer acceptance
        if ($status == 'processing') {
            // Stock Difference - for Excel export to mandi
            if ($processing_view_type == 'stock_difference') {
                $ordered = DB::table('order_details')
                    ->join('orders', 'order_details.order_id', '=', 'orders.id')
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->where('orders.order_status', 'processing')
                    ->where('users.user_type', 'b2b')
                    ->select('order_details.item_id', DB::raw('SUM(order_details.quantity) as total_ordered'))
                    ->groupBy('order_details.item_id')
                    ->get()
                    ->keyBy('item_id');

                $items = Item::select('id', 'name', 'stock')->get();

                foreach ($items as $item) {
                    $total_ordered = isset($ordered[$item->id]) ? $ordered[$item->id]->total_ordered : 0;
                    $extra_needed = max(0, $total_ordered - $item->stock);
                    $stock_diff_items[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'total_ordered' => $total_ordered,
                        'stock' => $item->stock,
                        'extra_needed' => $extra_needed,
                    ];
                }
            }

            // By Customer view
            if ($processing_view_type == 'by_customer') {
                $customer_items = $orders->groupBy('user_id')->map(function ($customer_orders) {
                    return [
                        'customer' => $customer_orders->first()->customer,
                        'orders' => $customer_orders,
                        'total_amount' => $customer_orders->sum('order_amount'),
                        'total_items' => $customer_orders->sum(function ($order) {
                            return $order->details->sum('quantity');
                        })
                    ];
                });
            }

            // Get delivery men and packagers
            $delivery_men = DeliveryMan::active()->get();
            $packagers = B2BPackager::active()->get();
        }

        return view('admin-views.order.list-b2b', compact(
            'orders', 
            'total', 
            'status', 
            'stock_diff_items', 
            'total_items', 
            'customer_items', 
            'inventory_items',
            'delivery_men',
            'packagers',
            'processing_view_type'
        ));
    }

    public function filter(Request $request)
    {
        return redirect()->route('admin.b2b-order.list', ['status' => 'all']);
    }

    public function stockDifferenceExport($type)
    {
        $ordered = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.order_status', 'processing')
            ->where('users.user_type', 'b2b')
            ->select('order_details.item_id', DB::raw('SUM(order_details.quantity) as total_ordered'))
            ->groupBy('order_details.item_id')
            ->get()
            ->keyBy('item_id');

        // Get items with their units, only those with ordered quantity > 0
        $items = Item::with('unit')
            ->whereIn('id', $ordered->keys())
            ->get();

        $report = [];
        $serial = 1;
        foreach ($items as $item) {
            $total_ordered = isset($ordered[$item->id]) ? (float)$ordered[$item->id]->total_ordered : 0.0;
            $stock = (float)$item->stock;
            $extra_needed = max(0.0, $total_ordered - $stock);
            $unit = $item->unit ? $item->unit->unit : '';
            
            // Ensure 0 values are explicitly set as 0.0
            $report[] = [
                '#' => $serial++,
                'Item ID' => $item->id,
                'Item Name' => $item->name,
                'Unit' => $unit,
                'Ordered Quantity' => $total_ordered == 0 ? 0.0 : $total_ordered,
                'In Stock' => $stock == 0 ? 0.0 : $stock,
                'Extra Needed' => $extra_needed == 0 ? 0.0 : $extra_needed,
            ];
        }

        $currentDate = now()->format('Y-m-d');
        $filename = "Stock_Difference_Report_{$currentDate}";

        if ($type == 'csv') {
            $filename .= '.csv';
            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$filename",
            ];
            $handle = fopen('php://output', 'w');
            ob_start();
            
            // Write headers
            if (!empty($report)) {
                fputcsv($handle, array_keys($report[0]));
            }
            
            // Write data rows
            foreach ($report as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            $content = ob_get_clean();
            return response($content, 200, $headers);
        } else {
            $filename .= '.xlsx';
            return Excel::download(new StockDifferenceExport($report), $filename);
        }
    }

    public function export(Request $request)
    {
        $file_type = $request->get('file_type', 'excel');
        $status = $request->get('status', 'all');
        
        $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('users.user_type', 'b2b')
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'store', 'details.item'])
            ->when($status !== 'all', function($q) use ($status) {
                $q->where('order_status', $status);
            })
            ->orderBy('orders.created_at', 'desc')
            ->select('orders.*')
            ->get();

        $export_data = [];
        foreach ($orders as $order) {
            $export_data[] = [
                'Order ID' => $order->id,
                'Customer Name' => $order->customer ? $order->customer->f_name . ' ' . $order->customer->l_name : 'N/A',
                'Customer Phone' => $order->customer ? $order->customer->phone : 'N/A',
                'Store' => $order->store ? $order->store->name : 'N/A',
                'Order Amount' => $order->order_amount,
                'Order Status' => $order->order_status,
                'Payment Status' => $order->payment_status,
                'Order Date' => $order->created_at->format('Y-m-d H:i:s'),
                'Items Count' => $order->details->count(),
            ];
        }

        if ($file_type == 'csv') {
            $filename = 'b2b_orders_' . $status . '.csv';
            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$filename",
            ];
            $handle = fopen('php://output', 'w');
            ob_start();
            fputcsv($handle, array_keys($export_data[0]));
            foreach ($export_data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            $content = ob_get_clean();
            return response($content, 200, $headers);
        } else {
            return Excel::download(new ArrayExport($export_data), 'b2b_orders_' . $status . '.xlsx');
        }
    }

    public function details($id)
    {
        $order = Order::with(['customer', 'store', 'details.item'])
            ->where('id', $id)
            ->whereHas('customer', function($q) {
                $q->where('user_type', 'b2b');
            })
            ->firstOrFail();

        return view('admin-views.order.details-b2b', compact('order'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('users.user_type', 'b2b')
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'store'])
            ->where(function($q) use ($search) {
                $q->where('orders.id', 'like', "%$search%");
            })
            ->select('orders.*')
            ->paginate(20);

        $view = view('admin-views.order.partials._b2b_order_rows', compact('orders'))->render();

        return response()->json(['view' => $view]);
    }

    public function processAllOrders(Request $request)
    {
        $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('users.user_type', 'b2b')
            ->with('details.item')
            ->where('order_status', 'pending')
            ->select('orders.*')
            ->get();

        foreach ($orders as $order) {
            $order->order_status = 'processing';
            $order->packaging_time = now()->timestamp;
            $order->save();
        }

        Toastr::success('All B2B pending orders moved to processing!');
        return back();
    }

    public function updateInventory(Request $request)
    {
        $request->validate([
            'inventory_updates' => 'required|array',
            'inventory_updates.*.item_id' => 'required|exists:items,id',
            'inventory_updates.*.add_stock' => 'required|integer|min:0',
        ]);

        foreach ($request->inventory_updates as $update) {
            $item = Item::find($update['item_id']);
            $addStock = isset($update['add_stock']) ? (int)$update['add_stock'] : 0;
            $item->stock += $addStock;
            $item->save();
        }

        Toastr::success('Inventory updated successfully!');
        return back();
    }

    public function acceptOrderByCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
        ]);
        
        $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->where('order_status', 'accepted')
            ->where('user_id', $request->customer_id)
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->get();

        foreach ($orders as $order) {
            // Update order status
            $order->order_status = 'confirmed';
            $order->save();

            // Reduce inventory for each item in the order
            foreach ($order->details as $detail) {
                $item = Item::find($detail->item_id);
                if ($item) {
                    $item->stock = max(0, $item->stock - $detail->quantity);
                    $item->save();
                }
            }
        }

        Toastr::success('Orders accepted and inventory updated!');
        return back();
    }

    public function updateItemStock(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'new_stock' => 'required|integer|min:0',
        ]);
        
        $item = Item::find($request->item_id);
        $item->stock = $request->new_stock;
        $item->save();
        
        Toastr::success('Stock updated!');
        return back();
    }

    public function generateInvoice($id)
    {
        $order = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'store', 'details.item'])
            ->where('id', $id)
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->firstOrFail();

        return view('admin-views.order.invoice-b2b', compact('order'));
    }

    public function printInvoice($id)
    {
        $order = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'store', 'details.item'])
            ->where('id', $id)
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->firstOrFail();

        return view('admin-views.order.invoice-print-b2b', compact('order'))->render();
    }

    public function updateDeliveryManAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'delivery_man_id' => 'nullable|exists:delivery_men,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $client = $user->b2bClients->first();

        if ($client) {
            $client->default_deliveryman_id = $request->delivery_man_id;
            $client->save();
        } else {
            // Create a new client if none exists
            $client = B2BClient::create([
                'name' => $user->f_name . ' ' . $user->l_name,
                'default_deliveryman_id' => $request->delivery_man_id,
            ]);
            $user->b2bClients()->attach($client->id);
        }

        Toastr::success('Delivery man assignment updated successfully!');
        return back();
    }

    public function updatePackagerAssignment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'packager_id' => 'nullable|exists:b2b_packagers,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $client = $user->b2bClients->first();

        if ($client) {
            $client->default_packager_id = $request->packager_id;
            $client->save();
        } else {
            // Create a new client if none exists
            $client = B2BClient::create([
                'name' => $user->f_name . ' ' . $user->l_name,
                'default_packager_id' => $request->packager_id,
            ]);
            $user->b2bClients()->attach($client->id);
        }

        Toastr::success('Packager assignment updated successfully!');
        return back();
    }

    public function processToPackaging(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Get all accepted orders for this user
        $orders = Order::where('user_id', $request->user_id)
            ->where('order_status', 'accepted')
            ->whereHas('customer', function($q) {
                $q->where('user_type', 'b2b');
            })
            ->get();

        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $order->order_status = 'packaging';
                $order->save();
            }
            Toastr::success($orders->count() . ' order(s) moved to packaging stage!');
        } else {
            Toastr::warning('No accepted orders found for this user!');
        }

        return back();
    }

    public function processSelected(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        if (!empty($orderIds)) {
            // Update all selected orders from 'pending' to 'processing'
            \App\Models\Order::whereIn('id', $orderIds)
                ->where('order_status', 'pending')
                ->update(['order_status' => 'processing']);
            \Brian2694\Toastr\Facades\Toastr::success('Selected orders processed successfully!');
        } else {
            \Brian2694\Toastr\Facades\Toastr::warning('No orders selected!');
        }
        return redirect()->back();
    }

    public function edit($id)
    {
        $order = \App\Models\Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'store', 'details.item'])
            ->where('id', $id)
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->firstOrFail();

        return view('admin-views.order.edit-b2b', compact('order'));
    }

    public function delete($id)
    {
        $order = \App\Models\Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->where('id', $id)
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->firstOrFail();

        $order->delete();

        \Brian2694\Toastr\Facades\Toastr::success('Order deleted successfully!');
        return redirect()->back();
    }

    public function processCustomers(Request $request)
    {
        $action = $request->input('action');
        if ($action === 'all') {
           // Process all customers' orders in 'processing' state
           
            $orders = \App\Models\Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
                ->where('order_status', 'processing')
                ->whereHas('customer', function($q) {
                    $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
                })
                ->get();
        } else {
 // Process only selected customers' orders in 'processing' state
 $customerIds = $request->input('order_ids', []);
            $orders = \App\Models\Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
                ->where('order_status', 'processing')
            ->whereIn('id', $customerIds)
            ->whereHas('customer', function($q) {
                    $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
                })
                ->get();
        }
        foreach ($orders as $order) {
            $order->order_status = 'preparing';
            $order->save();
        }
        \Brian2694\Toastr\Facades\Toastr::success('Orders processed to preparing state successfully!');
        return redirect()->back();
    }

    public function acceptCustomers(Request $request)
    {
        try {
            $customerId = $request->input('customer_id');
            
            // Get all processing orders for this customer
            $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->where('users.user_type', 'b2b')
                ->where('orders.order_status', 'processing')
                ->where('orders.user_id', $customerId)
                ->with(['details.item'])
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No processing orders found for this customer'
                ]);
            }

            DB::beginTransaction();

            foreach ($orders as $order) {
                // Update order status to accepted
                $order->update(['order_status' => 'accepted']);

                // Subtract items from inventory
                foreach ($order->details as $detail) {
                    if ($detail->item) {
                        $item = $detail->item;
                        $quantity = $detail->quantity;
                        
                        // Check if enough stock
                        if ($item->stock >= $quantity) {
                            $item->decrement('stock', $quantity);
                        } else {
                            DB::rollback();
                            return response()->json([
                                'success' => false,
                                'message' => "Insufficient stock for {$item->name}. Available: {$item->stock}, Required: {$quantity}"
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orders accepted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error accepting orders: ' . $e->getMessage()
            ]);
        }
    }

    public function acceptSelected(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            
            if (empty($orderIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders selected'
                ]);
            }

            // Get selected processing orders - using same query structure as list method
            $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->where('users.user_type', 'b2b')
                ->where('orders.order_status', 'processing')
                ->whereIn('orders.id', $orderIds)
                ->with(['details.item'])
                ->select('orders.*')
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid processing orders found'
                ]);
            }

            $processedOrders = [];
            $skippedOrders = [];
            $insufficientStockItems = [];

            foreach ($orders as $order) {
                $canProcessOrder = true;
                $orderInsufficientItems = [];

                // Check stock availability for all items in this order
                foreach ($order->details as $detail) {
                    if ($detail->item) {
                        $item = $detail->item;
                        $quantity = $detail->quantity;
                        
                        // Check if enough stock
                        if ($item->stock < $quantity) {
                            $canProcessOrder = false;
                            $orderInsufficientItems[] = [
                                'item_name' => $item->name,
                                'available' => $item->stock,
                                'required' => $quantity
                            ];
                        }
                    }
                }

                if ($canProcessOrder) {
                    // Process this order - use individual transaction
                    DB::beginTransaction();
                    try {
                        // Update order status to accepted
                        $order->update(['order_status' => 'accepted']);

                        // Subtract items from inventory
                        foreach ($order->details as $detail) {
                            if ($detail->item) {
                                $item = $detail->item;
                                $quantity = $detail->quantity;
                                $item->decrement('stock', $quantity);
                            }
                        }

                        // Send notification to customer
                        if (!Helpers::send_order_notification($order)) {
                            \Log::warning('Failed to send push notification to user for order #' . $order->id);
                        }

                        DB::commit();
                        $processedOrders[] = $order->id;
                    } catch (\Exception $e) {
                        DB::rollback();
                        $skippedOrders[] = [
                            'order_id' => $order->id,
                            'reason' => 'Database error: ' . $e->getMessage()
                        ];
                    }
                } else {
                    // Skip this order due to insufficient stock
                    $skippedOrders[] = [
                        'order_id' => $order->id,
                        'reason' => 'Insufficient stock',
                        'items' => $orderInsufficientItems
                    ];
                    
                    // Add to insufficient stock items for reporting
                    foreach ($orderInsufficientItems as $item) {
                        $insufficientStockItems[] = $item;
                    }
                }
            }

            // Prepare response message
            $message = '';
            if (!empty($processedOrders)) {
                $message .= count($processedOrders) . ' orders accepted successfully!';
            }
            
            if (!empty($skippedOrders)) {
                if (!empty($message)) {
                    $message .= ' ';
                }
                $message .= count($skippedOrders) . ' orders skipped due to insufficient stock.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed_orders' => $processedOrders,
                'skipped_orders' => $skippedOrders,
                'insufficient_stock_items' => $insufficientStockItems
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accepting orders: ' . $e->getMessage()
            ]);
        }
    }


    
    public function preparingUpdateInventoryPartial(Request $request)
    {
        // Replicate the 'update_inventory' logic from list() for preparing state
        $ordered = \DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.order_status', 'preparing')
            ->where('users.user_type', 'b2b')
            ->select('order_details.item_id', \DB::raw('SUM(order_details.quantity) as total_ordered'))
            ->groupBy('order_details.item_id')
            ->get()
            ->keyBy('item_id');

        $items = \App\Models\Item::select('id', 'name', 'stock')->get();
        $inventory_items = [];
        foreach ($items as $item) {
            $total_ordered = isset($ordered[$item->id]) ? $ordered[$item->id]->total_ordered : 0;
            $extra_needed = max(0, $total_ordered - $item->stock);
            if ($extra_needed > 0) { // Only include items with stock difference
                $inventory_items[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'stock' => $item->stock,
                    'total_ordered' => $total_ordered,
                    'extra_needed' => $extra_needed,
                    'new_bought' => 0, // Default value for new input
                ];
            }
        }
        return view('admin-views.order.partials._update_inventory', compact('inventory_items'))->render();
    }

    public function preparingAcceptByCustomerPartial(Request $request)
    {
        // Fetch all preparing B2B orders with customer and details
        $orders = \App\Models\Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'details.item'])
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->where('order_status', 'preparing')
            ->get();
        return view('admin-views.order.partials._accept_by_customer', ['customer_items' => $orders])->render();
    }

    /**
     * Accept selected preparing orders via AJAX, update status and stock, return updated partial.
     */
    public function acceptPreparingOrdersAjax(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        if (!empty($orderIds)) {
            $orders = \App\Models\Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
                ->with('details')
                ->whereIn('id', $orderIds)
                ->where('order_status', 'preparing')
                ->get();
            foreach ($orders as $order) {
                $order->order_status = 'accepted';
                $order->save();
                // Reduce inventory for each item in the order
                foreach ($order->details as $detail) {
                    $item = \App\Models\Item::find($detail->item_id);
                    if ($item) {
                        $item->stock = max(0, $item->stock - $detail->quantity);
                        $item->save();
                    }
                }
            }
        }
        // Return the updated partial
        $orders = \App\Models\Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->with(['customer' => function($query) {
                $query->withoutGlobalScope('storage');
            }, 'details.item'])
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->where('order_status', 'preparing')
            ->get();
        return view('admin-views.order.partials._accept_by_customer', ['customer_items' => $orders])->render();
    }

    /**
     * Update delivery man assignment for a specific order
     */
    public function updateOrderDeliveryMan(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_man_id' => 'nullable|exists:delivery_men,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $order->delivery_man_id = $request->delivery_man_id;
        $order->save();

        Toastr::success('Delivery man assignment updated successfully!');
        return back();
    }

    /**
     * Update packager assignment for a specific order
     */
    public function updateOrderPackager(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'packager_id' => 'nullable|exists:b2b_packagers,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $order->packager_id = $request->packager_id;
        $order->save();

        Toastr::success('Packager assignment updated successfully!');
        return back();
    }

    /**
     * Assign delivery man and packager and move order to packaging status
     */
    public function assignAndPackage(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        
        // If delivery man is not assigned, get the customer's default delivery man
        if (!$order->delivery_man_id) {
            $customer = $order->customer;
            if ($customer && $customer->b2bClients->first()) {
                $order->delivery_man_id = $customer->b2bClients->first()->default_deliveryman_id;
            }
        }
        
        // If packager is not assigned, get the customer's default packager
        if (!$order->packager_id) {
            $customer = $order->customer;
            if ($customer && $customer->b2bClients->first()) {
                $order->packager_id = $customer->b2bClients->first()->default_packager_id;
            }
        }
        
        // If still no delivery man or packager assigned, show warning
        if (!$order->delivery_man_id || !$order->packager_id) {
            Toastr::warning('Please assign both delivery man and packager before proceeding!');
            return back();
        }

        // Update order status to packaging
        $order->order_status = 'packaging';
        $order->save();

        Toastr::success('Order moved to packaging stage successfully!');
        return back();
    }

    /**
     * Bulk assign delivery man and packager and move orders to packaging status
     */
    public function bulkAssignAndPackage(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $orderIds = $request->input('order_ids', []);
        $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->whereIn('id', $orderIds)
            ->where('order_status', 'accepted')
            ->whereHas('customer', function($q) {
                $q->withoutGlobalScope('storage')->where('user_type', 'b2b');
            })
            ->get();

        $processedCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            // If delivery man is not assigned, get the customer's default delivery man
            if (!$order->delivery_man_id) {
                $customer = $order->customer;
                if ($customer && $customer->b2bClients->first()) {
                    $order->delivery_man_id = $customer->b2bClients->first()->default_deliveryman_id;
                }
            }
            
            // If packager is not assigned, get the customer's default packager
            if (!$order->packager_id) {
                $customer = $order->customer;
                if ($customer && $customer->b2bClients->first()) {
                    $order->packager_id = $customer->b2bClients->first()->default_packager_id;
                }
            }
            
            // If both delivery man and packager are assigned, process the order
            if ($order->delivery_man_id && $order->packager_id) {
                $order->order_status = 'packaging';
                $order->save();
                $processedCount++;
            } else {
                $failedCount++;
            }
        }

        if ($processedCount > 0) {
            Toastr::success($processedCount . ' order(s) moved to packaging stage successfully!');
        }
        
        if ($failedCount > 0) {
            Toastr::warning($failedCount . ' order(s) could not be processed. Please assign delivery man and packager.');
        }

        return back();
    }

    public function add_order_proof(Request $request, $id)
    {
        if($request->order_proof == null ){
            Toastr::error(translate('messages.Must_select_an_Image'));
            return back();
        }

        $order = Order::whereHas('customer', function($q) {
            $q->where('user_type', 'b2b');
        })->find($id);

        if (!$order) {
            Toastr::error(translate('messages.order_not_found'));
            return back();
        }

        $img_names = $order->order_proof ? json_decode($order->order_proof) : [];
        $images = [];
        $total_file = count($request->order_proof) + count($img_names);
        
        if(!$img_names){
            $request->validate([
                'order_proof' => 'required|array|max:5',
            ]);
        }

        if ($total_file > 5) {
            Toastr::error(translate('messages.order_proof_must_not_have_more_than_5_item'));
            return back();
        }

        if (!empty($request->file('order_proof'))) {
            foreach ($request->order_proof as $img) {
                $image_name = \App\CentralLogics\Helpers::upload('order/', 'png', $img);
                array_push($img_names, ['img'=>$image_name, 'storage'=> \App\CentralLogics\Helpers::getDisk()]);
            }
            $images = $img_names;
        }

        if(count($images) > 0){
            $order->order_proof = json_encode($images);
        }
        $order->save();

        Toastr::success(translate('messages.order_proof_added'));
        return back();
    }

    public function remove_proof_image(Request $request)
    {
        $order = Order::whereHas('customer', function($q) {
            $q->where('user_type', 'b2b');
        })->find($request['id']);

        if (!$order) {
            Toastr::error(translate('messages.order_not_found'));
            return back();
        }

        $array = [];
        $proof = isset($order->order_proof) ? json_decode($order->order_proof, true) : [];
        
        if (count($proof) < 2) {
            Toastr::warning(translate('all_image_delete_warning'));
            return back();
        }
      
        \App\CentralLogics\Helpers::check_and_delete('order/' , $request['name']);
        
        foreach ($proof as $image) {
            if ($image != $request['name']) {
                array_push($array, $image);
            }
        }
        
        $order->order_proof = json_encode($array);
        $order->save();

        Toastr::success(translate('order_proof_image_removed_successfully'));
        return back();
    }

    /**
     * Acknowledge delivered B2B orders (can be used when user approves change requests)
     */
    public function acknowledgeDeliveredOrder(Request $request, $orderId)
    {
        try {
            $order = Order::join('users', 'orders.user_id', '=', 'users.id')
                ->where('users.user_type', 'b2b')
                ->where('orders.id', $orderId)
                ->where('orders.order_status', 'delivered')
                ->select('orders.*')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or not eligible for acknowledgment'
                ], 404);
            }

            $order->order_status = 'acknowledged';
            $order->acknowledged_at = now();
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order acknowledged successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error acknowledging order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error acknowledging order'
            ], 500);
        }
    }

    public function editOrderQuantities(Request $request, $orderId)
    {
        try {
            $order = Order::with(['details.item'])
                ->where('id', $orderId)
                ->whereHas('customer', function($q) {
                    $q->where('user_type', 'b2b');
                })
                ->where('order_status', 'processing')
                ->firstOrFail();

            return view('admin-views.order.edit-quantities-b2b', compact('order'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not in processing status'
            ]);
        }
    }

    public function updateOrderQuantities(Request $request, $orderId)
    {
        try {
            // Debug: Log the incoming request data
            \Log::info('UpdateOrderQuantities Request Data:', [
                'order_id' => $orderId,
                'request_data' => $request->all()
            ]);
            
            $request->validate([
                'order_details' => 'required|array',
                'order_details.*.id' => 'required|exists:order_details,id',
                'order_details.*.quantity' => 'nullable|integer|min:0',
                'order_details.*.is_na' => 'nullable|boolean',
                'modification_reason' => 'nullable|string|max:500'
            ]);

            $order = Order::with(['details.item'])
                ->where('id', $orderId)
                ->whereHas('customer', function($q) {
                    $q->where('user_type', 'b2b');
                })
                ->where('order_status', 'processing')
                ->firstOrFail();

            DB::beginTransaction();

            $totalAmount = 0;
            $modifiedItems = [];
            $naItems = [];

            foreach ($request->order_details as $detailData) {
                $orderDetail = $order->details->find($detailData['id']);
                
                if (!$orderDetail) {
                    continue;
                }

                $isNA = isset($detailData['is_na']) && $detailData['is_na'];
                $newQuantity = $isNA ? 0 : (int)($detailData['quantity'] ?? 0);
                $originalQuantity = $orderDetail->quantity;

                if ($isNA) {
                    // Mark item as NA
                    $orderDetail->quantity = 0;
                    $orderDetail->price = 0;
                    $orderDetail->is_na = true;
                    $naItems[] = $orderDetail->item->name ?? 'Unknown Item';
                } else {
                    // Update quantity
                    $orderDetail->quantity = $newQuantity;
                    $orderDetail->is_na = false;
                    
                    if ($newQuantity != $originalQuantity) {
                        $modifiedItems[] = [
                            'item_name' => $orderDetail->item->name ?? 'Unknown Item',
                            'original_quantity' => $originalQuantity,
                            'new_quantity' => $newQuantity
                        ];
                    }
                }

                $orderDetail->save();
                $totalAmount += ($orderDetail->price * $orderDetail->quantity);
            }

            // Update order total
            $order->order_amount = $totalAmount;
            $order->quantity_modified = true;
            $order->modification_reason = $request->modification_reason;
            $order->modified_at = now();
            $order->save();

            DB::commit();

            // Send notification to customer about order modification
            if ($order->customer && ($order->quantity_modified || !empty($naItems))) {
                $this->sendOrderModificationNotification($order, $modifiedItems, $naItems);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order quantities updated successfully!',
                'modified_items' => $modifiedItems,
                'na_items' => $naItems,
                'new_total' => $totalAmount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('UpdateOrderQuantities Error:', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating order quantities: ' . $e->getMessage()
            ]);
        }
    }

    private function sendOrderModificationNotification($order, $modifiedItems, $naItems)
    {
        try {
            $message = "Your order #{$order->id} has been modified. ";
            
            if (!empty($modifiedItems)) {
                $message .= "Quantities updated for: " . implode(', ', array_column($modifiedItems, 'item_name')) . ". ";
            }
            
            if (!empty($naItems)) {
                $message .= "Items not available: " . implode(', ', $naItems) . ". ";
            }
            
            $message .= "Please check your order details for updated information.";

            // Send push notification if customer has FCM token
            if ($order->customer && $order->customer->cm_firebase_token) {
                try {
                    $pushResult = \App\CentralLogics\Helpers::send_push_notif_to_device(
                        $order->customer->cm_firebase_token,
                        [
                            'title' => 'Order Modified',
                            'description' => $message,
                            'image' => '',
                            'type' => 'order_modified',
                            'order_id' => $order->id
                        ]
                    );
                    
                    if (!$pushResult) {
                        \Log::warning('Push notification failed for order #' . $order->id . ' - Firebase not configured or token invalid');
                    } else {
                        // Log successful notification
                        \Log::info('Order modification notification sent successfully for order #' . $order->id);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send push notification: ' . $e->getMessage());
                }
            } else {
                \Log::warning('No FCM token found for customer in order #' . $order->id);
            }

            // Send SMS notification if configured
            // Note: SMS notification function not implemented yet
            // if ($order->customer && $order->customer->phone) {
            //     try {
            //         \App\CentralLogics\Helpers::send_sms_notification(
            //             $order->customer->phone,
            //             $message
            //         );
            //     } catch (\Exception $e) {
            //         \Log::error('Failed to send SMS notification: ' . $e->getMessage());
            //     }
            // }

        } catch (\Exception $e) {
            \Log::error('Failed to send order modification notification: ' . $e->getMessage());
        }
    }
}