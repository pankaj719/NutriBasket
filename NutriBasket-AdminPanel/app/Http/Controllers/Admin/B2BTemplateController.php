<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateItem;
use App\Models\User;
use App\Models\Item;
use App\Models\B2BClient;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;

class B2BTemplateController extends Controller
{
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $clientId = $request->get('client_id');
        $query = Template::with(['user', 'items.item', 'client'])
            ->orderBy('created_at', 'desc');

        if ($clientId) {
            // Show all templates for this client, regardless of which manager created it
            $query->where('client_id', $clientId);
        }

        $templates = $query->paginate(20);
        $users = User::where('user_type', 'b2b')->get();
        $items = Item::all();
        $clients = B2BClient::all();

        return view('admin-views.b2b-templates.index', compact('templates', 'users', 'items', 'clients', 'clientId'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'client_id' => 'nullable|exists:b2b_clients,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string'
        ]);

        try {
            // Get the user to find their associated client
            $user = User::findOrFail($request->user_id);
            
            // If no client_id is provided, get it from the user's first B2B client
            $clientId = $request->client_id;
            if (!$clientId && $user->b2bClients->isNotEmpty()) {
                $clientId = $user->b2bClients->first()->id;
            }
            
            $template = Template::create([
                'user_id' => $request->user_id,
                'client_id' => $clientId,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => true,
            ]);

            // Add items to template
            foreach ($request->items as $item) {
                TemplateItem::create([
                    'template_id' => $template->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            Toastr::success('Template created successfully!');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error('Failed to create template: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'client_id' => 'nullable|exists:b2b_clients,id',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string'
        ]);

        try {
            $template->update([
                'user_id' => $request->user_id,
                'client_id' => $request->client_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            // Delete existing items and add new ones
            $template->items()->delete();

            foreach ($request->items as $item) {
                TemplateItem::create([
                    'template_id' => $template->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            Toastr::success('Template updated successfully!');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error('Failed to update template: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified template
     */
    public function destroy($id)
    {
        try {
            $template = Template::findOrFail($id);
            $template->items()->delete();
            $template->delete();

            Toastr::success('Template deleted successfully!');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error('Failed to delete template: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Get template details for editing
     */
    public function getTemplate($id)
    {
        $template = Template::with(['items.item', 'user', 'client'])->findOrFail($id);
        return response()->json($template);
    }

    /**
     * Get templates by client
     */
    public function getTemplatesByClient($clientId)
    {
        $templates = Template::with(['user', 'items.item'])
            ->where('client_id', $clientId)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($templates);
    }
} 