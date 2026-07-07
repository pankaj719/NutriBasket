<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\B2BPackager;
use App\Models\Order;
use App\Models\User;
use App\Models\B2BClient;
use App\Models\DeliveryMan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;

class PackagerController extends Controller
{
    // GET /api/v1/packager/profile?token={token}
    public function profile(Request $request)
    {
        // Use token-based authentication (Passport)
        $user = Auth::guard('b2b_packagers')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    // GET /api/v1/packager/orders?token={token}&status={status}&limit={limit}&offset={offset}
    public function getOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $packager = Auth::guard('b2b_packagers')->user();
        if (!$packager) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        $status = $request->get('status');

        // Build query for orders - use packager_id field directly
        $query = Order::with(['customer', 'store', 'delivery_man', 'details.item'])
            ->where('packager_id', $packager->id)
            ->where('order_type', '!=', 'parcel'); // Exclude parcel orders

        // Filter by status if provided
        if ($status) {
            $query->where('order_status', $status);
        }

        // Get paginated results
        $orders = $query->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        // Format orders with assignment information
        $formattedOrders = [];
        foreach ($orders as $order) {
            $customer = $order->customer;
            
            $formattedOrders[] = [
                'id' => $order->id,
                'order_status' => $order->order_status,
                'order_amount' => $order->order_amount,
                'created_at' => $order->created_at,
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->f_name . ' ' . $customer->l_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ] : null,
                'store' => $order->store ? [
                    'id' => $order->store->id,
                    'name' => $order->store->name,
                ] : null,
                'assigned_delivery_man' => $order->delivery_man ? [
                    'id' => $order->delivery_man->id,
                    'name' => $order->delivery_man->f_name . ' ' . $order->delivery_man->l_name,
                    'phone' => $order->delivery_man->phone,
                ] : null,
                'assigned_packager' => [
                    'id' => $packager->id,
                    'name' => $packager->name,
                    'phone' => $packager->phone,
                ],
                'items_count' => $order->details->count(),
            ];
        }

        return response()->json([
            'total_size' => $query->count(),
            'limit' => $limit,
            'offset' => $offset,
            'orders' => $formattedOrders
        ]);
    }

    // GET /api/v1/packager/order/{order_id}?token={token}
    public function getOrderDetails(Request $request, $orderId)
    {
        $packager = Auth::guard('b2b_packagers')->user();
        if (!$packager) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get order with all relationships - use packager_id field directly
        $order = Order::with([
            'customer', 
            'store', 
            'delivery_man', 
            'details.item.unit',
            'payments'
        ])
        ->where('id', $orderId)
        ->where('packager_id', $packager->id)
        ->where('order_type', '!=', 'parcel')
        ->first();

        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => 'Order not found or not assigned to you']
                ]
            ], 404);
        }

        $customer = $order->customer;

        // Format order details
        $formattedOrder = [
            'id' => $order->id,
            'order_status' => $order->order_status,
            'order_amount' => $order->order_amount,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at,
            'delivery_address' => $order->delivery_address ? json_decode($order->delivery_address) : null,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->f_name . ' ' . $customer->l_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ] : null,
            'store' => $order->store ? [
                'id' => $order->store->id,
                'name' => $order->store->name,
                'address' => $order->store->address,
                'phone' => $order->store->phone,
            ] : null,
            'assigned_delivery_man' => $order->delivery_man ? [
                'id' => $order->delivery_man->id,
                'name' => $order->delivery_man->f_name . ' ' . $order->delivery_man->l_name,
                'phone' => $order->delivery_man->phone,
                'email' => $order->delivery_man->email,
            ] : null,
            'assigned_packager' => [
                'id' => $packager->id,
                'name' => $packager->name,
                'phone' => $packager->phone,
                'email' => $packager->email,
            ],
            'items' => $order->details->map(function($detail) {
                return [
                    'id' => $detail->id,
                    'item_id' => $detail->item_id,
                    'item_name' => $detail->item ? $detail->item->name : 'N/A',
                    'item_image' => $detail->item ? $detail->item->image_full_url : null,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                    'total' => $detail->price * $detail->quantity,
                    'unit' => $detail->item && $detail->item->unit ? $detail->item->unit->unit : null,
                ];
            }),
            'payments' => $order->payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'payment_status' => $payment->payment_status,
                    'payment_method' => $payment->payment_method,
                    'amount' => $payment->amount,
                    'created_at' => $payment->created_at,
                ];
            }),
        ];

        return response()->json($formattedOrder);
    }

    // GET /api/v1/packager/orders/status/{status}?token={token}&limit={limit}&offset={offset}
    public function getOrdersByStatus(Request $request, $status)
    {
        $request->merge(['status' => $status]);
        return $this->getOrders($request);
    }

    // PUT /api/v1/packager/order/update-status?token={token}
    public function updateOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'status' => 'required|in:picked_up',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $packager = Auth::guard('b2b_packagers')->user();
        if (!$packager) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the order - use packager_id field directly
        $order = Order::with(['customer', 'delivery_man'])
            ->where('id', $request->order_id)
            ->where('packager_id', $packager->id)
            ->where('order_type', '!=', 'parcel')
            ->first();

        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => 'Order not found or not assigned to you']
                ]
            ], 404);
        }

        // Check if order is in packaging status
        if ($order->order_status !== 'packaging') {
            return response()->json([
                'errors' => [
                    ['code' => 'status', 'message' => 'Order must be in packaging status to update to picked_up']
                ]
            ], 400);
        }

        // Check if delivery man is assigned
        if (!$order->delivery_man_id) {
            return response()->json([
                'errors' => [
                    ['code' => 'delivery_man', 'message' => 'No delivery man assigned to this order']
                ]
            ], 400);
        }

        // Update order status
        $order->order_status = $request->status;
        $order->picked_up = now();
        
        // Add packager note if provided
        if ($request->note) {
            $order->order_note = $order->order_note ? $order->order_note . "\n[Packager Note: " . $request->note . "]" : "[Packager Note: " . $request->note . "]";
        }
        
        $order->save();

        // Send notification to delivery man
        $deliveryMan = $order->delivery_man;
        if ($deliveryMan) {
            // Update delivery man's current orders count
            $deliveryMan->current_orders = $deliveryMan->current_orders + 1;
            $deliveryMan->save();

            // Send notification (you can implement this based on your notification system)
            // Helpers::send_order_notification($order);
            
            // You can add push notification here
            // if ($deliveryMan->fcm_token) {
            //     Helpers::send_push_notif_to_device($deliveryMan->fcm_token, [
            //         'title' => 'New Order Assigned',
            //         'description' => 'Order #' . $order->id . ' has been assigned to you',
            //         'order_id' => $order->id
            //     ]);
            // }
        }

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => [
                'id' => $order->id,
                'order_status' => $order->order_status,
                'delivery_man_id' => $order->delivery_man_id,
                'picked_up_at' => $order->picked_up,
                'assigned_delivery_man' => $deliveryMan ? [
                    'id' => $deliveryMan->id,
                    'name' => $deliveryMan->f_name . ' ' . $deliveryMan->l_name,
                    'phone' => $deliveryMan->phone,
                    'email' => $deliveryMan->email,
                ] : null,
            ]
        ], 200);
    }

    // GET /api/v1/packager/orders/packaging?token={token}&limit={limit}&offset={offset}
    public function getPackagingOrders(Request $request)
    {
        $request->merge(['status' => 'packaging']);
        return $this->getOrders($request);
    }

    // GET /api/v1/packager/orders/picked_up?token={token}&limit={limit}&offset={offset}
    public function getPickedUpOrders(Request $request)
    {
        $request->merge(['status' => 'picked_up']);
        return $this->getOrders($request);
    }
} 