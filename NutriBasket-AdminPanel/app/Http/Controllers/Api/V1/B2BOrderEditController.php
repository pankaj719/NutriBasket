<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderChangeRequest;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class B2BOrderEditController extends Controller
{
    /**
     * Get order details for editing (only for delivered orders)
     */
    public function getEditableOrder($orderId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to edit orders.'
                ], 403);
            }

            // Allow all authenticated users to edit delivered orders
            // (Removed B2B restriction to allow all user types)

            $order = Order::with(['details.item'])
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->where('order_status', 'delivered')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or not eligible for editing. Only delivered orders can be edited.'
                ], 404);
            }

            // Check if order is within the 24-hour window
            $deliveredAt = \Carbon\Carbon::parse($order->delivered);
            $twentyFourHoursLater = $deliveredAt->addHours(24);
            $now = \Carbon\Carbon::now();

            if ($now->gt($twentyFourHoursLater)) {
                return response()->json([
                    'success' => false,
                    'message' => '24-hour editing window has expired. Order has been auto-acknowledged.'
                ], 400);
            }

            // Get existing change requests for this order
            $existingRequests = OrderChangeRequest::where('order_id', $orderId)
                ->where('status', 'pending')
                ->get()
                ->keyBy('item_id');

            $orderData = [
                'order_id' => $order->id,
                'order_status' => $order->order_status,
                'delivered' => $order->delivered,
                'time_remaining_hours' => ceil($now->diffInHours($twentyFourHoursLater, false)),
                'items' => $order->details->map(function ($detail) use ($existingRequests) {
                    return [
                        'item_id' => $detail->item_id,
                        'order_detail_id' => $detail->id,
                        'item_name' => $detail->item ? $detail->item->name : 'Unknown Item',
                        'current_quantity' => $detail->quantity,
                        'unit_price' => $detail->price,
                        'total_price' => $detail->quantity * $detail->price,
                        'pending_change_request' => $existingRequests->has($detail->id) ? [
                            'request_id' => $existingRequests[$detail->id]->id,
                            'new_quantity' => $existingRequests[$detail->id]->new_quantity,
                            'reason' => $existingRequests[$detail->id]->reason,
                            'status' => $existingRequests[$detail->id]->status,
                        ] : null,
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $orderData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit quantity change requests for delivered order
     */
    public function submitQuantityChanges(Request $request, $orderId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to edit orders.'
                ], 403);
            }

            // Allow all authenticated users to edit delivered orders
            // (Removed B2B restriction to allow all user types)

            // Check if the request is in the mobile app format (item_X: quantity)
            $requestData = $request->all();
            
            // Convert mobile app format to standard format
            if (!isset($requestData['changes'])) {
                $changes = [];
                foreach ($requestData as $key => $value) {
                    if (strpos($key, 'item_') === 0) {
                        $itemId = str_replace('item_', '', $key);
                        $changes[] = [
                            'item_id' => (int)$itemId,
                            'new_quantity' => (float)$value,
                        ];
                    }
                }
                $requestData['changes'] = $changes;
            }

            $validator = Validator::make($requestData, [
                'changes' => 'required|array|min:1',
                'changes.*.item_id' => 'required|integer',
                'changes.*.new_quantity' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->where('order_status', 'delivered')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or not eligible for editing.'
                ], 404);
            }

            // Check if order is within the 24-hour window
            $deliveredAt = \Carbon\Carbon::parse($order->delivered);
            $twentyFourHoursLater = $deliveredAt->addHours(24);
            $now = \Carbon\Carbon::now();

            if ($now->gt($twentyFourHoursLater)) {
                return response()->json([
                    'success' => false,
                    'message' => '24-hour editing window has expired.'
                ], 400);
            }

            $createdRequests = [];

            foreach ($requestData['changes'] as $change) {
                // Verify the item belongs to this order
                $orderDetail = OrderDetail::where('order_id', $orderId)
                    ->where('id', $change['item_id'])
                    ->first();

                if (!$orderDetail) {
                    continue; // Skip invalid items
                }

                // Check if there's already a pending request for this item
                $existingRequest = OrderChangeRequest::where('order_id', $orderId)
                    ->where('item_id', $change['item_id'])
                    ->where('status', 'pending')
                    ->first();

                if ($existingRequest) {
                    // Update existing request
                    $existingRequest->new_quantity = $change['new_quantity'];
                    $existingRequest->reason = $change['reason'] ?? '';
                    $existingRequest->save();
                    $createdRequests[] = $existingRequest;
                } else {
                    // Create new request
                    $changeRequest = OrderChangeRequest::create([
                        'order_id' => $orderId,
                        'item_id' => $change['item_id'],
                        'new_quantity' => $change['new_quantity'],
                        'reason' => $change['reason'] ?? '',
                        'status' => 'pending',
                    ]);
                    $createdRequests[] = $changeRequest;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Change requests submitted successfully',
                'data' => [
                    'requests_count' => count($createdRequests),
                    'requests' => $createdRequests
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting change requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get change request status for an order
     */
    public function getChangeRequestStatus($orderId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to view change requests.'
                ], 403);
            }

            // Allow all authenticated users to view change requests
            // (Removed B2B restriction to allow all user types)

            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            $changeRequests = OrderChangeRequest::with(['order'])
                ->where('order_id', $orderId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'order_status' => $order->order_status,
                    'change_requests' => $changeRequests->map(function ($request) {
                        return [
                            'id' => $request->id,
                            'item_id' => $request->item_id,
                            'new_quantity' => $request->new_quantity,
                            'reason' => $request->reason,
                            'status' => $request->status,
                            'created_at' => $request->created_at,
                            'updated_at' => $request->updated_at,
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching change requests: ' . $e->getMessage()
            ], 500);
        }
    }
}
