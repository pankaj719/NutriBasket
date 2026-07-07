@extends('layouts.admin.app')

@section('title', translate('Purchase History'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-history text-primary"></i>
                        {{translate('Purchase History')}}
                    </h1>
                    <p class="page-header-text text-muted">{{translate('View all purchase orders by date')}}</p>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.inventory.purchase.index') }}" class="btn btn-outline-primary">
                        <i class="tio-shopping-cart"></i> {{translate('Back to Purchase')}}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="tio-filter"></i> {{translate('Filter Purchase History')}}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.inventory.purchase.history') }}" id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Start Date')}}</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('End Date')}}</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Vendor')}}</label>
                                        <select name="vendor_id" class="form-select">
                                            <option value="">{{translate('All Vendors')}}</option>
                                            @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                    {{ $vendor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">{{translate('Item')}}</label>
                                        <select name="item_id" class="form-select">
                                            <option value="">{{translate('All Items')}}</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="tio-filter"></i> {{translate('Apply Filters')}}
                                        </button>
                                        <a href="{{ route('admin.inventory.purchase.history') }}" class="btn btn-outline-secondary">
                                            <i class="tio-clear"></i> {{translate('Clear')}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="tio-calendar"></i> {{translate('Purchase History by Date')}}
                                </h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge badge-light">{{ $purchasesByDate->count() }} {{translate('Dates')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($purchasesByDate->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">{{translate('Date')}}</th>
                                            <th class="border-0 text-center">{{translate('Items')}}</th>
                                            <th class="border-0 text-center">{{translate('Total Quantity')}}</th>
                                            <th class="border-0 text-center">{{translate('Total Amount')}}</th>
                                            <th class="border-0 text-center">{{translate('Vendors')}}</th>
                                            <th class="border-0 text-center">{{translate('Actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchasesByDate as $dateData)
                                            <tr class="purchase-history-row">
                                                <td class="border-0">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <i class="tio-calendar text-primary"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 font-weight-bold">
                                                                {{ \Carbon\Carbon::parse($dateData['date'])->format('d M Y') }}
                                                            </h6>
                                                            <small class="text-muted">
                                                                {{ \Carbon\Carbon::parse($dateData['date'])->format('l') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="border-0 text-center">
                                                    <span class="badge badge-info badge-lg">
                                                        {{ $dateData['total_items'] }}
                                                    </span>
                                                </td>
                                                <td class="border-0 text-center">
                                                    <span class="badge badge-success badge-lg">
                                                        {{ number_format($dateData['total_quantity'], 2) }}
                                                    </span>
                                                </td>
                                                <td class="border-0 text-center">
                                                    <div class="text-primary font-weight-bold">
                                                        {{ \App\CentralLogics\Helpers::format_currency($dateData['total_amount']) }}
                                                    </div>
                                                </td>
                                                <td class="border-0 text-center">
                                                    @if($dateData['vendors']->count() > 0)
                                                        <div class="d-flex flex-wrap justify-content-center gap-1">
                                                            @foreach($dateData['vendors']->take(3) as $vendor)
                                                                <span class="badge badge-soft-secondary">{{ $vendor }}</span>
                                                            @endforeach
                                                            @if($dateData['vendors']->count() > 3)
                                                                <span class="badge badge-soft-secondary">+{{ $dateData['vendors']->count() - 3 }}</span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="border-0 text-center">
                                                    <a href="{{ route('admin.inventory.purchase.history.detail', $dateData['date']) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="tio-eye"></i> {{translate('View Details')}}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="tio-calendar text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-muted">{{translate('No Purchase History Found')}}</h5>
                                <p class="text-muted">{{translate('No purchases match your current filters.')}}</p>
                                <a href="{{ route('admin.inventory.purchase.index') }}" class="btn btn-primary">
                                    <i class="tio-shopping-cart"></i> {{translate('Go to Purchase Management')}}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .purchase-history-row {
            transition: background-color 0.2s;
        }
        .purchase-history-row:hover {
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
        .gap-1 {
            gap: 0.25rem;
        }
        .gap-2 {
            gap: 0.5rem;
        }
    </style>
@endsection 