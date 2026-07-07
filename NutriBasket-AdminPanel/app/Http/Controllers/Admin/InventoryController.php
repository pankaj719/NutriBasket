<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\InventoryVendor;
use App\Models\InventoryPurchase;
use App\Models\WastageCategory;
use App\Models\InventoryWastage;
use App\Models\Order;
use App\Models\OrderDetail;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index()
    {
        return view('admin-views.inventory.index');
    }

    // Purchase Management
    public function purchaseIndex(Request $request)
    {
        $query = Item::with(['category', 'unit']);

        // Filter for ordered items
        if ($request->has('show_ordered_items') && $request->show_ordered_items) {
            $orderedItemIds = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('orders.order_status', 'processing')
                ->select('order_details.item_id')
                ->distinct()
                ->pluck('item_id');
            
            $query->whereIn('id', $orderedItemIds);
        }

        // Filter for stock deficit items
        if ($request->has('show_stock_deficit') && $request->show_stock_deficit) {
            $deficitItems = DB::table('items')
                ->join('order_details', 'items.id', '=', 'order_details.item_id')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('orders.order_status', 'processing')
                ->select('items.id', DB::raw('SUM(order_details.quantity) as total_ordered'))
                ->groupBy('items.id', 'items.stock')
                ->havingRaw('SUM(order_details.quantity) > items.stock')
                ->pluck('items.id');
            
            $query->whereIn('id', $deficitItems);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $items = $query->get();
        $vendors = InventoryVendor::active()->get();

        // Calculate additional data for each item
        $itemsWithData = $items->map(function ($item) {
            // Get ordered quantity for processing orders
            $orderedQty = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('orders.order_status', 'processing')
                ->where('order_details.item_id', $item->id)
                ->sum('order_details.quantity');

            // Get latest purchase info
            $latestPurchase = InventoryPurchase::where('item_id', $item->id)
                ->orderBy('purchase_date', 'desc')
                ->first();

            // Calculate extra needed (ordered - current stock)
            $extraNeeded = max(0, $orderedQty - $item->stock);

            // Get total purchases
            $totalPurchased = InventoryPurchase::where('item_id', $item->id)
                ->sum('quantity');

            // Get vendor info from latest purchase
            $vendor = $latestPurchase ? $latestPurchase->vendor : null;
            $purchasePrice = $latestPurchase ? $latestPurchase->price_per_unit : $item->price;

            return [
                'item' => $item,
                'ordered_qty' => $orderedQty,
                'extra_needed' => $extraNeeded,
                'total_purchased' => $totalPurchased,
                'latest_purchase_price' => $purchasePrice,
                'weighted_average_price' => $item->weighted_average_price,
                'vendor' => $vendor,
                'has_stock_difference' => $orderedQty > $item->stock,
                'is_ordered' => $orderedQty > 0
            ];
        });

        // Apply additional filters
        if ($request->has('show_stock_difference') && $request->show_stock_difference) {
            $itemsWithData = $itemsWithData->filter(function ($itemData) {
                return $itemData['has_stock_difference'];
            });
        }

        if ($request->has('show_ordered_items') && $request->show_ordered_items) {
            $itemsWithData = $itemsWithData->filter(function ($itemData) {
                return $itemData['is_ordered'];
            });
        }

        return view('admin-views.inventory.purchase.index', compact('itemsWithData', 'vendors'));
    }

    public function storePurchase(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'price_per_unit' => 'required|numeric|min:0.01',
            'vendor_id' => 'nullable|exists:inventory_vendors,id',
            'vendor_name' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $totalPrice = $request->quantity * $request->price_per_unit;

        $purchase = InventoryPurchase::create([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'price_per_unit' => $request->price_per_unit,
            'total_price' => $totalPrice,
            'vendor_id' => $request->vendor_id,
            'vendor_name' => $request->vendor_name,
            'purchase_date' => $request->purchase_date,
            'notes' => $request->notes,
            'created_by' => Auth::guard('admin')->id()
        ]);

        // Update item stock
        $item = Item::find($request->item_id);
        $item->increment('stock', $request->quantity);

        // Calculate and update weighted average price
        $item->calculateWeightedAveragePrice();

        // Update vendor total trade if vendor is selected
        if ($request->vendor_id) {
            $vendor = InventoryVendor::find($request->vendor_id);
            $vendor->increment('total_trade', $totalPrice);
        }

        Toastr::success('Purchase recorded successfully!');
        return redirect()->back();
    }

    public function purchaseSummary()
    {
        // Get current stock levels
        $items = Item::with(['category', 'unit'])->get();
        
        // Calculate ordered stock for processing orders
        $orderedStock = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'processing')
            ->select('order_details.item_id', DB::raw('SUM(order_details.quantity) as total_ordered'))
            ->groupBy('order_details.item_id')
            ->get()
            ->keyBy('item_id');

        // Calculate total purchases
        $totalPurchases = InventoryPurchase::select('item_id', DB::raw('SUM(quantity) as total_purchased'))
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        // Calculate total wastage
        $totalWastage = InventoryWastage::select('item_id', DB::raw('SUM(quantity) as total_wasted'))
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        return view('admin-views.inventory.purchase.summary', compact('items', 'orderedStock', 'totalPurchases', 'totalWastage'));
    }

    public function updateInventory(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'new_stock' => 'required|integer|min:0',
            'new_price' => 'required|numeric|min:0.01'
        ]);

        $item = Item::find($request->item_id);
        $oldStock = $item->stock;
        $stockDifference = $request->new_stock - $oldStock;

        // Update item
        $item->update([
            'stock' => $request->new_stock,
            'price' => $request->new_price
        ]);

        // If stock was increased, record as purchase
        if ($stockDifference > 0) {
            InventoryPurchase::create([
                'item_id' => $request->item_id,
                'quantity' => $stockDifference,
                'price_per_unit' => $request->new_price,
                'total_price' => $stockDifference * $request->new_price,
                'purchase_date' => now()->toDateString(),
                'notes' => 'Manual inventory update',
                'created_by' => Auth::guard('admin')->id()
            ]);

            // Calculate and update weighted average price
            $item->calculateWeightedAveragePrice();
        }

        Toastr::success('Inventory updated successfully!');
        return redirect()->back();
    }

    public function bulkUpdateInventory(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.item_id' => 'required|exists:items,id',
            'updates.*.new_stock' => 'required|integer|min:0',
            'updates.*.new_price' => 'required|numeric|min:0.01',
            'updates.*.vendor_id' => 'nullable|exists:inventory_vendors,id',
            'updates.*.vendor_name' => 'nullable|string|max:255',
            'updates.*.purchase_notes' => 'nullable|string'
        ]);

        $updatedCount = 0;

        foreach ($request->updates as $update) {
            $item = Item::find($update['item_id']);
            $oldStock = $item->stock;
            $stockDifference = $update['new_stock'] - $oldStock;

            // Update item
            $item->update([
                'stock' => $update['new_stock'],
                'price' => $update['new_price']
            ]);

            // If stock was increased, record as purchase
            if ($stockDifference > 0) {
                InventoryPurchase::create([
                    'item_id' => $update['item_id'],
                    'quantity' => $stockDifference,
                    'price_per_unit' => $update['new_price'],
                    'total_price' => $stockDifference * $update['new_price'],
                    'vendor_id' => $update['vendor_id'] ?? null,
                    'vendor_name' => $update['vendor_name'] ?? null,
                    'purchase_date' => now()->toDateString(),
                    'notes' => $update['purchase_notes'] ?? 'Bulk inventory update',
                    'created_by' => Auth::guard('admin')->id()
                ]);

                // Calculate and update weighted average price
                $item->calculateWeightedAveragePrice();
            }

            $updatedCount++;
        }

        Toastr::success("Successfully updated {$updatedCount} items!");
        return redirect()->back();
    }

    public function bulkPurchase(Request $request)
    {
        // Validate that we have purchase data
        if (!$request->has('purchases') || !is_array($request->purchases)) {
            Toastr::error('No purchase data provided.');
            return redirect()->back();
        }

        $purchasedCount = 0;
        $processedItems = [];

        // Process each purchase
        foreach ($request->purchases as $itemId => $purchaseData) {
            // Validate required fields
            if (empty($purchaseData['quantity']) || empty($purchaseData['price_per_unit'])) {
                continue;
            }

            // Validate numeric values
            if (!is_numeric($purchaseData['quantity']) || !is_numeric($purchaseData['price_per_unit'])) {
                continue;
            }

            $quantity = floatval($purchaseData['quantity']);
            $pricePerUnit = floatval($purchaseData['price_per_unit']);

            // Validate positive values
            if ($quantity <= 0 || $pricePerUnit <= 0) {
                continue;
            }

            // Find the item
            $item = Item::find($itemId);
            if (!$item) {
                continue;
            }

            $totalPrice = $quantity * $pricePerUnit;

            try {
                // Create purchase record
                InventoryPurchase::create([
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'price_per_unit' => $pricePerUnit,
                    'total_price' => $totalPrice,
                    'vendor_id' => $purchaseData['vendor_id'] ?? null,
                    'vendor_name' => $purchaseData['vendor_name'] ?? null,
                    'purchase_date' => now()->toDateString(),
                    'notes' => $purchaseData['notes'] ?? 'Bulk purchase',
                    'created_by' => Auth::guard('admin')->id()
                ]);

                // Update item stock (append)
                $item->increment('stock', $quantity);

                // Calculate and update weighted average price
                $item->calculateWeightedAveragePrice();

                // Update vendor total trade if vendor is selected
                if (!empty($purchaseData['vendor_id']) && $purchaseData['vendor_id'] !== 'others') {
                    $vendor = InventoryVendor::find($purchaseData['vendor_id']);
                    if ($vendor) {
                        $vendor->increment('total_trade', $totalPrice);
                    }
                }

                $purchasedCount++;
                $processedItems[] = $item->name;

            } catch (\Exception $e) {
                Toastr::error('Error processing item: ' . $item->name . ' - ' . $e->getMessage());
            }
        }

        // Show success or warning message
        if ($purchasedCount > 0) {
            Toastr::success("Successfully processed purchase for {$purchasedCount} items: " . implode(', ', $processedItems));
        } else {
            Toastr::warning("No purchases were processed. Please ensure you have entered valid quantities and prices.");
        }

        return redirect()->back();
    }

    // Wastage Management
    public function wastageIndex()
    {
        $items = Item::with(['category', 'unit'])->get();
        $categories = WastageCategory::active()->get();
        $wastages = InventoryWastage::with(['item', 'category', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin-views.inventory.wastage.index', compact('items', 'categories', 'wastages'));
    }

    public function storeWastage(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'category_id' => 'nullable|exists:wastage_categories,id',
            'category_name' => 'nullable|string|max:255',
            'reason' => 'nullable|string'
        ]);

        // All wastage is treated as loss (zero value)
        $totalValue = 0;

        $wastage = InventoryWastage::create([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'price_per_unit' => 0, // Set to 0 since all wastage is loss
            'total_value' => $totalValue,
            'category_id' => $request->category_id,
            'category_name' => $request->category_name,
            'reason' => $request->reason,
            'created_by' => Auth::guard('admin')->id()
        ]);

        // Subtract from item stock
        $item = Item::find($request->item_id);
        $item->decrement('stock', $request->quantity);

        Toastr::success('Wastage recorded successfully!');
        return redirect()->back();
    }

    // Vendor Management
    public function vendorIndex()
    {
        $vendors = InventoryVendor::orderBy('created_at', 'desc')->paginate(20);
        return view('admin-views.inventory.vendor.index', compact('vendors'));
    }

    public function storeVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'location' => 'required|string',
            'total_trade' => 'nullable|numeric|min:0'
        ]);

        InventoryVendor::create($request->all());

        Toastr::success('Vendor added successfully!');
        return redirect()->back();
    }

    public function updateVendor(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'location' => 'required|string',
            'total_trade' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $vendor = InventoryVendor::findOrFail($id);
        $vendor->update($request->all());

        Toastr::success('Vendor updated successfully!');
        return redirect()->back();
    }

    // Category Management
    public function categoryIndex()
    {
        $categories = WastageCategory::orderBy('created_at', 'desc')->paginate(20);
        return view('admin-views.inventory.category.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        WastageCategory::create($request->all());

        Toastr::success('Category added successfully!');
        return redirect()->back();
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category = WastageCategory::findOrFail($id);
        $category->update($request->only(['name', 'description']));

        Toastr::success(translate('Category updated successfully'));
        return redirect()->back();
    }

    // Purchase History
    public function purchaseHistory(Request $request)
    {
        $query = InventoryPurchase::with(['item.category', 'item.unit', 'vendor', 'createdBy']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('purchase_date', '<=', $request->end_date);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by item
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Get purchases grouped by date
        $purchasesByDate = $query->orderBy('purchase_date', 'desc')
            ->get()
            ->groupBy('purchase_date')
            ->map(function ($purchases, $date) {
                return [
                    'date' => $date,
                    'total_items' => $purchases->count(),
                    'total_quantity' => $purchases->sum('quantity'),
                    'total_amount' => $purchases->sum('total_price'),
                    'vendors' => $purchases->pluck('vendor_name')->filter()->unique()->values(),
                    'purchases' => $purchases
                ];
            });

        // Get filter options
        $vendors = InventoryVendor::active()->get();
        $items = Item::with(['category', 'unit'])->get();

        return view('admin-views.inventory.purchase.history', compact('purchasesByDate', 'vendors', 'items'));
    }

    public function purchaseHistoryDetail($date)
    {
        $purchases = InventoryPurchase::with(['item.category', 'item.unit', 'vendor', 'createdBy'])
            ->where('purchase_date', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($purchases->isEmpty()) {
            Toastr::error(translate('No purchases found for this date'));
            return redirect()->route('admin.inventory.purchase.history');
        }

        $summary = [
            'date' => $date,
            'total_items' => $purchases->count(),
            'total_quantity' => $purchases->sum('quantity'),
            'total_amount' => $purchases->sum('total_price'),
            'vendors' => $purchases->pluck('vendor_name')->filter()->unique()->values(),
            'created_by' => $purchases->first()->createdBy->name ?? 'Unknown'
        ];

        return view('admin-views.inventory.purchase.history-detail', compact('purchases', 'summary'));
    }
} 