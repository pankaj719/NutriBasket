@extends('layouts.admin.app')

@section('title', translate('B2B Customer Invoices'))

@push('css_or_js')
<style>
    .customer-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
    }
    .stats-card {
        border-radius: 10px;
        border: 1px solid #e3ebf0;
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .customer-row {
        transition: all 0.2s ease;
    }
    .customer-row:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    .status-due {
        background-color: #ffeaa7;
        color: #fdcb6e;
    }
    .status-paid {
        background-color: #81ecec;
        color: #00b894;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center">
                    <i class="tio-receipt-outlined mr-2"></i>
                    {{ translate('B2B Customer Invoices') }}
                </h1>
                <p class="page-header-text">{{ translate('Manage invoices for all B2B customers') }}</p>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stats-card p-3 text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="tio-users text-primary" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-1">{{ $customers->count() }}</h4>
                <span class="text-muted">{{ translate('Total Customers') }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card p-3 text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="tio-shopping-cart text-info" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-1">{{ $customers->sum('total_orders') }}</h4>
                <span class="text-muted">{{ translate('Total Orders') }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card p-3 text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="tio-money text-success" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-1">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($customers->sum('total_amount'), 2) }}</h4>
                <span class="text-muted">{{ translate('Total Amount') }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card p-3 text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="tio-alarm text-warning" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-1">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($customers->sum('total_due_amount'), 2) }}</h4>
                <span class="text-muted">{{ translate('Due Amount') }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card p-3 text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="tio-checkmark-circle text-success" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-1">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($customers->sum('total_paid_amount'), 2) }}</h4>
                <span class="text-muted">{{ translate('Paid Amount') }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card p-3 text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="tio-chart-bar text-warning" style="font-size: 2rem;"></i>
                </div>
                <h4 class="mb-1">{{ number_format($customers->sum('total_quantity_sold')) }}</h4>
                <span class="text-muted">{{ translate('Total Items Sold') }}</span>
            </div>
        </div>
    </div>

    <!-- Customer List -->
    <div class="card">
        <div class="card-header border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">{{ translate('B2B Customers') }}</h4>
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <small class="text-muted">{{ translate('Showing') }} {{ $customers->count() }} {{ translate('customers') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-borderless">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">{{ translate('Customer') }}</th>
                            <th class="border-0">{{ translate('Contact') }}</th>
                            <th class="border-0 text-center">{{ translate('Total Orders') }}</th>
                            <th class="border-0 text-center">{{ translate('Quantity Sold') }}</th>
                            <th class="border-0 text-center">{{ translate('Total Amount') }}</th>
                            <th class="border-0 text-center">{{ translate('Paid Amount') }}</th>
                            <th class="border-0 text-center">{{ translate('Due Amount') }}</th>
                            <th class="border-0 text-center">{{ translate('Period') }}</th>
                            <th class="border-0 text-center">{{ translate('Status') }}</th>
                            <th class="border-0 text-center">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr class="customer-row" onclick="viewCustomerInvoices({{ $customer['id'] }})">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-circle avatar-sm mr-3">
                                        <span class="avatar-initials bg-primary text-white">
                                            {{ strtoupper(substr($customer['customer_name'], 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $customer['customer_name'] }}</h6>
                                        <small class="text-muted">ID: {{ $customer['manager_id'] }}</small>
                                        @if($customer['client_name'] != 'N/A')
                                        <br><small class="text-info">{{ $customer['client_name'] }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    @if($customer['email'])
                                    <div><i class="tio-email mr-1"></i>{{ $customer['email'] }}</div>
                                    @endif
                                    @if($customer['phone'])
                                    <div><i class="tio-call mr-1"></i>{{ $customer['phone'] }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary badge-pill">{{ $customer['total_orders'] }}</span>
                                <br><small class="text-muted">{{ $customer['completed_orders'] }} completed</small>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($customer['total_quantity_sold']) }}</strong>
                            </td>
                            <td class="text-center">
                                <strong class="text-primary">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($customer['total_amount'], 2) }}</strong>
                            </td>
                            <td class="text-center">
                                <strong class="text-success">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($customer['total_paid_amount'], 2) }}</strong>
                            </td>
                            <td class="text-center">
                                <strong class="text-danger">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($customer['total_due_amount'], 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($customer['start_date'] && $customer['end_date'])
                                <small class="text-muted">
                                    @php
                                        try {
                                            $startDateFormatted = '';
                                            $endDateFormatted = '';
                                            
                                            if($customer['start_date'] && is_object($customer['start_date']) && method_exists($customer['start_date'], 'format')) {
                                                $startDateFormatted = $customer['start_date']->format('M d, Y');
                                            } else {
                                                $startDateFormatted = $customer['start_date'];
                                            }
                                            
                                            if($customer['end_date'] && is_object($customer['end_date']) && method_exists($customer['end_date'], 'format')) {
                                                $endDateFormatted = $customer['end_date']->format('M d, Y');
                                            } else {
                                                $endDateFormatted = $customer['end_date'];
                                            }
                                        } catch(\Exception $e) {
                                            $startDateFormatted = $customer['start_date'];
                                            $endDateFormatted = $customer['end_date'];
                                        }
                                    @endphp
                                    {{ $startDateFormatted }}
                                    <br>to<br>
                                    {{ $endDateFormatted }}
                                </small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customer['total_due_amount'] > 0)
                                <span class="status-badge status-due">{{ translate('Due') }}</span>
                                @else
                                <span class="status-badge status-paid">{{ translate('Paid') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.b2b-invoices.customer', $customer['id']) }}" 
                                   class="btn btn-outline-primary btn-sm"
                                   title="{{ translate('View Invoices') }}">
                                    <i class="tio-visible"></i> {{ translate('View') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="tio-receipt-outlined" style="font-size: 4rem; color: #e3ebf0;"></i>
                <h4 class="mt-3 text-muted">{{ translate('No B2B customers with orders found') }}</h4>
                <p class="text-muted">{{ translate('B2B customers will appear here once they place orders') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
    function viewCustomerInvoices(customerId) {
        window.location.href = "{{ route('admin.b2b-invoices.customer', '') }}/" + customerId;
    }

    // Add some animation
    $(document).ready(function() {
        $('.stats-card').each(function(index) {
            $(this).delay(index * 100).animate({
                opacity: 1
            }, 300);
        });
    });
</script>
@endpush
