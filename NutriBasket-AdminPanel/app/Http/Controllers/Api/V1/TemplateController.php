<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * Get template list for the authenticated user and all managers in the same client
     */
    public function index(Request $request)
    {
        // Debug: Log user info
        \Log::info('Template index called by user:', [
            'user_id' => $request->user()->id ?? 'null',
            'user_email' => $request->user()->email ?? 'null'
        ]);
        
        $user = $request->user();
        
        // Get user's B2B clients
        $userClients = $user->b2bClients;
        
        if ($userClients->isEmpty()) {
            // If user is not assigned to any B2B client, show only their own templates
            $templates = Template::where('user_id', $user->id)
                ->whereNull('client_id')
                ->with(['items.item'])
                ->active()
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Get templates from all managers assigned to the same clients
            $clientIds = $userClients->pluck('id');
            
            $templates = Template::where(function($query) use ($user, $clientIds) {
                    // User's own templates (both personal and client-based)
                    $query->where('user_id', $user->id)
                          ->orWhere(function($q) use ($clientIds) {
                              // Templates from all managers in the same clients
                              $q->whereIn('client_id', $clientIds);
                          });
                })
                ->with(['items.item', 'user', 'client'])
                ->active()
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'templates' => $templates,
            'message' => 'Templates retrieved successfully'
        ], 200);
    }

    /**
     * Create a new template
     */
    public function store(Request $request)
    {
        // Debug: Log the request data
        \Log::info('Template creation request:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            \Log::error('Template validation failed:', $validator->errors()->toArray());
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $user = $request->user();
            
            // Get user's primary B2B client (first one)
            $primaryClient = $user->b2bClients->first();
            
            $template = new Template();
            $template->user_id = $user->id;
            $template->client_id = $primaryClient ? $primaryClient->id : null; // Set client_id if user is assigned to a client
            $template->name = $request->name;
            $template->description = $request->description;
            $template->is_active = true;
            $template->save();

            // Add items to template
            foreach ($request->items as $item) {
                $templateItem = new TemplateItem();
                $templateItem->template_id = $template->id;
                $templateItem->item_id = $item['item_id'];
                $templateItem->quantity = $item['quantity'];
                $templateItem->notes = $item['notes'] ?? null;
                $templateItem->save();
            }

            $template->load(['items.item', 'client']);

            return response()->json([
                'template' => $template,
                'message' => 'Template created successfully'
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Template creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing template
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        // Find template - user can update their own templates or templates from their client
        $template = Template::where(function($query) use ($user, $id) {
                $query->where('id', $id)
                      ->where(function($q) use ($user) {
                          // User's own templates
                          $q->where('user_id', $user->id)
                            // Or templates from user's clients
                            ->orWhereIn('client_id', $user->b2bClients->pluck('id'));
                      });
            })
            ->first();

        if (!$template) {
            return response()->json([
                'message' => 'Template not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'items' => 'sometimes|required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            // Update template basic info
            if ($request->has('name')) {
                $template->name = $request->name;
            }
            if ($request->has('description')) {
                $template->description = $request->description;
            }
            if ($request->has('is_active')) {
                $template->is_active = $request->is_active;
            }
            $template->save();

            // Update items if provided
            if ($request->has('items')) {
                // Delete existing items
                $template->items()->delete();

                // Add new items
                foreach ($request->items as $item) {
                    $templateItem = new TemplateItem();
                    $templateItem->template_id = $template->id;
                    $templateItem->item_id = $item['item_id'];
                    $templateItem->quantity = $item['quantity'];
                    $templateItem->notes = $item['notes'] ?? null;
                    $templateItem->save();
                }
            }

            $template->load(['items.item', 'client']);

            return response()->json([
                'template' => $template,
                'message' => 'Template updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a template
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        // Find template - user can delete their own templates or templates from their client
        $template = Template::where(function($query) use ($user, $id) {
                $query->where('id', $id)
                      ->where(function($q) use ($user) {
                          // User's own templates
                          $q->where('user_id', $user->id)
                            // Or templates from user's clients
                            ->orWhereIn('client_id', $user->b2bClients->pluck('id'));
                      });
            })
            ->first();

        if (!$template) {
            return response()->json([
                'message' => 'Template not found'
            ], 404);
        }

        try {
            $template->delete();

            return response()->json([
                'message' => 'Template deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert order to template
     */
    public function convertFromOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $user = $request->user();
            
            // Get order details
            $order = \App\Models\Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->with(['details.item'])
                ->first();

            if (!$order) {
                return response()->json([
                    'message' => 'Order not found or not accessible'
                ], 404);
            }

            // Get user's primary B2B client
            $primaryClient = $user->b2bClients->first();

            // Create template
            $template = new Template();
            $template->user_id = $user->id;
            $template->client_id = $primaryClient ? $primaryClient->id : null;
            $template->name = $request->name;
            $template->description = $request->description;
            $template->is_active = true;
            $template->save();

            // Add order items to template
            foreach ($order->details as $detail) {
                $templateItem = new TemplateItem();
                $templateItem->template_id = $template->id;
                $templateItem->item_id = $detail->item_id;
                $templateItem->quantity = $detail->quantity;
                $templateItem->notes = $detail->variant ?? null;
                $templateItem->save();
            }

            $template->load(['items.item', 'client']);

            return response()->json([
                'template' => $template,
                'message' => 'Template created from order successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create template from order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
