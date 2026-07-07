<?php
// app/Http/Controllers/Api/V1/OrderChangeRequestController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderChangeRequest;
use App\Models\OrderDetails;

class OrderChangeRequestController extends Controller
{
    public function index()
    {
        $requests = OrderChangeRequest::with('order')->orderBy('created_at', 'desc')->get();
       return view('admin-views.change_requests.index', compact('requests'));
    }

    public function approve($id)
    {
        $request = OrderChangeRequest::findOrFail($id);
        $request->status = 'approved';
        $request->save();

        // Update the order item quantity
        $orderItem = OrderDetails::where('id', $request->item_id)->first();
        if ($orderItem) {
            $orderItem->quantity = $request->new_quantity;
            $orderItem->save();
        }

        // Get the order to check if it's a B2B order and delivered
        $order = $request->order;
        if ($order && $order->customer && $order->customer->user_type === 'b2b' && $order->order_status === 'delivered') {
            // Acknowledge the order since change request has been approved
            $order->order_status = 'acknowledged';
            $order->acknowledged_at = now();
            $order->save();
        }

        // TODO: Notify delivery app (push notification or polling)

        return redirect()->back()->with('success', 'Change request approved and order updated.');
    }

    public function reject($id)
    {
        $request = OrderChangeRequest::findOrFail($id);
        $request->status = 'declined';
        $request->save();

        // TODO: Notify delivery app (push notification or polling)

        return redirect()->back()->with('success', 'Change request declined.');
    }

    public function store(Request $request, $order)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'new_quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $changeRequest = \App\Models\OrderChangeRequest::create([
            'order_id' => $order,
            'item_id' => $request->item_id,
            'new_quantity' => $request->new_quantity,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'data' => $changeRequest]);
    }

    public function bulkStore(Request $request, $order)
    {
        $request->validate([
            'changes' => 'required|array',
            'changes.*.item_id' => 'required|integer',
            'changes.*.new_quantity' => 'required|numeric|min:0.01',
        ]);

        $created = [];
        foreach ($request->changes as $change) {
            $created[] = \App\Models\OrderChangeRequest::create([
                'order_id' => $order,
                'item_id' => $change['item_id'],
                'new_quantity' => $change['new_quantity'],
                'status' => 'pending',
            ]);
        }

        return response()->json(['success' => true, 'data' => $created]);
    }
}