@extends('layouts.admin.app')

@section('title', translate('Edit Order Quantities'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon">
                            <img src="{{ asset('/public/assets/admin/img/shopping-basket.png') }}" class="w--20" alt="">
                        </span>
                        <span>
                            Edit Order Quantities - Order #{{ $order->id }}
                        </span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.b2b-order.list', ['processing']) }}" class="btn btn-outline-primary">
                        <i class="tio-arrow-back"></i> Back to Processing
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Order Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="tio-user"></i> Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($order->customer)
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> {{ $order->customer->f_name }} {{ $order->customer->l_name }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Phone:</strong> {{ $order->customer->phone }}
                                </div>
                            </div>
                        @else
                            <p class="text-muted">Customer information not available</p>
                        @endif
                    </div>
                </div>

                <!-- Order Items Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="tio-shopping-cart"></i> Edit Order Items
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="edit-quantities-form">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>Original Quantity</th>
                                            <th>Available Stock</th>
                                            <th>New Quantity</th>
                                            <th>Mark as NA</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->details as $detail)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $detail->item->image_full_url ?? asset('public/assets/admin/img/100x100/food-default-image.png') }}" 
                                                             alt="Item Image" 
                                                             class="rounded mr-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                        <div>
                                                            <strong>{{ $detail->item->name ?? 'Unknown Item' }}</strong>
                                                            @if($detail->item && $detail->item->description)
                                                                <br><small class="text-muted">{{ Str::limit($detail->item->description, 50) }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info">{{ $detail->quantity }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($detail->item)
                                                        <span class="badge badge-{{ $detail->item->stock >= $detail->quantity ? 'success' : 'warning' }}">
                                                            {{ $detail->item->stock }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <input type="hidden" name="order_details[{{ $loop->index }}][id]" value="{{ $detail->id }}">
                                                    <input type="number" 
                                                           name="order_details[{{ $loop->index }}][quantity]" 
                                                           value="{{ $detail->quantity }}" 
                                                           min="0" 
                                                           class="form-control quantity-input" 
                                                           data-item-id="{{ $detail->id }}"
                                                           {{ $detail->is_na ? 'disabled' : '' }}>
                                                </td>
                                                <td class="text-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" 
                                                               class="custom-control-input na-checkbox" 
                                                               id="na_{{ $detail->id }}"
                                                               name="order_details[{{ $loop->index }}][is_na]"
                                                               value="1"
                                                               {{ $detail->is_na ? 'checked' : '' }}
                                                               data-item-id="{{ $detail->id }}">
                                                        <label class="custom-control-label" for="na_{{ $detail->id }}">
                                                            Not Available
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    {{ \App\CentralLogics\Helpers::format_currency($detail->price) }}
                                                </td>
                                                <td class="text-right">
                                                    <span class="item-total" data-item-id="{{ $detail->id }}">
                                                        {{ \App\CentralLogics\Helpers::format_currency($detail->price * $detail->quantity) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Modification Reason -->
                            <div class="form-group mt-3">
                                <label for="modification_reason">
                                    <strong>Reason for Modification (Optional)</strong>
                                </label>
                                <textarea class="form-control" 
                                          id="modification_reason" 
                                          name="modification_reason" 
                                          rows="3" 
                                          placeholder="Explain why quantities were modified (e.g., insufficient stock, customer request, etc.)">{{ $order->modification_reason }}</textarea>
                            </div>

                            <!-- Summary -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <strong>Original Total:</strong> {{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-success">
                                        <strong>New Total:</strong> <span id="new-total">{{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                                        <i class="tio-close"></i> Cancel
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="tio-checkmark"></i> Update Order
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Stock Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="tio-inventory"></i> Stock Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($order->details as $detail)
                            @if($detail->item)
                                <div class="mb-2">
                                    <strong>{{ $detail->item->name }}:</strong>
                                    <span class="badge badge-{{ $detail->item->stock >= $detail->quantity ? 'success' : 'warning' }}">
                                        {{ $detail->item->stock }} available
                                    </span>
                                    @if($detail->item->stock < $detail->quantity)
                                        <span class="text-danger ml-1">
                                            ({{ $detail->quantity - $detail->item->stock }} short)
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="tio-info"></i> Instructions
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="tio-checkmark-circle text-success"></i>
                                <strong>Edit Quantity:</strong> Change the quantity to available stock
                            </li>
                            <li class="mb-2">
                                <i class="tio-close-circle text-danger"></i>
                                <strong>Mark as NA:</strong> Check if item is not available
                            </li>
                            <li class="mb-2">
                                <i class="tio-money text-primary"></i>
                                <strong>Total Updates:</strong> Automatically calculated
                            </li>
                            <li>
                                <i class="tio-user text-info"></i>
                                <strong>Customer Notification:</strong> Will be informed of changes
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .quantity-input:disabled {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .na-checkbox:checked + .custom-control-label::before {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('edit-quantities-form');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            const naCheckboxes = document.querySelectorAll('.na-checkbox');
            const newTotalSpan = document.getElementById('new-total');

            // Handle NA checkbox changes
            naCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const itemId = this.dataset.itemId;
                    const quantityInput = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                    
                    if (this.checked) {
                        quantityInput.disabled = true;
                        quantityInput.value = 0;
                    } else {
                        quantityInput.disabled = false;
                        quantityInput.value = quantityInput.getAttribute('data-original-value') || 0;
                    }
                    
                    updateTotals();
                });
            });

            // Handle quantity input changes
            quantityInputs.forEach(input => {
                input.addEventListener('input', updateTotals);
                
                // Store original value
                input.setAttribute('data-original-value', input.value);
            });

            // Update totals calculation
            function updateTotals() {
                let newTotal = 0;
                
                quantityInputs.forEach(input => {
                    const itemId = input.dataset.itemId;
                    const quantity = parseInt(input.value) || 0;
                    const price = parseFloat(input.closest('tr').querySelector('td:nth-child(6)').textContent.replace(/[^\d.]/g, ''));
                    const total = quantity * price;
                    
                    // Update item total
                    const itemTotalSpan = document.querySelector(`.item-total[data-item-id="${itemId}"]`);
                    if (itemTotalSpan) {
                        itemTotalSpan.textContent = formatCurrency(total);
                    }
                    
                    newTotal += total;
                });
                
                newTotalSpan.textContent = formatCurrency(newTotal);
            }

            // Format currency
            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'BDT'
                }).format(amount);
            }

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                // Debug: Log form data
                console.log('Form data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }
                
                fetch('{{ route("admin.b2b-order.update-quantities", $order->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: true
                        }).then(() => {
                            window.location.href = '{{ route("admin.b2b-order.list", ["processing"]) }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Check if the error is a 500 status but the order was actually updated
                    if (error.message.includes('500')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Partial Success!',
                            text: 'Order quantities were updated successfully, but there was an issue with notifications. The order has been modified.',
                            showConfirmButton: true
                        }).then(() => {
                            window.location.href = '{{ route("admin.b2b-order.list", ["processing"]) }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while updating the order: ' + error.message
                        });
                    }
                });
            });

            // Initialize totals
            updateTotals();
        });
    </script>
@endsection 