@extends('layouts.admin.app')

@section('title', translate('Wastage Management'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-delete text-danger"></i>
                        {{translate('Wastage Management')}}
                    </h1>
                    <p class="page-header-text text-muted">{{translate('Record inventory losses and wastage')}}</p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Add Wastage Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="tio-plus"></i> {{translate('Record Wastage')}}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.inventory.wastage.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Item')}} <span class="text-danger">*</span></label>
                                        <select name="item_id" class="form-select" required>
                                            <option value="">{{translate('Select Item')}}</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" data-stock="{{ $item->stock }}">
                                                    {{ $item->name }} ({{ $item->stock }} in stock)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Quantity Lost')}} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="tio-minus"></i></span>
                                            <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Category')}}</label>
                                        <select name="category_id" class="form-select" id="category_select">
                                            <option value="">{{translate('Select Category')}}</option>
                                            <option value="others">{{translate('Others')}}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Category Name (if Others)')}}</label>
                                        <input type="text" name="category_name" class="form-control" id="category_name" disabled>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Reason for Wastage')}}</label>
                                        <textarea name="reason" class="form-control" rows="2" placeholder="{{translate('Enter reason for wastage (e.g., expired, damaged, spoiled)...')}}"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="tio-delete"></i> {{translate('Record Wastage')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wastage Records -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="tio-list"></i> {{translate('Wastage Records')}}
                                </h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge badge-light">{{ $wastages->total() }} {{translate('Records')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{translate('Date & Time')}}</th>
                                        <th class="border-0">{{translate('Item Details')}}</th>
                                        <th class="border-0 text-center">{{translate('Quantity Lost')}}</th>
                                        <th class="border-0 text-center">{{translate('Category')}}</th>
                                        <th class="border-0 text-center">{{translate('Reason')}}</th>
                                        <th class="border-0 text-center">{{translate('Recorded By')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($wastages as $wastage)
                                        <tr class="wastage-row">
                                            <td class="border-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <i class="tio-calendar text-danger"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 font-weight-bold">
                                                            {{ $wastage->created_at->format('M d, Y') }}
                                                        </h6>
                                                        <small class="text-muted">
                                                            {{ $wastage->created_at->format('H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="border-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <i class="tio-shopping-basket text-primary"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 font-weight-bold">{{ $wastage->item->name }}</h6>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge badge-soft-secondary me-2">
                                                                {{ $wastage->item->category->name ?? 'N/A' }}
                                                            </span>
                                                            <small class="text-muted">{{ $wastage->item->unit->title ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="border-0 text-center">
                                                <span class="badge badge-danger badge-lg">
                                                    {{ number_format($wastage->quantity, 2) }}
                                                </span>
                                            </td>
                                            <td class="border-0 text-center">
                                                @if($wastage->category)
                                                    <span class="badge badge-warning">{{ $wastage->category->name }}</span>
                                                @elseif($wastage->category_name)
                                                    <span class="badge badge-warning">{{ $wastage->category_name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="border-0 text-center">
                                                @if($wastage->reason)
                                                    <div class="text-muted small" title="{{ $wastage->reason }}">
                                                        {{ Str::limit($wastage->reason, 30) }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="border-0 text-center">
                                                <span class="badge badge-info">
                                                    {{ $wastage->createdBy->f_name ?? 'Admin' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="mb-3">
                                                    <i class="tio-delete text-muted" style="font-size: 3rem;"></i>
                                                </div>
                                                <h5 class="text-muted">{{translate('No Wastage Records Found')}}</h5>
                                                <p class="text-muted">{{translate('No wastage has been recorded yet.')}}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($wastages->hasPages())
                            <div class="card-footer">
                                <div class="d-flex justify-content-center">
                                    {{ $wastages->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .wastage-row {
            transition: background-color 0.2s;
        }
        .wastage-row:hover {
            background-color: rgba(220, 53, 69, 0.05);
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
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dee2e6;
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
        // Handle category selection
        document.getElementById('category_select').addEventListener('change', function() {
            var categoryNameInput = document.getElementById('category_name');
            
            if (this.value === 'others') {
                categoryNameInput.disabled = false;
                categoryNameInput.required = true;
            } else {
                categoryNameInput.disabled = true;
                categoryNameInput.required = false;
                categoryNameInput.value = '';
            }
        });

        // Show stock warning when item is selected
        document.querySelector('select[name="item_id"]').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var stock = selectedOption.getAttribute('data-stock');
            
            if (stock && parseFloat(stock) <= 0) {
                alert('Warning: This item has no stock available!');
            }
        });
    });
</script>
@endpush 