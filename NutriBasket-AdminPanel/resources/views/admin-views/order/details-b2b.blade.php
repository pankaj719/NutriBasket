@extends('layouts.admin.app')

@section('title', translate('B2B Order Details'))

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
                            B2B {{ translate('order_details') }} 
                            <span class="badge badge-soft-dark rounded-circle ml-1">{{ $order->details->count() }}</span>
                        </span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="row flex-xl-nowrap">
            <div class="col-lg-8 order-print-area-left">
                <!-- Main Order Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Card Header -->
                    <div class="card-header border-0 align-items-start flex-wrap">
                        <div class="row w-100">
                            <div class="col-md-8">
                                <div class="order-info-left">
                                    <h1 class="page-header-title d-flex align-items-center __gap-5px mb-3">
                                        B2B {{ translate('messages.order') }} #{{ $order['id'] }}
                                    </h1>
                                    <div class="order-meta-info">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="info-item mb-2">
                                                    <span class="info-label">
                                                        <i class="tio-date-range"></i>
                                                        {{ translate('messages.order_date') }}:
                                                    </span>
                                                    <span class="info-value">
                                                        {{ date('d M Y ' . config('timeformat'), strtotime($order['created_at'])) }}
                                                    </span>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <span class="info-label">
                                                        <i class="tio-shop"></i>
                                                        {{ translate('messages.store') }}:
                                                    </span>
                                                    <span class="info-value">
                                                        <span class="badge badge-soft-primary">
                                                            {{ Str::limit($order->store ? $order->store->name : translate('messages.store deleted!'), 25, '...') }}
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-item mb-2">
                                                    <span class="info-label">
                                                        <i class="tio-payment"></i>
                                                        {{ translate('messages.payment_method') }}:
                                                    </span>
                                                    <span class="info-value">
                                                        @if ($order['payment_method'] == 'cash_on_delivery')
                                                            <span class="badge badge-soft-info">
                                                                {{ translate('messages.cash_on_delivery') }}
                                                            </span>
                                                        @elseif($order['payment_method'] == 'wallet')
                                                            <span class="badge badge-soft-info">
                                                                {{ translate('messages.wallet') }}
                                                            </span>
                                                        @elseif($order['payment_method'] == 'pay_now')
                                                            <span class="badge badge-soft-info">
                                                                {{ translate('messages.pay_now') }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-soft-info">
                                                                {{ translate(str_replace('_', ' ', $order['payment_method'])) }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <span class="info-label">
                                                        <i class="tio-credit-card"></i>
                                                        {{ translate('messages.payment_status') }}:
                                                    </span>
                                                    <span class="info-value">
                                                        @if ($order['payment_status'] == 'paid')
                                                            <span class="badge badge-soft-success">
                                                                {{ translate('messages.paid') }}
                                                            </span>
                                                        @elseif($order['payment_status'] == 'partially_paid')
                                                            <span class="badge badge-soft-warning">
                                                                {{ translate('messages.partially_paid') }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-soft-danger">
                                                                {{ translate('messages.unpaid') }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($order['order_note'])
                                        <div class="order-note mt-3">
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="tio-comment"></i>
                                                    {{ translate('messages.order_note') }}:
                                                </span>
                                                <span class="info-value text-muted">
                                                    {{ $order['order_note'] }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="order-info-right text-right">
                                    <div class="btn--container mb-3">
                                        <a class="btn btn--primary print--btn font-regular" 
                                           href="{{ route('admin.b2b-order.generate-invoice', [$order['id']]) }}">
                                            <i class="tio-print mr-sm-1"></i> 
                                            <span>{{ translate('messages.print_invoice') }}</span>
                                        </a>
                                    </div>
                                    <div class="order-status-info">
                                        <div class="info-item mb-2">
                                            <span class="info-label">
                                                {{ translate('status') }}:
                                            </span>
                                            <span class="info-value">
                                                @if ($order['order_status'] == 'pending')
                                                    <span class="badge badge-soft-info text-capitalize">
                                                        {{ translate('messages.pending') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'confirmed')
                                                    <span class="badge badge-soft-info text-capitalize">
                                                        {{ translate('messages.confirmed') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'processing')
                                                    <span class="badge badge-soft-warning text-capitalize">
                                                        {{ translate('messages.processing') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'accepted')
                                                    <span class="badge badge-soft-success text-capitalize">
                                                        {{ translate('messages.accepted') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'packaging')
                                                    <span class="badge badge-soft-warning text-capitalize">
                                                        {{ translate('messages.packaging') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'picked_up')
                                                    <span class="badge badge-soft-warning text-capitalize">
                                                        {{ translate('messages.out_for_delivery') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'delivered')
                                                    <span class="badge badge-soft-success text-capitalize">
                                                        {{ translate('messages.delivered') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'acknowledged')
                                                    <span class="badge badge-soft-primary text-capitalize">
                                                        {{ translate('messages.acknowledged') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'failed')
                                                    <span class="badge badge-soft-danger text-capitalize">
                                                        {{ translate('messages.payment_failed') }}
                                                    </span>
                                                @elseif($order['order_status'] == 'canceled')
                                                    <span class="badge badge-soft-danger text-capitalize">
                                                        {{ translate('messages.canceled') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-soft-danger text-capitalize">
                                                        {{ translate(str_replace('_', ' ', $order['order_status'])) }}
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Card Header -->

                    <!-- Card Body -->
                    <div class="card-body">
                        <!-- Customer & Store Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            <i class="tio-user"></i>
                                            {{ translate('messages.customer_information') }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($order->customer)
                                            <div class="media align-items-center">
                                                <div class="avatar avatar-circle">
                                                    <img class="avatar-img" 
                                                         src="{{ $order->customer->image_full_url ?? asset('public/assets/admin/img/100x100/food-default-image.png') }}" 
                                                         alt="Customer Image">
                                                </div>
                                                <div class="media-body pl-3">
                                                    <span class="fz--14px text-title font-semibold text-hover-primary d-block">
                                                        {{ $order->customer->f_name . ' ' . $order->customer->l_name }}
                                                    </span>
                                                    <span class="text-body d-block">
                                                        <i class="tio-call-talking-quiet"></i> 
                                                        {{ $order->customer->phone }}
                                                    </span>
                                                    <span class="text-title d-block">
                                                        <i class="tio-email"></i> 
                                                        {{ $order->customer->email }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-muted text-center py-3">
                                                <i class="tio-user-x"></i>
                                                {{ translate('messages.customer_not_found') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            <i class="tio-shop"></i>
                                            {{ translate('messages.store_information') }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($order->store)
                                            <div class="media align-items-center">
                                                <div class="avatar avatar-circle">
                                                    <img class="avatar-img" 
                                                         src="{{ $order->store->logo_full_url ?? asset('public/assets/admin/img/100x100/food-default-image.png') }}" 
                                                         alt="Store Logo">
                                                </div>
                                                <div class="media-body pl-3">
                                                    <span class="fz--14px text-title font-semibold text-hover-primary d-block">
                                                        {{ $order->store->name }}
                                                    </span>
                                                    <span class="text-body d-block">
                                                        <i class="tio-call-talking-quiet"></i> 
                                                        {{ $order->store->phone }}
                                                    </span>
                                                    <span class="text-title d-block">
                                                        <i class="tio-poi"></i> 
                                                        {{ Str::limit($order->store->address, 50) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-muted text-center py-3">
                                                <i class="tio-shop-x"></i>
                                                {{ translate('messages.store_not_found') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- B2B Assignment Information -->
                        @if($order->customer && $order->customer->b2bClients->count() > 0)
                            @php
                                $b2bClient = $order->customer->b2bClients->first();
                            @endphp
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">
                                                <i class="tio-delivery"></i>
                                                {{ translate('messages.assigned_delivery_man') }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $deliveryManId = $order->delivery_man_id ?? ($b2bClient->default_deliveryman_id ?? null);
                                                $deliveryMan = $deliveryManId ? \App\Models\DeliveryMan::find($deliveryManId) : null;
                                            @endphp
                                            @if($deliveryMan)
                                                <div class="media align-items-center">
                                                    <div class="avatar avatar-circle">
                                                        <img class="avatar-img" 
                                                             src="{{ $deliveryMan->image_full_url ?? asset('public/assets/admin/img/100x100/food-default-image.png') }}" 
                                                             alt="Delivery Man Image">
                                                    </div>
                                                    <div class="media-body pl-3">
                                                        <span class="fz--14px text-title font-semibold text-hover-primary d-block">
                                                            {{ $deliveryMan->f_name . ' ' . $deliveryMan->l_name }}
                                                        </span>
                                                        <span class="text-body d-block">
                                                            <i class="tio-call-talking-quiet"></i> 
                                                            {{ $deliveryMan->phone }}
                                                        </span>
                                                        <span class="text-title d-block">
                                                            <i class="tio-email"></i> 
                                                            {{ $deliveryMan->email }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-muted text-center py-3">
                                                    <i class="tio-user-x"></i>
                                                    {{ translate('messages.no_delivery_man_assigned') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">
                                                <i class="tio-package"></i>
                                                {{ translate('messages.assigned_packager') }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $packagerId = $order->packager_id ?? ($b2bClient->default_packager_id ?? null);
                                                $packager = $packagerId ? \App\Models\B2BPackager::find($packagerId) : null;
                                            @endphp
                                            @if($packager)
                                                <div class="media align-items-center">
                                                    <div class="avatar avatar-circle">
                                                        <img class="avatar-img" 
                                                             src="{{ $packager->image_full_url ?? asset('public/assets/admin/img/100x100/food-default-image.png') }}" 
                                                             alt="Packager Image">
                                                    </div>
                                                    <div class="media-body pl-3">
                                                        <span class="fz--14px text-title font-semibold text-hover-primary d-block">
                                                            {{ $packager->name }}
                                                        </span>
                                                        <span class="text-body d-block">
                                                            <i class="tio-call-talking-quiet"></i> 
                                                            {{ $packager->phone }}
                                                        </span>
                                                        <span class="text-title d-block">
                                                            <i class="tio-email"></i> 
                                                            {{ $packager->email }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-muted text-center py-3">
                                                    <i class="tio-user-x"></i>
                                                    {{ translate('messages.no_packager_assigned') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($order->quantity_modified)
                            <!-- Modification Notice -->
                            <div class="alert alert-warning mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="tio-warning mr-2"></i>
                                    <div>
                                        <strong>Order Modified</strong>
                                        <p class="mb-0 mt-1">
                                            This order has been modified on {{ $order->modified_at ? date('d M Y H:i', strtotime($order->modified_at)) : 'N/A' }}.
                                            @if($order->modification_reason)
                                                <br><strong>Reason:</strong> {{ $order->modification_reason }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Order Items -->
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="tio-shopping-cart"></i>
                                    {{ translate('messages.order_items') }}
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="border-0 pl-4">{{ translate('messages.item') }}</th>
                                                <th class="border-0 text-center">{{ translate('messages.quantity') }}</th>
                                                <th class="border-0 text-right">{{ translate('messages.unit_price') }}</th>
                                                <th class="border-0 text-right pr-4">{{ translate('messages.total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->details as $detail)
                                                <tr>
                                                    <td class="pl-4">
                                                        <div class="media align-items-center">
                                                            <img class="avatar avatar-lg mr-3" 
                                                                 src="{{ $detail->item->image_full_url ?? asset('public/assets/admin/img/100x100/food-default-image.png') }}" 
                                                                 alt="Item Image">
                                                            <div class="media-body">
                                                                <h5 class="text-hover-primary mb-0">
                                                                    {{ $detail->item->name ?? translate('messages.item_not_found') }}
                                                                </h5>
                                                                @if($detail->item && $detail->item->description)
                                                                    <span class="text-body font-size-sm">
                                                                        {{ Str::limit($detail->item->description, 100) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($detail->is_na)
                                                            <span class="badge badge-danger">
                                                                <i class="tio-close"></i> NA
                                                            </span>
                                                        @else
                                                            <span class="badge badge-soft-info">
                                                                {{ $detail->quantity }}
                                                                @if($detail->item && $detail->item->unit)
                                                                    {{ $detail->item->unit->unit }}
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="font-weight-bold">
                                                            {{ \App\CentralLogics\Helpers::format_currency($detail->price) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        <span class="font-weight-bold text-primary">
                                                            {{ \App\CentralLogics\Helpers::format_currency($detail->price * $detail->quantity) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="row mt-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            <i class="tio-money"></i>
                                            {{ translate('messages.payment_summary') }}
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-right pl-4">{{ translate('messages.subtotal') }}</td>
                                                        <td class="text-right pr-4">
                                                            {{ \App\CentralLogics\Helpers::format_currency($order->order_amount - $order->delivery_charge - $order->dm_tips) }}
                                                        </td>
                                                    </tr>
                                                    @if($order->delivery_charge > 0)
                                                        <tr>
                                                            <td class="text-right pl-4">{{ translate('messages.delivery_charge') }}</td>
                                                            <td class="text-right pr-4">
                                                                {{ \App\CentralLogics\Helpers::format_currency($order->delivery_charge) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @if($order->dm_tips > 0)
                                                        <tr>
                                                            <td class="text-right pl-4">{{ translate('messages.delivery_man_tips') }}</td>
                                                            <td class="text-right pr-4">
                                                                {{ \App\CentralLogics\Helpers::format_currency($order->dm_tips) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @if($order->coupon_discount_amount > 0)
                                                        <tr>
                                                            <td class="text-right pl-4 text-success">
                                                                {{ translate('messages.coupon_discount') }}
                                                            </td>
                                                            <td class="text-right pr-4 text-success">
                                                                -{{ \App\CentralLogics\Helpers::format_currency($order->coupon_discount_amount) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @if($order->store_discount_amount > 0)
                                                        <tr>
                                                            <td class="text-right pl-4 text-success">
                                                                {{ translate('messages.store_discount') }}
                                                            </td>
                                                            <td class="text-right pr-4 text-success">
                                                                -{{ \App\CentralLogics\Helpers::format_currency($order->store_discount_amount) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr class="border-top">
                                                        <td class="text-right pl-4 font-weight-bold">
                                                            {{ translate('messages.total') }}
                                                        </td>
                                                        <td class="text-right pr-4 font-weight-bold text-primary">
                                                            <span class="h5 mb-0">
                                                                {{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Card Body -->
                </div>
                <!-- End Main Order Card -->
            </div>

            <div class="col-lg-4 order-print-area-right">
                <!-- Delivery Information -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="tio-location"></i>
                            {{ translate('messages.delivery_information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($order->delivery_address)
                            @php
                                $address = json_decode($order->delivery_address, true);
                            @endphp
                            <div class="mb-3">
                                <strong class="d-block mb-1">
                                    <i class="tio-user"></i>
                                    {{ translate('messages.contact_person_name') }}
                                </strong>
                                <span class="text-muted">{{ $address['contact_person_name'] ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <strong class="d-block mb-1">
                                    <i class="tio-call-talking-quiet"></i>
                                    {{ translate('messages.contact_person_number') }}
                                </strong>
                                <span class="text-muted">{{ $address['contact_person_number'] ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <strong class="d-block mb-1">
                                    <i class="tio-poi"></i>
                                    {{ translate('messages.address') }}
                                </strong>
                                <span class="text-muted">{{ $address['address'] ?? 'N/A' }}</span>
                            </div>
                            @if(isset($address['latitude']) && isset($address['longitude']))
                                <div class="mb-3">
                                    <strong class="d-block mb-1">
                                        <i class="tio-gps"></i>
                                        {{ translate('messages.coordinates') }}
                                    </strong>
                                    <span class="text-muted">{{ $address['latitude'] }}, {{ $address['longitude'] }}</span>
                                </div>
                            @endif
                        @else
                            <div class="text-muted text-center py-3">
                                <i class="tio-location-off"></i>
                                {{ translate('messages.no_delivery_address') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Delivery Proof -->
                @if($order->order_proof && is_array($order->order_proof_full_url) && count($order->order_proof_full_url) > 0)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="tio-camera"></i>
                                {{ translate('messages.delivery_proof') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="delivery-proof-gallery">
                                @foreach($order->order_proof_full_url as $index => $proof_image)
                                    <div class="proof-image-container mb-3">
                                        <div class="proof-image-wrapper">
                                            <img src="{{ $proof_image }}" 
                                                 alt="Delivery Proof {{ $index + 1 }}" 
                                                 class="proof-image" 
                                                 onclick="openImageModal('{{ $proof_image }}', 'Delivery Proof {{ $index + 1 }}')"
                                                 loading="lazy">
                                            <div class="proof-image-overlay">
                                                <i class="tio-zoom-in"></i>
                                                <span>{{ translate('messages.click_to_view') }}</span>
                                            </div>
                                        </div>
                                        <div class="proof-image-info">
                                            <small class="text-muted">
                                                <i class="tio-calendar"></i>
                                                {{ translate('messages.uploaded_on') }}: {{ date('d M Y H:i', strtotime($order->delivered ?? $order->updated_at)) }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif($order->order_status == 'delivered')
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="tio-camera"></i>
                                {{ translate('messages.delivery_proof') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-muted text-center py-3">
                                <i class="tio-camera-off" style="font-size: 2rem; color: #dee2e6;"></i>
                                <p class="mb-0 mt-2">{{ translate('messages.no_delivery_proof_available') }}</p>
                                <small>{{ translate('messages.delivery_man_did_not_upload_proof') }}</small>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Order Timeline -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="tio-timeline"></i>
                            {{ translate('messages.order_timeline') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title text-success">
                                        <i class="tio-shopping-cart"></i>
                                        {{ translate('messages.order_placed') }}
                                    </h6>
                                    <p class="timeline-text">{{ date('d M Y H:i', strtotime($order->created_at)) }}</p>
                                </div>
                            </div>
                            @if($order->confirmed)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title text-info">
                                            <i class="tio-checkmark-circle"></i>
                                            {{ translate('messages.order_confirmed') }}
                                        </h6>
                                        <p class="timeline-text">{{ date('d M Y H:i', strtotime($order->confirmed)) }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($order->processing_time)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title text-warning">
                                            <i class="tio-settings"></i>
                                            {{ translate('messages.order_processing') }}
                                        </h6>
                                        <p class="timeline-text">{{ date('d M Y H:i', strtotime($order->processing_time)) }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($order->packaging_time)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title text-warning">
                                            <i class="tio-package"></i>
                                            {{ translate('messages.order_packaging') }}
                                        </h6>
                                        <p class="timeline-text">{{ date('d M Y H:i', strtotime($order->packaging_time)) }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($order->picked_up)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title text-warning">
                                            <i class="tio-delivery"></i>
                                            {{ translate('messages.order_picked_up') }}
                                        </h6>
                                        <p class="timeline-text">{{ date('d M Y H:i', strtotime($order->picked_up)) }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($order->delivered)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title text-success">
                                            <i class="tio-checkmark-circle"></i>
                                            {{ translate('messages.order_delivered') }}
                                        </h6>
                                        <p class="timeline-text">{{ date('d M Y H:i', strtotime($order->delivered)) }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">{{ translate('messages.delivery_proof') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Delivery Proof" class="img-fluid" style="max-height: 70vh;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('messages.close') }}</button>
                    <a id="downloadImage" href="" download class="btn btn-primary">
                        <i class="tio-download"></i> {{ translate('messages.download') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        function openImageModal(imageSrc, imageTitle) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModalLabel').textContent = imageTitle;
            document.getElementById('downloadImage').href = imageSrc;
            $('#imageModal').modal('show');
        }
    </script>
    
    <style>
        /* Delivery Proof Styles */
        .delivery-proof-gallery {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .proof-image-container {
            position: relative;
        }
        
        .proof-image-wrapper {
            position: relative;
            display: inline-block;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .proof-image-wrapper:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .proof-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
        }
        
        .proof-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 8px;
        }
        
        .proof-image-wrapper:hover .proof-image-overlay {
            opacity: 1;
        }
        
        .proof-image-overlay i {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .proof-image-overlay span {
            font-size: 0.8rem;
            text-align: center;
        }
        
        .proof-image-info {
            margin-top: 8px;
            padding-left: 5px;
        }
        
        .proof-image-info small {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Modal Enhancements */
        #imageModal .modal-body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        #modalImage {
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Responsive Design */
        @media (min-width: 768px) {
            .delivery-proof-gallery {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            
            .proof-image {
                height: 180px;
            }
        }
        
        @media (min-width: 992px) {
            .proof-image {
                height: 200px;
            }
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 25px;
        }
        
        .timeline-marker {
            position: absolute;
            left: -35px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #dee2e6;
        }
        
        .timeline-content {
            padding-left: 15px;
            border-left: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
        
        .timeline-title {
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .timeline-text {
            color: #6c757d;
            font-size: 0.8rem;
            margin-bottom: 0;
        }
        
        .card-header {
            border-bottom: 1px solid #dee2e6;
        }
        
        .avatar {
            border-radius: 50%;
            overflow: hidden;
        }
        
        .avatar-circle {
            width: 50px;
            height: 50px;
        }
        
        .avatar-lg {
            width: 60px;
            height: 60px;
        }
        
        .badge-soft-info {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }
        
        .badge-soft-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .badge-soft-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .badge-soft-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .badge-soft-primary {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .text-hover-primary:hover {
            color: #007bff !important;
        }
        
        .border {
            border: 1px solid #dee2e6 !important;
        }
        
        .bg-light {
            background-color: #f8f9fa !important;
        }
        
        /* Order Header Layout Styles */
        .order-info-left {
            padding-right: 20px;
        }
        
        .order-info-right {
            padding-left: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            font-weight: 500;
            color: #212529;
            text-align: right;
        }
        
        .order-meta-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .order-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
        }
        
        .order-status-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }
        
        .page-header-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        @media (max-width: 768px) {
            .order-info-left {
                padding-right: 0;
                margin-bottom: 20px;
            }
            
            .order-info-right {
                padding-left: 0;
                text-align: left !important;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .info-value {
                text-align: left;
            }
        }
    </style>
@endpush 