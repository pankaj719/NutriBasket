@extends('layouts.admin.app')

@section('title', translate('Purchase Details - ') . $summary['date'])

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-calendar text-primary"></i>
                        {{translate('Purchase Details')}}
                    </h1>
                    <p class="page-header-text text-muted">
                        {{translate('Purchase history for')}} {{ \Carbon\Carbon::parse($summary['date'])->format('d M Y, l') }}
                    </p>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.inventory.purchase.history') }}" class="btn btn-outline-primary">
                        <i class="tio-arrow-back"></i> {{translate('Back to History')}}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center text-info">
                                    <i class="tio-shopping-basket"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Total Items')}}</p>
                                    <h4 class="card-title">{{ $summary['total_items'] }}</h4>
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
                                    <i class="tio-plus"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Total Quantity')}}</p>
                                    <h4 class="card-title">{{ number_format($summary['total_quantity'], 2) }}</h4>
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
                                <div class="icon-big text-center text-primary">
                                    <i class="tio-money"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Total Amount')}}</p>
                                    <h4 class="card-title">{{ \App\CentralLogics\Helpers::format_currency($summary['total_amount']) }}</h4>
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
                                <div class="icon-big text-center text-warning">
                                    <i class="tio-store"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">{{translate('Vendors')}}</p>
                                    <h4 class="card-title">{{ $summary['vendors']->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Details -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="tio-list"></i> {{translate('Purchase Items')}}
                                </h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge badge-light">{{ $purchases->count() }} {{translate('Items')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0" style="width: 20%;">{{translate('Item Details')}}</th>
                                        <th class="border-0 text-center" style="width: 8%;">{{translate('Quantity')}}</th>
                                        <th class="border-0 text-center" style="width: 10%;">{{translate('Price/Unit')}}</th>
                                        <th class="border-0 text-center" style="width: 10%;">{{translate('Weighted Avg')}}</th>
                                        <th class="border-0 text-center" style="width: 10%;">{{translate('Total Price')}}</th>
                                        <th class="border-0 text-center" style="width: 12%;">{{translate('Vendor')}}</th>
                                        <th class="border-0 text-center" style="width: 12%;">{{translate('Notes')}}</th>
                                        <th class="border-0 text-center" style="width: 8%;">{{translate('Time')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $purchase)
                                        <tr class="purchase-detail-row">
                                            <td class="border-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <i class="tio-shopping-basket text-primary"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 font-weight-bold">{{ $purchase->item->name }}</h6>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge badge-soft-secondary me-2">
                                                                {{ $purchase->item->category->name ?? 'N/A' }}
                                                            </span>
                                                            <small class="text-muted">{{ $purchase->item->unit->title ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="border-0 text-center">
                                                <span class="badge badge-success badge-lg">
                                                    {{ number_format($purchase->quantity, 2) }}
                                                </span>
                                            </td>
                                            <td class="border-0 text-center">
                                                <div class="text-primary font-weight-bold">
                                                    {{ \App\CentralLogics\Helpers::format_currency($purchase->price_per_unit) }}
                                                </div>
                                            </td>
                                            <td class="border-0 text-center">
                                                <div class="text-success font-weight-bold">
                                                    {{ \App\CentralLogics\Helpers::format_currency($purchase->weighted_average_price) }}
                                                </div>
                                            </td>
                                            <td class="border-0 text-center">
                                                <div class="text-success font-weight-bold">
                                                    {{ \App\CentralLogics\Helpers::format_currency($purchase->total_price) }}
                                                </div>
                                            </td>
                                            <td class="border-0 text-center">
                                                @if($purchase->vendor_name)
                                                    <span class="badge badge-info">{{ $purchase->vendor_name }}</span>
                                                @elseif($purchase->vendor)
                                                    <span class="badge badge-info">{{ $purchase->vendor->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="border-0 text-center">
                                                @if($purchase->notes)
                                                    <div class="text-muted small" title="{{ $purchase->notes }}">
                                                        {{ Str::limit($purchase->notes, 30) }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="border-0 text-center">
                                                <small class="text-muted">
                                                    {{ $purchase->created_at->format('H:i') }}
                                                </small>
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

        <!-- Additional Information -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="tio-info"></i> {{translate('Purchase Information')}}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>{{translate('Date:')}}</strong></p>
                                <p class="text-muted">{{ \Carbon\Carbon::parse($summary['date'])->format('d M Y, l') }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>{{translate('Created By:')}}</strong></p>
                                <p class="text-muted">{{ $summary['created_by'] }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>{{translate('Total Items:')}}</strong></p>
                                <p class="text-muted">{{ $summary['total_items'] }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>{{translate('Total Quantity:')}}</strong></p>
                                <p class="text-muted">{{ number_format($summary['total_quantity'], 2) }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>{{translate('Total Amount:')}}</strong></p>
                                <p class="text-muted">{{ \App\CentralLogics\Helpers::format_currency($summary['total_amount']) }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>{{translate('Vendors:')}}</strong></p>
                                <p class="text-muted">{{ $summary['vendors']->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="tio-store"></i> {{translate('Vendors Used')}}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($summary['vendors']->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($summary['vendors'] as $vendor)
                                    <span class="badge badge-info">{{ $vendor }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">{{translate('No vendor information available')}}</p>
                        @endif
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
        .purchase-detail-row {
            transition: background-color 0.2s;
        }
        .purchase-detail-row:hover {
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
        .gap-2 {
            gap: 0.5rem;
        }
    </style>
@endsection 