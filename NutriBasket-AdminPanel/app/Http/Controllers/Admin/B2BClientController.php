<?php
namespace App\Http\Controllers\Admin;

use App\Models\B2BClient;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class B2BClientController extends Controller
{
    public function index()
    {
        $clients = \App\Models\B2BClient::with(['users', 'contracts', 'defaultDeliveryman', 'defaultPackager'])->paginate(20);
        $b2bUsers = \App\Models\User::where('user_type', 'b2b')->get();
        $b2bClients = \App\Models\B2BClient::all();
        $allItems = \App\Models\Item::all();
        $deliverymen = \App\Models\DeliveryMan::all();
        $packagers = \App\Models\B2BPackager::all();
        return view('admin-views.b2b-clients.index', compact('clients', 'b2bUsers', 'b2bClients', 'allItems', 'deliverymen', 'packagers'));
    }

    public function store(Request $request)
    {
        // Debug logging
        \Log::info('=== B2B CLIENT STORE DEBUG ===');
        \Log::info('Request Method: ' . $request->method());
        \Log::info('Request URL: ' . $request->url());
        \Log::info('All Request Data: ' . json_encode($request->all()));
        \Log::info('Has Items: ' . ($request->has('items') ? 'YES' : 'NO'));
        \Log::info('Items Count: ' . (is_array($request->items) ? count($request->items) : 'NOT ARRAY'));
        \Log::info('Items Data: ' . json_encode($request->items));
        
        $request->validate([
            'name' => 'required',
            'contract_end_date' => 'required|date',
            'default_deliveryman_id' => 'nullable|exists:delivery_men,id',
            'default_packager_id' => 'nullable|exists:b2b_packagers,id',
            'address' => 'nullable|string',
        ]);
        $client = B2BClient::create([
            'name' => $request->name,
            'default_deliveryman_id' => $request->default_deliveryman_id,
            'default_packager_id' => $request->default_packager_id,
            'address' => $request->address,
        ]);

        // Create contract for client
        $contract = $client->contracts()->create([
            'client_id' => $client->id,
            'name' => $client->name . ' Contract',
            'status' => 'active',
            'end_date' => $request->contract_end_date,
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

        \Log::info('Contract created with items count: ' . $contract->items()->count());

        return redirect()->back()->with('success', 'B2B Client and contract added!');
    }

    public function assignUser(Request $request, $clientId)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $client = B2BClient::findOrFail($clientId);
        $client->users()->syncWithoutDetaching([$request->user_id]);
        return redirect()->back()->with('success', 'User assigned!');
    }

    public function removeUser($clientId, $userId)
    {
        $client = B2BClient::findOrFail($clientId);
        $client->users()->detach($userId);
        return redirect()->back()->with('success', 'User removed!');
    }

    /**
     * Show all B2B contracts with their client.
     */
    public function contracts()
    {
        $contracts = \App\Models\Contract::with(['client', 'items'])->paginate(20);
        $allItems = \App\Models\Item::all();
        return view('admin-views.b2b-contracts.index', compact('contracts', 'allItems'));
    }

    /**
     * Update the default deliveryman for a B2B client.
     */
    public function updateDefaultDeliveryman(Request $request, $clientId)
    {
        $request->validate([
            'default_deliveryman_id' => 'nullable|exists:delivery_men,id',
        ]);
        $client = B2BClient::findOrFail($clientId);
        $client->default_deliveryman_id = $request->default_deliveryman_id;
        $client->save();
        return redirect()->back()->with('success', 'Default deliveryman updated!');
    }

    /**
     * Update the default packager for a B2B client.
     */
    public function updateDefaultPackager(Request $request, $clientId)
    {
        $request->validate([
            'default_packager_id' => 'nullable|exists:b2b_packagers,id',
        ]);
        $client = B2BClient::findOrFail($clientId);
        $client->default_packager_id = $request->default_packager_id;
        $client->save();
        return redirect()->back()->with('success', 'Default packager updated!');
    }

    /**
     * Delete a B2B client.
     */
    public function destroy($clientId)
    {
        $client = B2BClient::findOrFail($clientId);
        
        // Delete associated contracts first
        $client->contracts()->delete();
        
        // Detach users
        $client->users()->detach();
        
        // Delete the client
        $client->delete();
        
        return redirect()->back()->with('success', 'B2B Client deleted successfully!');
    }

    /**
     * Delete a B2B contract.
     */
    public function destroyContract($contractId)
    {
        $contract = \App\Models\Contract::findOrFail($contractId);
        
        // Delete associated contract items first
        $contract->items()->delete();
        
        // Delete the contract
        $contract->delete();
        
        return redirect()->back()->with('success', 'B2B Contract deleted successfully!');
    }

    /**
     * Show bulk import page for contract items
     */
    public function bulkImportIndex()
    {
        $allItems = \App\Models\Item::all();
        $contracts = \App\Models\Contract::with(['client'])->get();
        return view('admin-views.b2b-contracts.bulk-import', compact('allItems', 'contracts'));
    }

    /**
     * Handle bulk import of contract items
     */
    public function bulkImportContractItems(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'items_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $collections = (new \Rap2hpoutre\FastExcel\FastExcel)->import($request->file('items_file'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'Invalid file format. Please upload a valid Excel or CSV file.');
        }

        $contract = \App\Models\Contract::findOrFail($request->contract_id);
        $importedCount = 0;
        $errors = [];
        


        try {
            \DB::beginTransaction();

            foreach ($collections as $index => $collection) {
                // Handle different possible column names
                $itemId = $collection['ItemId'] ?? $collection['item_id'] ?? $collection['Item ID'] ?? $collection['itemid'] ?? null;
                $price = $collection['Price'] ?? $collection['price'] ?? $collection['PRICE'] ?? null;
                
                // Validate required fields
                if (empty($itemId) || empty($price)) {
                    $errors[] = "Row " . ($index + 2) . ": ItemId and Price are required";
                    continue;
                }

                // Check if item exists
                $item = \App\Models\Item::find($itemId);
                if (!$item) {
                    $errors[] = "Row " . ($index + 2) . ": Item with ID " . $itemId . " not found";
                    continue;
                }

                // Check if price is valid
                if (!is_numeric($price) || $price < 0) {
                    $errors[] = "Row " . ($index + 2) . ": Price must be a positive number";
                    continue;
                }

                // Check if item already exists in contract
                $existingItem = $contract->items()->where('item_id', $itemId)->first();
                if ($existingItem) {
                    // Update existing item price
                    $existingItem->update(['price' => $price]);
                } else {
                    // Create new contract item
                    $contract->items()->create([
                        'item_id' => $itemId,
                        'price' => $price,
                    ]);
                }
                $importedCount++;
            }

            \DB::commit();

            $message = "Successfully imported {$importedCount} items to the contract.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Failed to import items: ' . $e->getMessage());
        }
    }

    /**
     * Export contract items template
     */
    public function exportContractItemsTemplate()
    {
        $items = \App\Models\Item::select('id', 'name')->get();
        
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'ItemId' => $item->id,
                'ItemName' => $item->name,
                'Price' => '', // Empty for template
            ];
        }

        return (new \Rap2hpoutre\FastExcel\FastExcel($data))->download('contract_items_template.xlsx');
    }

    /**
     * Export existing contract items for reference
     */
    public function exportContractItems($contractId)
    {
        $contract = \App\Models\Contract::with(['items.item'])->findOrFail($contractId);
        
        $data = [];
        foreach ($contract->items as $contractItem) {
            $data[] = [
                'ItemId' => $contractItem->item_id,
                'ItemName' => $contractItem->item ? $contractItem->item->name : 'Unknown Item',
                'Price' => $contractItem->price,
            ];
        }

        $filename = 'contract_' . $contractId . '_items.xlsx';
        return (new \Rap2hpoutre\FastExcel\FastExcel($data))->download($filename);
    }

    /**
     * Test method to manually test contract item creation
     */
    public function testImport()
    {
        try {
            $contract = \App\Models\Contract::first();
            if (!$contract) {
                return response()->json(['error' => 'No contracts found']);
            }

            $item = \App\Models\Item::first();
            if (!$item) {
                return response()->json(['error' => 'No items found']);
            }

            // Test creating a contract item
            $contractItem = $contract->items()->create([
                'item_id' => $item->id,
                'price' => 15.50,
            ]);

            // Test updating the same item
            $contractItem->update(['price' => 20.00]);

            return response()->json([
                'success' => true,
                'message' => 'Test item created and updated successfully',
                'contract_item_id' => $contractItem->id,
                'contract_id' => $contract->id,
                'item_id' => $item->id,
                'final_price' => $contractItem->price,
                'total_items_in_contract' => $contract->items()->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Show bulk contract creation page
     */
    public function bulkContractCreateIndex()
    {
        $allItems = \App\Models\Item::all();
        $clients = \App\Models\B2BClient::all();
        $deliverymen = \App\Models\DeliveryMan::all();
        $packagers = \App\Models\B2BPackager::all();
        return view('admin-views.b2b-contracts.bulk-create', compact('allItems', 'clients', 'deliverymen', 'packagers'));
    }

    /**
     * Handle bulk contract creation with items
     */
    public function bulkContractCreate(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:b2b_clients,id',
            'contract_name' => 'required|string|max:255',
            'end_date' => 'required|date',
            'items_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $collections = (new \Rap2hpoutre\FastExcel\FastExcel)->import($request->file('items_file'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', 'Invalid file format. Please upload a valid Excel or CSV file.');
        }

        $client = \App\Models\B2BClient::findOrFail($request->client_id);
        $importedCount = 0;
        $errors = [];

        try {
            \DB::beginTransaction();

            // Create the contract
            $contract = $client->contracts()->create([
                'client_id' => $client->id,
                'name' => $request->contract_name,
                'status' => 'active',
                'end_date' => $request->end_date,
            ]);

            // Process items
            foreach ($collections as $index => $collection) {
                // Handle different possible column names
                $itemId = $collection['ItemId'] ?? $collection['item_id'] ?? $collection['Item ID'] ?? $collection['itemid'] ?? null;
                $price = $collection['Price'] ?? $collection['price'] ?? $collection['PRICE'] ?? null;
                
                // Validate required fields
                if (empty($itemId) || empty($price)) {
                    $errors[] = "Row " . ($index + 2) . ": ItemId and Price are required";
                    continue;
                }

                // Check if item exists
                $item = \App\Models\Item::find($itemId);
                if (!$item) {
                    $errors[] = "Row " . ($index + 2) . ": Item with ID " . $itemId . " not found";
                    continue;
                }

                // Check if price is valid
                if (!is_numeric($price) || $price < 0) {
                    $errors[] = "Row " . ($index + 2) . ": Price must be a positive number";
                    continue;
                }

                // Create contract item
                $contract->items()->create([
                    'item_id' => $itemId,
                    'price' => $price,
                ]);
                $importedCount++;
            }

            \DB::commit();

            $message = "Successfully created contract '{$request->contract_name}' with {$importedCount} items for client '{$client->name}'.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create contract: ' . $e->getMessage());
        }
    }

    /**
     * Export template for bulk contract creation
     */
    public function exportBulkContractTemplate()
    {
        $items = \App\Models\Item::select('id', 'name')->get();
        
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'ItemId' => $item->id,
                'ItemName' => $item->name,
                'Price' => '', // Empty for template
            ];
        }

        return (new \Rap2hpoutre\FastExcel\FastExcel($data))->download('bulk_contract_template.xlsx');
    }
}