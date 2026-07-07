@extends('layouts.admin.app')

@section('title', translate('Inventory Summary'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('Inventory Summary')}}</h1>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.inventory.purchase.index') }}" class="btn btn-primary">
                        <i class="tio-shopping-cart"></i> {{translate('Purchase Management')}}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-title bg-primary rounded">
                                        <i class="tio-shopping-cart"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 text-right">
                                <h4 class="mb-1">{{ $items->count() }}</h4>
                                <span class="text-muted">{{translate('Total Items')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-title bg-warning rounded">
                                        <i class="tio-warning"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 text-right">
                                <h4 class="mb-1">{{ $items->where('stock', 0)->count() }}</h4>
                                <span class="text-muted">{{translate('Out of Stock')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-title bg-info rounded">
                                        <i class="tio-shopping-bag"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 text-right">
                                <h4 class="mb-1">{{ $orderedStock->count() }}</h4>
                                <span class="text-muted">{{translate('Ordered Items')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-title bg-danger rounded">
                                        <i class="tio-delete"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 text-right">
                                <h4 class="mb-1">{{ $totalWastage->count() }}</h4>
                                <span class="text-muted">{{translate('Wastage Items')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Inventory Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{translate('Enhanced Inventory Summary')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>{{translate('Item Name')}}</th>
                                        <th>{{translate('Category')}}</th>
                                        <th>{{translate('Current Stock')}}</th>
                                        <th>{{translate('Ordered Stock')}}</th>
                                        <th>{{translate('Extra Needed')}}</th>
                                        <th>{{translate('Total Purchased')}}</th>
                                        <th>{{translate('Latest Purchase Price')}}</th>
                                        <th>{{translate('Latest Vendor')}}</th>
                                        <th>{{translate('Total Wasted')}}</th>
                                        <th>{{translate('Available Stock')}}</th>
                                        <th>{{translate('Status')}}</th>
                                        <th>{{translate('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        @php
                                            $orderedQty = $orderedStock->get($item->id)->total_ordered ?? 0;
                                            $totalPurchased = $totalPurchases->get($item->id)->total_purchased ?? 0;
                                            $totalWasted = $totalWastage->get($item->id)->total_wasted ?? 0;
                                            $availableStock = $item->stock - $orderedQty;
                                            $status = '';
                                            $statusClass = '';
                                            
                                            if ($item->stock == 0) {
                                                $status = 'Out of Stock';
                                                $statusClass = 'badge-danger';
                                            } elseif ($availableStock < 0) {
                                                $status = 'Stock Deficit';
                                                $statusClass = 'badge-warning';
                                            } elseif ($availableStock <= 5) {
                                                $status = 'Low Stock';
                                                $statusClass = 'badge-info';
                                            } else {
                                                $status = 'In Stock';
                                                $statusClass = 'badge-success';
                                            }

                                            // Get latest purchase info
                                            $latestPurchase = \App\Models\InventoryPurchase::where('item_id', $item->id)
                                                ->orderBy('purchase_date', 'desc')
                                                ->first();
                                            
                                            $extraNeeded = max(0, $orderedQty - $item->stock);
                                        @endphp
                                        <tr class="{{ $availableStock < 0 ? 'table-warning' : ($orderedQty > 0 ? 'table-info' : '') }}">
                                            <td>
                                                <strong>{{ $item->name }}</strong>
                                                <br><small class="text-muted">{{ $item->unit->title ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $item->category->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $item->stock > 0 ? 'success' : 'danger' }}">
                                                    {{ $item->stock }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($orderedQty > 0)
                                                    <span class="badge badge-info">{{ $orderedQty }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($extraNeeded > 0)
                                                    <span class="badge badge-warning">{{ $extraNeeded }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>{{ $totalPurchased }}</td>
                                            <td>
                                                @if($latestPurchase)
                                                    {{ \App\CentralLogics\Helpers::format_currency($latestPurchase->price_per_unit) }}
                                                    <br><small class="text-muted">{{ $latestPurchase->purchase_date->format('M d, Y') }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($latestPurchase && $latestPurchase->vendor)
                                                    {{ $latestPurchase->vendor->name }}
                                                @elseif($latestPurchase && $latestPurchase->vendor_name)
                                                    {{ $latestPurchase->vendor_name }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $totalWasted }}</td>
                                            <td>
                                                <span class="badge badge-{{ $availableStock >= 0 ? 'success' : 'danger' }}">
                                                    {{ $availableStock }}
                                                </span>
                                            </td>
                                            <td><span class="badge {{ $statusClass }}">{{ $status }}</span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateModal{{ $item->id }}">
                                                    {{translate('Update')}}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Modals -->
    @foreach($items as $item)
        <div class="modal fade" id="updateModal{{ $item->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{translate('Update Inventory')}} - {{ $item->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.inventory.purchase.update-inventory') }}">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>{{translate('Current Stock')}}</label>
                                <input type="text" class="form-control" value="{{ $item->stock }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>{{translate('New Stock')}} <span class="text-danger">*</span></label>
                                <input type="number" name="new_stock" class="form-control" value="{{ $item->stock }}" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>{{translate('New Price')}} <span class="text-danger">*</span></label>
                                <input type="number" name="new_price" class="form-control" value="{{ $item->price }}" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
                            <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Auto-calculate new stock when modal opens
        $('[data-toggle="modal"]').click(function() {
            var itemId = $(this).data('target').replace('#updateModal', '');
            var currentStock = parseInt($('#updateModal' + itemId + ' input[readonly]').val());
            $('#updateModal' + itemId + ' input[name="new_stock"]').val(currentStock);
        });
    });
</script>
@endpush 