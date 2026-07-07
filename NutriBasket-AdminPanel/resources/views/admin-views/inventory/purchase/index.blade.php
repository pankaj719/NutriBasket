@extends('layouts.admin.app')

@section('title', translate('Purchase Management'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-shopping-cart text-primary"></i>
                        {{translate('Purchase Management')}}
                    </h1>
                    <p class="page-header-text text-muted">{{translate('Manage inventory purchases and stock levels')}}</p>
                </div>
                <div class="col-sm-auto">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.inventory.purchase.history') }}" class="btn btn-outline-primary btn-lg">
                            <i class="tio-history"></i> {{translate('Purchase History')}}
                        </a>
                        <button type="button" class="btn btn-primary btn-lg" id="processPurchaseBtn">
                            <i class="tio-save"></i> {{translate('Process Purchase')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center text-warning">
                                    <i class="tio-shopping-basket"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Total Items')}}</p>
                                    <h4 class="card-title">{{ count($itemsWithData) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center text-info">
                                    <i class="tio-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Ordered Items')}}</p>
                                    <h4 class="card-title">{{ $itemsWithData->where('is_ordered', true)->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center text-danger">
                                    <i class="tio-warning"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Low Stock')}}</p>
                                    <h4 class="card-title">{{ $itemsWithData->where('has_stock_difference', true)->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center text-success">
                                    <i class="tio-store"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Vendors')}}</p>
                                    <h4 class="card-title">{{ count($vendors) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="tio-filter"></i> {{translate('Filters & Search')}}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.inventory.purchase.index') }}" id="filterForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Search Items')}}</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="tio-search"></i></span>
                                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{translate('Search by item name...')}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Quick Filters')}}</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="show_ordered_items" name="show_ordered_items" value="1" {{ request('show_ordered_items') ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="show_ordered_items">{{translate('Ordered Items')}}</label>
                                                </div>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="show_stock_deficit" name="show_stock_deficit" value="1" {{ request('show_stock_deficit') ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="show_stock_deficit">{{translate('Stock Deficit')}}</label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="show_stock_difference" name="show_stock_difference" value="1" {{ request('show_stock_difference') ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="show_stock_difference">{{translate('Stock Difference')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="tio-filter"></i> {{translate('Apply Filters')}}
                                            </button>
                                            <a href="{{ route('admin.inventory.purchase.index') }}" class="btn btn-outline-secondary">
                                                <i class="tio-clear"></i> {{translate('Clear')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="tio-inventory"></i> {{translate('Inventory & Purchase Management')}}
                                </h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge badge-light">{{ count($itemsWithData) }} {{translate('Items')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <form id="bulkPurchaseForm" method="POST" action="{{ route('admin.inventory.purchase.bulk-purchase') }}">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="border-0" style="width: 20%;">{{translate('Item Details')}}</th>
                                            <th class="border-0 text-center" style="width: 7%;">{{translate('Stock')}}</th>
                                            <th class="border-0 text-center" style="width: 7%;">{{translate('Ordered')}}</th>
                                            <th class="border-0 text-center" style="width: 7%;">{{translate('Needed')}}</th>
                                            <th class="border-0 text-center" style="width: 10%;">{{translate('Previous Price')}}</th>
                                            <th class="border-0 text-center" style="width: 10%;">{{translate('Weighted Avg')}}</th>
                                            <th class="border-0 text-center" style="width: 10%;">{{translate('Add Stock')}}</th>
                                            <th class="border-0 text-center" style="width: 10%;">{{translate('New Price')}}</th>
                                            <th class="border-0 text-center" style="width: 19%;">{{translate('Vendor')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemsWithData as $itemData)
                                            @php
                                                $item = $itemData['item'];
                                                $statusClass = '';
                                                $statusIcon = '';
                                                if ($itemData['has_stock_difference']) {
                                                    $statusClass = 'table-warning';
                                                    $statusIcon = '<i class="tio-warning text-warning"></i>';
                                                } elseif ($itemData['is_ordered']) {
                                                    $statusClass = 'table-info';
                                                    $statusIcon = '<i class="tio-shopping-cart text-info"></i>';
                                                }
                                            @endphp
                                            <tr class="{{ $statusClass }} purchase-row">
                                                <td class="border-0">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            {!! $statusIcon !!}
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 font-weight-bold">{{ $item->name }}</h6>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge badge-soft-secondary me-2">{{ $item->category->name ?? 'N/A' }}</span>
                                                                <small class="text-muted">{{ $item->unit->title ?? 'N/A' }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="border-0 text-center">
                                                    <span class="badge badge-{{ $item->stock > 0 ? 'success' : 'danger' }} badge-lg">
                                                        {{ $item->stock }}
                                                    </span>
                                                </td>
                                                <td class="border-0 text-center">
                                                    @if($itemData['ordered_qty'] > 0)
                                                        <span class="badge badge-info badge-lg">{{ $itemData['ordered_qty'] }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="border-0 text-center">
                                                    @if($itemData['extra_needed'] > 0)
                                                        <span class="badge badge-warning badge-lg">{{ $itemData['extra_needed'] }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="border-0 text-center">
                                                    <div class="text-primary font-weight-bold">
                                                        {{ \App\CentralLogics\Helpers::format_currency($itemData['latest_purchase_price']) }}
                                                    </div>
                                                </td>
                                                <td class="border-0 text-center">
                                                    <div class="text-success font-weight-bold">
                                                        {{ \App\CentralLogics\Helpers::format_currency($itemData['weighted_average_price']) }}
                                                    </div>
                                                </td>
                                                <td class="border-0">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="tio-plus"></i></span>
                                                        <input type="number" name="purchases[{{ $item->id }}][quantity]" 
                                                               class="form-control add-stock-input" 
                                                               value="" min="0" step="0.01"
                                                               placeholder="0.00">
                                                    </div>
                                                </td>
                                                <td class="border-0">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="tio-money"></i></span>
                                                        <input type="number" name="purchases[{{ $item->id }}][price_per_unit]" 
                                                               class="form-control" 
                                                               value="" 
                                                               step="0.01" min="0.01" 
                                                               placeholder="0.00">
                                                    </div>
                                                </td>
                                                <td class="border-0">
                                                    <div class="row g-2">
                                                        <div class="col-12">
                                                            <select name="purchases[{{ $item->id }}][vendor_id]" class="form-select form-select-sm vendor-select">
                                                                <option value="">{{translate('Select Vendor')}}</option>
                                                                <option value="others">{{translate('Others')}}</option>
                                                                @foreach($vendors as $vendor)
                                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <input type="text" name="purchases[{{ $item->id }}][vendor_name]" 
                                                                   class="form-control form-control-sm vendor-name-input" 
                                                                   placeholder="{{translate('Vendor name if others')}}" 
                                                                   style="display: none;">
                                                        </div>
                                                        <div class="col-12">
                                                            <input type="text" name="purchases[{{ $item->id }}][notes]" 
                                                                   class="form-control form-control-sm" 
                                                                   placeholder="{{translate('Notes')}}">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-stats {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card-stats:hover {
            transform: translateY(-2px);
        }
        .icon-big {
            font-size: 2.5rem;
        }
        .purchase-row {
            transition: background-color 0.2s;
        }
        .purchase-row:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        .badge-lg {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        .badge-soft-secondary {
            background-color: #e9ecef;
            color: #6c757d;
        }
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        .gap-2 {
            gap: 0.5rem;
        }
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }
    </style>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== DOCUMENT READY ===');
        console.log('Vanilla JavaScript loaded');
        
        // Check if button exists
        var button = document.getElementById('processPurchaseBtn');
        console.log('Process Purchase button found:', button ? 'YES' : 'NO');
        if (button) {
            console.log('Button text:', button.textContent);
            console.log('Button HTML:', button.innerHTML);
            
            // Add a simple test click
            button.addEventListener('click', function() {
                console.log('=== SIMPLE TEST CLICK ===');
                alert('Button click detected!');
            });
        }
        
        // Handle vendor selection
        document.querySelectorAll('.vendor-select').forEach(function(select) {
            select.addEventListener('change', function() {
                var vendorNameInput = this.closest('td').querySelector('.vendor-name-input');
                
                if (this.value === 'others') {
                    vendorNameInput.style.display = 'block';
                    vendorNameInput.required = true;
                } else {
                    vendorNameInput.style.display = 'none';
                    vendorNameInput.required = false;
                    vendorNameInput.value = '';
                }
            });
        });

        // Auto-fill previous purchase price when add stock is entered
        document.querySelectorAll('.add-stock-input').forEach(function(input) {
            input.addEventListener('input', function() {
                var row = this.closest('tr');
                var previousPriceSpan = row.querySelector('td:nth-child(5) div');
                var priceInput = row.querySelector('input[name*="[price_per_unit]"]');
                
                if (previousPriceSpan) {
                    var previousPrice = previousPriceSpan.textContent.replace(/[^\d.]/g, '');
                    if (previousPrice && parseFloat(previousPrice) > 0) {
                        priceInput.value = previousPrice;
                    }
                }
            });
        });

        // New Process Purchase button logic
        if (button) {
            button.addEventListener('click', function() {
                console.log('=== PROCESS PURCHASE BUTTON CLICKED ===');
                console.log('Button clicked at:', new Date().toISOString());
                
                // Check if we can find quantity inputs
                var quantityInputs = document.querySelectorAll('input[name*="[quantity]"]');
                console.log('Found quantity inputs:', quantityInputs.length);
                
                // Collect all items with valid purchase data
                var purchaseData = {};
                var validItems = 0;
                var totalInputs = 0;
                
                console.log('=== SCANNING INPUTS ===');
                
                quantityInputs.forEach(function(quantityInput, index) {
                    totalInputs++;
                    var row = quantityInput.closest('tr');
                    var priceInput = row.querySelector('input[name*="[price_per_unit]"]');
                    var vendorSelect = row.querySelector('select[name*="[vendor_id]"]');
                    var vendorNameInput = row.querySelector('input[name*="[vendor_name]"]');
                    var notesInput = row.querySelector('input[name*="[notes]"]');
                    
                    var quantity = quantityInput.value.trim();
                    var price = priceInput.value.trim();
                    
                    console.log('Input ' + index + ':');
                    console.log('  - Quantity:', quantity);
                    console.log('  - Price:', price);
                    console.log('  - Quantity input name:', quantityInput.name);
                    console.log('  - Price input name:', priceInput.name);
                    
                    // Only process if both quantity and price are provided
                    if (quantity && price && parseFloat(quantity) > 0 && parseFloat(price) > 0) {
                        var itemId = quantityInput.name.match(/\[(\d+)\]/)[1];
                        
                        purchaseData[itemId] = {
                            quantity: parseFloat(quantity),
                            price_per_unit: parseFloat(price),
                            vendor_id: vendorSelect ? vendorSelect.value : '',
                            vendor_name: vendorNameInput ? vendorNameInput.value : '',
                            notes: notesInput ? notesInput.value : ''
                        };
                        
                        validItems++;
                        console.log('  ✓ Valid item found:', itemId, purchaseData[itemId]);
                    } else {
                        console.log('  ✗ Invalid item - missing or zero values');
                    }
                });
                
                console.log('=== SUMMARY ===');
                console.log('Total inputs scanned:', totalInputs);
                console.log('Valid items found:', validItems);
                console.log('Purchase data:', purchaseData);
                
                if (validItems === 0) {
                    console.log('No valid items found - showing alert');
                    alert('Please enter quantity and price for at least one item.');
                    return;
                }
                
                console.log('=== CREATING FORM ===');
                
                // Create a new form with only the valid data
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.inventory.purchase.bulk-purchase") }}';
                
                console.log('Form created with action:', form.action);
                
                // Add CSRF token
                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                console.log('CSRF token added');
                
                // Add purchase data
                Object.keys(purchaseData).forEach(function(itemId) {
                    var data = purchaseData[itemId];
                    console.log('Adding data for item:', itemId, data);
                    
                    // Quantity
                    var quantityInput = document.createElement('input');
                    quantityInput.type = 'hidden';
                    quantityInput.name = 'purchases[' + itemId + '][quantity]';
                    quantityInput.value = data.quantity;
                    form.appendChild(quantityInput);
                    
                    // Price
                    var priceInput = document.createElement('input');
                    priceInput.type = 'hidden';
                    priceInput.name = 'purchases[' + itemId + '][price_per_unit]';
                    priceInput.value = data.price_per_unit;
                    form.appendChild(priceInput);
                    
                    // Vendor ID
                    if (data.vendor_id) {
                        var vendorIdInput = document.createElement('input');
                        vendorIdInput.type = 'hidden';
                        vendorIdInput.name = 'purchases[' + itemId + '][vendor_id]';
                        vendorIdInput.value = data.vendor_id;
                        form.appendChild(vendorIdInput);
                    }
                    
                    // Vendor Name
                    if (data.vendor_name) {
                        var vendorNameInput = document.createElement('input');
                        vendorNameInput.type = 'hidden';
                        vendorNameInput.name = 'purchases[' + itemId + '][vendor_name]';
                        vendorNameInput.value = data.vendor_name;
                        form.appendChild(vendorNameInput);
                    }
                    
                    // Notes
                    if (data.notes) {
                        var notesInput = document.createElement('input');
                        notesInput.type = 'hidden';
                        notesInput.name = 'purchases[' + itemId + '][notes]';
                        notesInput.value = data.notes;
                        form.appendChild(notesInput);
                    }
                });
                
                console.log('Form data added');
                
                // Confirm and submit
                if (confirm('Process purchase for ' + validItems + ' items?')) {
                    console.log('User confirmed - submitting form...');
                    document.body.appendChild(form);
                    console.log('Form appended to body');
                    form.submit();
                    console.log('Form submitted');
                } else {
                    console.log('User cancelled');
                }
            });
        }
    });
</script>
@endpush 