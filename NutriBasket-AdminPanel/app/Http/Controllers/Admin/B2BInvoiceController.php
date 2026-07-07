<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\B2BClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class B2BInvoiceController extends Controller
{
    /**
     * Display B2B customer invoice summary page
     * Shows all B2B customers with their order statistics
     */
    public function index()
    {
        // Get all B2B users (managers) with their order statistics
        $customers = User::with(['b2bClients'])
            ->where('user_type', 'b2b')
            ->whereHas('orders')
            ->get()
            ->map(function ($user) {
                // Get all orders for this B2B user with details
                $orders = Order::with(['details'])
                    ->where('user_id', $user->id)
                    ->get();
                
                if ($orders->count() > 0) {
                    // Calculate total quantity sold using order details relationship
                    $totalQuantity = $orders->sum(function ($order) {
                        return $order->details->sum('quantity');
                    });

                    // Calculate total amount and due amount properly
                    $totalAmount = $orders->sum('order_amount');
                    $totalPaidAmount = $orders->where('order_status', 'delivered')->sum('order_amount');
                    $totalDueAmount = $totalAmount - $totalPaidAmount;

                    // Get start date (first order)
                    $startDate = $orders->min('created_at');
                    if ($startDate) {
                        try {
                            if (is_string($startDate)) {
                                $startDate = Carbon::parse($startDate);
                            } elseif (!($startDate instanceof Carbon)) {
                                $startDate = Carbon::parse($startDate);
                            }
                        } catch (\Exception $e) {
                            $startDate = null;
                        }
                    } else {
                        $startDate = null;
                    }

                    // Get end date (last order)
                    $endDate = $orders->max('created_at');
                    if ($endDate) {
                        try {
                            if (is_string($endDate)) {
                                $endDate = Carbon::parse($endDate);
                            } elseif (!($endDate instanceof Carbon)) {
                                $endDate = Carbon::parse($endDate);
                            }
                        } catch (\Exception $e) {
                            $endDate = null;
                        }
                    } else {
                        $endDate = null;
                    }

                    // Get total completed orders count
                    $completedOrdersCount = $orders->where('order_status', 'delivered')->count();

                    // Get B2B client name (if available)
                    $clientName = $user->b2bClients->first()->company_name ?? 'N/A';

                    return [
                        'id' => $user->id,
                        'customer_name' => $user->f_name . ' ' . $user->l_name,
                        'manager_id' => $user->id,
                        'client_name' => $clientName,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'total_quantity_sold' => $totalQuantity,
                        'total_amount' => $totalAmount,
                        'total_due_amount' => $totalDueAmount,
                        'total_paid_amount' => $totalPaidAmount,
                        'total_orders' => $orders->count(),
                        'completed_orders' => $completedOrdersCount,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'orders' => $orders
                    ];
                }
                return null;
            })
            ->filter() // Remove null values
            ->sortByDesc('total_due_amount')
            ->values();

        return view('admin-views.b2b-invoices.index', compact('customers'));
    }

    /**
     * Display all invoices for a specific B2B customer
     */
    public function customerInvoices($id)
    {
        // Get the B2B user
        $customer = User::with(['b2bClients', 'orders.details'])
            ->where('user_type', 'b2b')
            ->findOrFail($id);

        // Get all orders for this customer with pagination
        $orders = Order::where('user_id', $id)
            ->with(['delivery_man', 'packager', 'details'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate summary statistics
        $allOrders = Order::with(['details'])
            ->where('user_id', $id)
            ->get();

        // Handle case where user has no orders
        if ($allOrders->isEmpty()) {
            $summary = [
                'total_orders' => 0,
                'total_quantity' => 0,
                'total_amount' => 0,
                'total_due_amount' => 0,
                'total_paid_amount' => 0,
                'first_order_date' => null,
                'last_order_date' => null,
            ];
            
            return view('admin-views.b2b-invoices.customer', compact('customer', 'orders', 'summary'));
        }
        
        $totalQuantity = $allOrders->sum(function ($order) {
            return $order->details->sum('quantity');
        });

        $totalAmount = $allOrders->sum('order_amount');
        $totalPaidAmount = $allOrders->where('order_status', 'delivered')->sum('order_amount');
        $totalDueAmount = $totalAmount - $totalPaidAmount; // Due = Total - Paid

        // Get and parse dates properly
        $firstOrderDate = $allOrders->min('created_at');
        if ($firstOrderDate) {
            try {
                if (is_string($firstOrderDate)) {
                    $firstOrderDate = Carbon::parse($firstOrderDate);
                } elseif (!($firstOrderDate instanceof Carbon)) {
                    $firstOrderDate = Carbon::parse($firstOrderDate);
                }
            } catch (\Exception $e) {
                $firstOrderDate = null;
            }
        } else {
            $firstOrderDate = null;
        }

        $lastOrderDate = $allOrders->max('created_at');
        if ($lastOrderDate) {
            try {
                if (is_string($lastOrderDate)) {
                    $lastOrderDate = Carbon::parse($lastOrderDate);
                } elseif (!($lastOrderDate instanceof Carbon)) {
                    $lastOrderDate = Carbon::parse($lastOrderDate);
                }
            } catch (\Exception $e) {
                $lastOrderDate = null;
            }
        } else {
            $lastOrderDate = null;
        }

        $summary = [
            'total_orders' => $allOrders->count(),
            'total_quantity' => $totalQuantity,
            'total_amount' => $totalAmount,
            'total_due_amount' => $totalDueAmount,
            'total_paid_amount' => $totalPaidAmount,
            'first_order_date' => $firstOrderDate,
            'last_order_date' => $lastOrderDate,
        ];

        return view('admin-views.b2b-invoices.customer', compact('customer', 'orders', 'summary'));
    }
}
