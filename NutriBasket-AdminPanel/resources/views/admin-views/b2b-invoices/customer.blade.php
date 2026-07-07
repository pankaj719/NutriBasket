@extends('layouts.admin.app')

@section('title', translate('Customer Invoices'))

@push('css_or_js')
<style>
    .customer-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
    }
    .summary-stat {
        border-radius: 8px;
        border: 1px solid #e3ebf0;
        transition: all 0.3s ease;
    }
    .summary-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .order-card {
        border-radius: 8px;
        border: 1px solid #e3ebf0;
        transition: all 0.2s ease;
    }
    .order-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .status-pending {
        background-color: #ffeaa7;
        color: #fdcb6e;
    }
    .status-processing {
        background-color: #74b9ff;
        color: #0984e3;
    }
    .status-delivered {
        background-color: #81ecec;
        color: #00b894;
    }
    .status-cancelled {
        background-color: #fab1a0;
        color: #e17055;
    }
    .invoice-header {
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item">
                            <a class="breadcrumb-link" href="{{ route('admin.b2b-invoices.index') }}">
                                {{ translate('B2B Invoices') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ translate('Customer Invoices') }}</li>
                    </ol>
                </nav>
                <h1 class="page-header-title d-flex align-items-center">
                    <i class="tio-receipt-outlined mr-2"></i>
                    {{ translate('Customer Invoices') }}
                </h1>
            </div>
            <div class="col-sm-auto">
                <a class="btn btn-outline-primary" href="{{ route('admin.b2b-invoices.index') }}">
                    <i class="tio-arrow-backward"></i> {{ translate('Back to All Customers') }}
                </a>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Customer Information Card -->
    <div class="card customer-info-card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xl avatar-circle mr-4">
                            <span class="avatar-initials bg-white text-primary" style="font-size: 1.5rem;">
                                {{ strtoupper(substr($customer->f_name . ' ' . $customer->l_name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <h2 class="mb-1">{{ $customer->f_name }} {{ $customer->l_name }}</h2>
                            <p class="mb-1 opacity-75">
                                <i class="tio-user mr-1"></i> Manager ID: {{ $customer->id }}
                            </p>
                            @if($customer->email)
                            <p class="mb-1 opacity-75">
                                <i class="tio-email mr-1"></i> {{ $customer->email }}
                            </p>
                            @endif
                            @if($customer->phone)
                            <p class="mb-0 opacity-75">
                                <i class="tio-call mr-1"></i> {{ $customer->phone }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-right">
                    @if($customer->b2bClients->count() > 0)
                    <div class="bg-white bg-opacity-25 rounded p-2">
                        <small class="text-white-50">{{ translate('Company') }}</small>
                        <h6 class="mb-0">{{ $customer->b2bClients->first()->company_name }}</h6>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="summary-stat p-3 text-center">
                <i class="tio-shopping-cart text-primary mb-2" style="font-size: 1.5rem;"></i>
                <h5 class="mb-1">{{ $summary['total_orders'] }}</h5>
                <small class="text-muted">{{ translate('Total Orders') }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-stat p-3 text-center">
                <i class="tio-chart-bar text-info mb-2" style="font-size: 1.5rem;"></i>
                <h5 class="mb-1">{{ number_format($summary['total_quantity']) }}</h5>
                <small class="text-muted">{{ translate('Items Sold') }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-stat p-3 text-center">
                <i class="tio-money text-success mb-2" style="font-size: 1.5rem;"></i>
                <h5 class="mb-1">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($summary['total_amount'], 2) }}</h5>
                <small class="text-muted">{{ translate('Total Amount') }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-stat p-3 text-center">
                <i class="tio-alarm text-warning mb-2" style="font-size: 1.5rem;"></i>
                <h5 class="mb-1">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($summary['total_due_amount'], 2) }}</h5>
                <small class="text-muted">{{ translate('Due Amount') }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-stat p-3 text-center">
                <i class="tio-checkmark-circle text-success mb-2" style="font-size: 1.5rem;"></i>
                <h5 class="mb-1">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($summary['total_paid_amount'], 2) }}</h5>
                <small class="text-muted">{{ translate('Paid Amount') }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="summary-stat p-3 text-center">
                <i class="tio-calendar text-secondary mb-2" style="font-size: 1.5rem;"></i>
                <h6 class="mb-1 text-sm">
                    @if($summary['first_order_date'] && is_object($summary['first_order_date']) && method_exists($summary['first_order_date'], 'format'))
                    {{ $summary['first_order_date']->format('M d, Y') }}
                    @else
                    -
                    @endif
                </h6>
                <small class="text-muted">{{ translate('First Order') }}</small>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card">
        <div class="invoice-header p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ translate('Order Invoices') }}</h4>
                <div class="d-flex align-items-center">
                    <span class="text-muted mr-3">
                        {{ translate('Total') }}: {{ $orders->total() }} {{ translate('orders') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-borderless">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">{{ translate('Order ID') }}</th>
                            <th class="border-0">{{ translate('Date') }}</th>
                            <th class="border-0 text-center">{{ translate('Items') }}</th>
                            <th class="border-0 text-center">{{ translate('Total Amount') }}</th>
                            <th class="border-0 text-center">{{ translate('Status') }}</th>
                            <th class="border-0 text-center">{{ translate('Delivery Man') }}</th>
                            <th class="border-0 text-center">{{ translate('Packager') }}</th>
                            <th class="border-0 text-center">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        @php
                            $itemCount = $order->details->count();
                            $totalQuantity = $order->details->sum('quantity');
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="mr-2">
                                        <h6 class="mb-0">#{{ $order->id }}</h6>
                                        <small class="text-muted">
                                            @if($order->created_at && is_object($order->created_at) && method_exists($order->created_at, 'format'))
                                                {{ $order->created_at->format('M d, Y H:i') }}
                                            @else
                                                {{ $order->created_at }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">
                                    @if($order->created_at && is_object($order->created_at) && method_exists($order->created_at, 'format'))
                                        {{ $order->created_at->format('M d, Y') }}
                                    @else
                                        {{ $order->created_at }}
                                    @endif
                                </span>
                                <br>
                                <small class="text-muted">
                                    @if($order->created_at && is_object($order->created_at) && method_exists($order->created_at, 'format'))
                                        {{ $order->created_at->format('H:i A') }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-secondary">{{ $itemCount }} {{ translate('items') }}</span>
                                <br>
                                <small class="text-muted">{{ translate('Qty') }}: {{ $totalQuantity }}</small>
                            </td>
                            <td class="text-center">
                                <strong>{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($order->order_amount, 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = '';
                                    switch($order->order_status) {
                                        case 'pending':
                                            $statusClass = 'status-pending';
                                            break;
                                        case 'processing':
                                        case 'confirmed':
                                            $statusClass = 'status-processing';
                                            break;
                                        case 'delivered':
                                            $statusClass = 'status-delivered';
                                            break;
                                        case 'cancelled':
                                        case 'refund_requested':
                                        case 'refunded':
                                            $statusClass = 'status-cancelled';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ translate($order->order_status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($order->delivery_man)
                                <span class="text-sm">{{ $order->delivery_man->f_name }} {{ $order->delivery_man->l_name }}</span>
                                @else
                                <span class="text-muted">{{ translate('Not assigned') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($order->packager)
                                <span class="text-sm">{{ $order->packager->f_name }} {{ $order->packager->l_name }}</span>
                                @else
                                <span class="text-muted">{{ translate('Not assigned') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.order.details', [$order['id']]) }}" 
                                       class="btn btn-outline-info btn-sm"
                                       title="{{ translate('View Details') }}">
                                        <i class="tio-visible"></i>
                                    </a>
                                    @if(in_array($order->order_status, ['delivered', 'confirmed']))
                                    <a href="{{ route('admin.b2b-order.generate-invoice', [$order['id']]) }}" 
                                       class="btn btn-outline-primary btn-sm"
                                       title="{{ translate('Generate Invoice') }}"
                                       target="_blank">
                                        <i class="tio-receipt"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer border-0">
                {{ $orders->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="tio-receipt-outlined" style="font-size: 4rem; color: #e3ebf0;"></i>
                <h4 class="mt-3 text-muted">{{ translate('No orders found') }}</h4>
                <p class="text-muted">{{ translate('This customer has not placed any orders yet') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
    // Add some animation
    $(document).ready(function() {
        $('.summary-stat').each(function(index) {
            $(this).delay(index * 100).animate({
                opacity: 1
            }, 300);
        });
    });
</script>
@endpush
