<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Contract;
use App\Models\Item;
use App\Models\B2BClient; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class B2BUserController extends Controller
{
    public function index()
    {
        $b2bUsers = User::where('user_type', 'b2b')->with('b2bClients')->paginate(20);
        $b2bClients = \App\Models\B2BClient::all();
        return view('admin-views.b2b-users.index', compact('b2bUsers', 'b2bClients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:6',
            'client_id' => 'nullable|exists:b2b_clients,id',
        ]);
        
        $user = User::create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'user_type' => 'b2b',
        ]);
        
        if ($request->filled('client_id')) {
            $user->b2bClients()->attach($request->client_id);
        }
        
        return redirect()->back()->with('success', 'B2B Manager added successfully! Phone: ' . $request->phone . ', Password: ' . $request->password);
    }

    public function storeVendor(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|email|unique:users,email',
            // add other fields as needed
        ]);
        $user = User::create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'user_type' => 'b2b',
            // add other fields as needed
        ]);
        return redirect()->back()->with('success', 'B2B Vendor added!');
    }

    public function storeContract(Request $request, $userId)
    {
        $request->validate([
            'name' => 'required',
            'end_date' => 'required|date',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.price' => 'nullable|numeric',
        ]);

        $contract = Contract::create([
            'user_id' => $userId,
            'name' => $request->name,
            'status' => 'active',
            'end_date' => $request->end_date,
        ]);

        // Save all items
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $contract->items()->create([
                    'item_id' => $itemData['item_id'],
                    'price' => $itemData['price'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Contract with items added!');
    }

    public function toggleContract($contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $contract->status = $contract->status === 'active' ? 'deactivated' : 'active';
        $contract->save();
        return redirect()->back()->with('success', 'Contract status updated!');
    }

    public function create()
    {
        $items = Item::all(); // or your logic to get items
        return view('admin.b2busers.create', compact('items'));
    }

    public function updateContract(Request $request, $contractId)
    {
        $contract = Contract::findOrFail($contractId);

        $request->validate([
            'end_date' => 'required|date',
            'items' => 'array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.price' => 'nullable|numeric',
        ]);

        $contract->end_date = $request->end_date;
        $contract->save();

        $requestItems = $request->items ?? [];
        $requestItemIds = collect($requestItems)->pluck('item_id')->filter()->toArray();

        // Remove items not in the request
        $contract->items()->whereNotIn('item_id', $requestItemIds)->delete();

        // Update or create contract items
        if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
            foreach ($request->items as $itemData) {
                if (!isset($itemData['item_id']) || empty($itemData['item_id'])) continue;
                $contractItem = $contract->items()->where('item_id', $itemData['item_id'])->first();
                if ($contractItem) {
                    $contractItem->price = $itemData['price'];
                    $contractItem->save();
                } else {
                    $contract->items()->create([
                        'item_id' => $itemData['item_id'],
                        'price' => $itemData['price'],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Contract updated!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Detach from all b2b clients (pivot table)
        $user->b2bClients()->detach();

        // Delete all contracts and their items
        foreach ($user->contracts as $contract) {
            $contract->items()->delete();
            $contract->delete();
        }

        // Delete the user
        $user->delete();

        return redirect()->back()->with('success', 'User and all related data deleted!');
    }

    public function assignClient(Request $request, $userId)
    {
        $request->validate([
            'client_id' => 'required|exists:b2b_clients,id',
        ]);
        $user = User::findOrFail($userId);
        $user->b2bClients()->attach($request->client_id);
        return redirect()->back()->with('success', 'Client assigned to user!');
    }
}