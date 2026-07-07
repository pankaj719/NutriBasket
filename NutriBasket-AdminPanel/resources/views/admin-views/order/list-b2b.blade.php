@extends('layouts.admin.app')

@section('title', 'B2B Order List')

@section('content')
<style>
.order-items {
    max-height: 150px;
    overflow-y: auto;
}

.item-row {
    padding: 2px 0;
    border-bottom: 1px solid #f0f0f0;
}

.item-row:last-child {
    border-bottom: none;
}

.item-name {
    font-weight: 500;
    color: #333;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}
</style>
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            @if($status == 'manager_packaged')
                B2B Packagers
            @elseif($status == 'manager_deliveryman')
                B2B Deliveryman
            @elseif($status == 'on_the_way')
                On My Way
            @else
                B2B {{ ucfirst($status) }} Orders
            @endif
            <span class="badge badge-soft-dark ml-2">{{ $total }}</span>
        </h1>
    </div>
    <div class="card">
        <div class="card-body">
            @if ($status == 'pending')
                <form id="process-selected-form" action="{{ route('admin.b2b-order.process-selected') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary mb-2" id="process-selected-btn" disabled>
                        Process Selected
                    </button>
                    <div class="table-responsive datatable-custom">
                        <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                            <thead class="thead-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-pending-orders">
                                    </th>
                                    <th class="border-0">{{translate('messages.sl')}}</th>
                                    <th class="table-column-pl-0 border-0">{{translate('messages.order_id')}}</th>
                                    <th class="border-0">{{translate('messages.order_date')}}</th>
                                    <th class="border-0">{{translate('messages.customer_information')}}</th>
                                    <th class="border-0">{{translate('messages.store')}}</th>
                                    <th class="text-center border-0">{{translate('messages.Item_Quantity')}}</th>
                                    <th class="border-0">{{translate('messages.total_amount')}}</th>
                                    <th class="text-center border-0">{{translate('messages.order_status')}}</th>
                                    <th class="text-center border-0">{{translate('messages.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody id="set-rows">
                                @foreach($orders as $key => $order)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="order-checkbox" name="order_ids[]" value="{{ $order->id }}">
                                        </td>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $order->id }}</td>
                                        <td>
                                            {{ optional($order->created_at instanceof \Carbon\Carbon ? $order->created_at : \Carbon\Carbon::parse($order->created_at))->format('Y-m-d H:i') }}
                                        </td>
                                        <td>
                                            @if($order->customer)
                                                {{ $order->customer->f_name }} {{ $order->customer->l_name }}<br>
                                                {{ $order->customer->phone }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{ $order->store ? $order->store->name : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $totalQuantity = 0;
                                                $units = [];
                                                foreach($order->details as $detail) {
                                                    $totalQuantity += $detail->quantity;
                                                    if($detail->item && $detail->item->unit) {
                                                        $unitName = $detail->item->unit->unit;
                                                        if(!in_array($unitName, $units)) {
                                                            $units[] = $unitName;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            {{ $totalQuantity }}
                                            @if(count($units) > 0)
                                                <br><small class="text-muted">{{ implode(', ', $units) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $order->order_amount ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ $order->order_status }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.b2b-order.details', $order->id) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="tio-visible"></i>
                                            </a>
                                            {{-- <a href="{{ route('admin.b2b-order.edit', $order->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="tio-edit"></i>
                                            </a> --}}
                                            {{-- <a href="{{ route('admin.b2b-order.delete', $order->id) }}" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this order?')">
                                                <i class="tio-delete"></i>
                                            </a> --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                @if(count($orders) !== 0)
                    <hr>
                @endif
                <div class="page-area">
                    {!! $orders->appends($_GET)->links() !!}
                </div>
            @elseif($status == 'processing')
                <!-- Enhanced Order Acceptance View -->
                <div id="accept-orders-container">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success" id="accept-selected-btn" disabled onclick="acceptSelectedOrders()">
                                <i class="tio-checkmark"></i> Accept Selected Orders
                            </button>
                            <span class="ml-2 text-muted" id="selected-count">0 orders selected</span>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('admin.b2b-order.stock-difference-export', 'excel') }}" class="btn btn-primary mr-2">
                                <i class="tio-download"></i> Export Stock Report
                            </a>
                            <a href="{{ route('admin.inventory.purchase.index') }}" class="btn btn-info mr-2">
                                <i class="tio-shopping-cart"></i> Inventory Management
                            </a>
                        </div>
                    </div>
                    
                    @if(count($orders) !== 0)
                        <hr>
                    @endif
                    
                    <div class="table-responsive datatable-custom">
                        <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select-all-processing-orders">
                                    </th>
                                    <th class="border-0">{{translate('messages.sl')}}</th>
                                    <th class="table-column-pl-0 border-0">{{translate('messages.order_id')}}</th>
                                    <th class="border-0">{{translate('messages.order_date')}}</th>
                                    <th class="border-0">{{translate('messages.customer_information')}}</th>
                                    <th class="border-0">{{translate('messages.store')}}</th>
                                    <th class="text-center border-0">Items & Quantities</th>
                                    <th class="border-0">{{translate('messages.total_amount')}}</th>
                                    <th class="text-center border-0">{{translate('messages.order_status')}}</th>
                                    <th class="text-center border-0">{{translate('messages.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody id="set-rows">
                                @foreach($orders as $key => $order)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="processing-order-checkbox" name="order_ids[]" value="{{ $order->id }}">
                                        </td>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $order->id }}</td>
                                        <td>
                                            {{ optional($order->created_at instanceof \Carbon\Carbon ? $order->created_at : \Carbon\Carbon::parse($order->created_at))->format('Y-m-d H:i') }}
                                        </td>
                                        <td>
                                            @if($order->customer)
                                                {{ $order->customer->f_name }} {{ $order->customer->l_name }}<br>
                                                {{ $order->customer->phone }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{ $order->store ? $order->store->name : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="order-items">
                                                @foreach($order->details as $detail)
                                                    <div class="item-row">
                                                        <span class="item-name">{{ $detail->item ? $detail->item->name : 'N/A' }}</span>
                                                        <span class="badge badge-info ml-2">{{ $detail->quantity }} qty</span>
                                                        @if($detail->variation)
                                                            <small class="text-muted ml-1">({{ json_decode($detail->variation, true)[0]['type'] ?? '' }})</small>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            {{ $order->order_amount ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">{{ $order->order_status }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-success" onclick="acceptSingleOrder({{ $order->id }})">
                                                <i class="tio-checkmark"></i> Accept
                                            </button>
                                            <a href="{{ route('admin.b2b-order.edit-quantities', $order->id) }}" class="btn btn-sm btn-warning" title="Edit Quantities">
                                                <i class="tio-edit"></i> Edit
                                            </a>
                                            <a href="{{ route('admin.b2b-order.details', $order->id) }}" class="btn btn-sm btn-info">
                                                <i class="tio-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if(count($orders) !== 0)
                        <hr>
                    @endif
                    <div class="page-area">
                        {!! $orders->appends($_GET)->links() !!}
                    </div>
                </div>
            @elseif ($status == 'preparing')
                {{-- Preparing: 2 options --}}
                <style>
                    .custom-btn {
                        background-color: #005454 !important;
                        border-color: #005454 !important;
                        color: white !important;
                    }
                    .custom-btn.active {
                        background-color: #29a645 !important;
                        border-color: #29a645 !important;
                        color: white !important;
                    }
                    .custom-btn:hover {
                        background-color: #29a645 !important;
                        border-color: #29a645 !important;
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:hover {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:focus {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:active {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:not(:disabled):not(.disabled):active {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:not(:disabled):not(.disabled):active:focus {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle i {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:hover i {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:focus i {
                        color: white !important;
                    }
                    .custom-btn.dropdown-toggle:active i {
                        color: white !important;
                    }
                    .custom-btn i.tio-download-to {
                        color: white !important;
                    }
                    /* Additional specific rules for Export button text */
                    .js-hs-unfold-invoker.custom-btn {
                        color: white !important;
                    }
                    .js-hs-unfold-invoker.custom-btn * {
                        color: white !important;
                    }
                    .hs-unfold .custom-btn {
                        color: white !important;
                    }
                    .hs-unfold .custom-btn * {
                        color: white !important;
                    }
                    a.js-hs-unfold-invoker.btn.custom-btn {
                        color: white !important;
                    }
                    .hs-unfold .dropdown-toggle {
                        color: white !important;
                    }
                </style>
                <div class="mb-3">
                    <a href="{{ request()->fullUrlWithQuery(['preparing_view_type' => 'update_inventory']) }}" class="btn btn-sm custom-btn {{ $preparing_view_type == 'update_inventory' ? 'active' : '' }}">
                        Update Inventory
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['preparing_view_type' => 'accept_by_customer']) }}" class="btn btn-sm custom-btn {{ $preparing_view_type == 'accept_by_customer' ? 'active' : '' }}">
                        Accept Order by Customer
                    </a>
                </div>
                @if ($preparing_view_type == 'update_inventory')
                    <form action="{{ route('admin.b2b-order.update-inventory') }}" method="POST">
                        @csrf
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Current Stock</th>
                                    <th>Total Ordered</th>
                                    <th>Extra Needed</th>
                                    <th>Add Inventory</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventory_items as $index => $item)
                                    <tr>
                                        <td>{{ $item['item_id'] }}</td>
                                        <td>{{ $item['item_name'] }}</td>
                                        <td>{{ $item['stock'] }}</td>
                                        <td>{{ $item['total_ordered'] }}</td>
                                        <td>{{ $item['extra_needed'] }}</td>
                                        <td>
                                            <input type="hidden" name="inventory_updates[{{$index}}][item_id]" value="{{ $item['item_id'] }}">
                                            <input type="number" name="inventory_updates[{{$index}}][add_stock]" value="0" min="0" class="form-control" style="width:100px;">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-success">Add to Inventory</button>
                        </div>
                    </form>
                @elseif ($preparing_view_type == 'accept_by_customer')
                    <form id="accept-customer-orders-form" action="{{ route('admin.b2b-order.accept-customers') }}" method="POST">
                        @csrf
                        <button type="submit" name="action" value="all" class="btn btn-success mb-2">Accept All</button>
                        <button type="submit" name="action" value="selected" class="btn btn-primary mb-2" id="accept-selected-btn" disabled>Accept Selected</button>
                        <table class="table table-bordered">
                            <thead> 
                                <tr>
                                    <th><input type="checkbox" id="select-all-accept-customers"></th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer_items as $order)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="accept-customer-checkbox" name="order_ids[]" value="{{ $order->id }}">
                                        </td>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->customer ? $order->customer->f_name . ' ' . $order->customer->l_name : 'N/A' }}</td>
                                        <td>
                                            <ul>
                                                @foreach($order->details as $item)
                                                    <li>
                                                        {{ $item->item->name ?? 'N/A' }} 
                                                        ({{ $item->quantity }}
                                                        @if($item->item && $item->item->unit)
                                                            {{ $item->item->unit->unit }}
                                                        @endif
                                                        )
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>

                @endif
                        @elseif ($status == 'accepted')
                {{-- Accepted: Show individual orders with delivery man and packager assignments --}}
                <form id="bulk-assign-form" action="{{ route('admin.b2b-order.bulk-assign-and-package') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success mb-3" id="bulk-assign-btn" disabled>
                        <i class="tio-arrow-forward"></i> Process Selected
                    </button>
                    <div class="table-responsive datatable-custom">
                        <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">
                                        <input type="checkbox" id="select-all-accepted-orders">
                                    </th>
                                    <th class="border-0">{{translate('messages.sl')}}</th>
                                    <th class="border-0">Order ID</th>
                                    <th class="border-0">Customer Name</th>
                                    <th class="border-0">Address</th>
                                    <th class="border-0">Order Count</th>
                                    <th class="border-0">Assigned Delivery Man</th>
                                    <th class="border-0">Assigned Packager</th>
                                    <th class="text-center border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="set-rows">
                                @foreach($orders as $index => $order)
                                    @php
                                        $defaultDeliveryMan = $order->customer && $order->customer->b2bClients->first() ? $order->customer->b2bClients->first()->default_deliveryman_id : null;
                                        $defaultPackager = $order->customer && $order->customer->b2bClients->first() ? $order->customer->b2bClients->first()->default_packager_id : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="accepted-order-checkbox" name="order_ids[]" value="{{ $order->id }}">
                                        </td>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->customer ? $order->customer->f_name . ' ' . $order->customer->l_name : 'N/A' }}</td>
                                        <td>
                                            @if($order->delivery_address)
                                                @php
                                                    $addressData = json_decode($order->delivery_address, true);
                                                    $address = $addressData['address'] ?? $order->delivery_address;
                                                @endphp
                                                {{ $address }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $order->details ? $order->details->count() : 0 }}</td>
                                        <td>
                                            <form action="{{ route('admin.b2b-order.update-order-delivery-man') }}" method="POST" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                <select name="delivery_man_id" class="form-control" onchange="this.form.submit()">
                                                    <option value="">Select Delivery Man</option>
                                                    @if(isset($delivery_men) && $delivery_men->count() > 0)
                                                        @foreach($delivery_men as $dm)
                                                            <option value="{{ $dm->id }}" {{ ($order->delivery_man_id ? $order->delivery_man_id : $defaultDeliveryMan) == $dm->id ? 'selected' : '' }}>
                                                                {{ $dm->f_name }} {{ $dm->l_name }} ({{ $dm->phone }})
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.b2b-order.update-order-packager') }}" method="POST" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                <select name="packager_id" class="form-control" onchange="this.form.submit()">
                                                    <option value="">Select Packager</option>
                                                    @if(isset($packagers) && $packagers->count() > 0)
                                                        @foreach($packagers as $packager)
                                                            <option value="{{ $packager->id }}" {{ ($order->packager_id ? $order->packager_id : $defaultPackager) == $packager->id ? 'selected' : '' }}>
                                                                {{ $packager->name }} ({{ $packager->phone }})
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.b2b-order.assign-and-package') }}" method="POST" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="tio-arrow-forward"></i> Assign
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @elseif ($status == 'packaging')
                {{-- Packaging: Show orders in packaging state, same as pending table --}}
                <div class="table-responsive datatable-custom">
                    <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{translate('messages.sl')}}</th>
                                <th class="table-column-pl-0 border-0">{{translate('messages.order_id')}}</th>
                                <th class="border-0">{{translate('messages.order_date')}}</th>
                                <th class="border-0">{{translate('messages.customer_information')}}</th>
                                <th class="border-0">{{translate('messages.store')}}</th>
                                <th class="text-center border-0">{{translate('messages.Item_Quantity')}}</th>
                                <th class="border-0">{{translate('messages.total_amount')}}</th>
                                <th class="text-center border-0">{{translate('messages.order_status')}}</th>
                                <th class="text-center border-0">{{translate('messages.actions')}}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($orders as $key => $order)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ optional($order->created_at instanceof \Carbon\Carbon ? $order->created_at : \Carbon\Carbon::parse($order->created_at))->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($order->customer)
                                            {{ $order->customer->f_name }} {{ $order->customer->l_name }}<br>
                                            {{ $order->customer->phone }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->store ? $order->store->name : 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $order->details ? $order->details->sum('quantity') : 0 }}
                                    </td>
                                    <td>
                                        {{ $order->order_amount ?? 'N/A' }}
                                    </td>
                                    <td class="text-center">{{ $order->order_status }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.b2b-order.details', $order->id) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="tio-visible"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($orders) !== 0)
                    <hr>
                @endif
                <div class="page-area">
                    {!! $orders->appends($_GET)->links() !!}
                </div>
            @elseif ($status == 'picked_up' || $status == 'on_the_way')
                {{-- Picked Up/On My Way: Show orders in picked_up state with items --}}
                <div class="table-responsive datatable-custom">
                    <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{translate('messages.sl')}}</th>
                                <th class="table-column-pl-0 border-0">{{translate('messages.order_id')}}</th>
                                <th class="border-0">{{translate('messages.order_date')}}</th>
                                <th class="border-0">{{translate('messages.customer_information')}}</th>
                                <th class="border-0">{{translate('messages.store')}}</th>
                                <th class="text-center border-0">Items & Quantities</th>
                                <th class="border-0">{{translate('messages.total_amount')}}</th>
                                <th class="text-center border-0">{{translate('messages.order_status')}}</th>
                                <th class="text-center border-0">{{translate('messages.actions')}}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($orders as $key => $order)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ optional($order->created_at instanceof \Carbon\Carbon ? $order->created_at : \Carbon\Carbon::parse($order->created_at))->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($order->customer)
                                            {{ $order->customer->f_name }} {{ $order->customer->l_name }}<br>
                                            {{ $order->customer->phone }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->store ? $order->store->name : 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="order-items">
                                            @foreach($order->details as $detail)
                                                <div class="item-row">
                                                    <span class="item-name">{{ $detail->item ? $detail->item->name : 'N/A' }}</span>
                                                    <span class="badge badge-info ml-2">{{ $detail->quantity }} qty</span>
                                                    @if($detail->variation)
                                                        <small class="text-muted ml-1">({{ json_decode($detail->variation, true)[0]['type'] ?? '' }})</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        {{ $order->order_amount ?? 'N/A' }}
                                    </td>
                                    <td class="text-center">{{ $order->order_status }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.b2b-order.details', $order->id) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="tio-visible"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($orders) !== 0)
                    <hr>
                @endif
                <div class="page-area">
                    {!! $orders->appends($_GET)->links() !!}
                </div>
            @else
                {{-- Default: Show normal order table --}}
            <div class="table-responsive datatable-custom">
                    <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                    <thead class="thead-light">
                    <tr>
                                <th class="border-0">{{translate('messages.sl')}}</th>
                        <th class="table-column-pl-0 border-0">{{translate('messages.order_id')}}</th>
                        <th class="border-0">{{translate('messages.order_date')}}</th>
                        <th class="border-0">{{translate('messages.customer_information')}}</th>
                            <th class="border-0">{{translate('messages.store')}}</th>
                            <th class="text-center border-0">{{translate('messages.Item_Quantity')}}</th>
                        <th class="border-0">{{translate('messages.total_amount')}}</th>
                            <th class="text-center border-0">{{translate('messages.order_status')}}</th>
                        <th class="text-center border-0">{{translate('messages.actions')}}</th>
                    </tr>
                    </thead>
                    <tbody id="set-rows">
                            @include('admin-views.order.partials._b2b_table', ['orders' => $orders])
                    </tbody>
                </table>
            </div>
            @if(count($orders) !== 0)
            <hr>
            @endif
            <div class="page-area">
                {!! $orders->appends($_GET)->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
// Simple unified select all functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle pending orders
    const pendingSelectAll = document.getElementById('select-all-pending-orders');
    const pendingCheckboxes = document.querySelectorAll('#process-selected-form .order-checkbox');
    const pendingButton = document.getElementById('process-selected-btn');
    
    if (pendingSelectAll && pendingCheckboxes.length > 0) {
        pendingSelectAll.addEventListener('change', function() {
            pendingCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
            updatePendingButton();
        });
        
        pendingCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(pendingCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(pendingCheckboxes).some(cb => cb.checked);
                pendingSelectAll.checked = allChecked;
                pendingSelectAll.indeterminate = anyChecked && !allChecked;
                updatePendingButton();
            });
        });
        
        function updatePendingButton() {
            const checkedCount = document.querySelectorAll('#process-selected-form .order-checkbox:checked').length;
            if (pendingButton) {
                pendingButton.disabled = checkedCount === 0;
                pendingButton.textContent = `Process Selected (${checkedCount})`;
            }
        }
    }
    
    // Handle processing orders
    const processingSelectAll = document.getElementById('select-all-processing-orders');
    const processingCheckboxes = document.querySelectorAll('.processing-order-checkbox');
    const processingButton = document.getElementById('accept-selected-btn');
    const selectedCount = document.getElementById('selected-count');
    
    if (processingSelectAll && processingCheckboxes.length > 0) {
        processingSelectAll.addEventListener('change', function() {
            processingCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
            updateProcessingButton();
        });
        
        processingCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(processingCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(processingCheckboxes).some(cb => cb.checked);
                processingSelectAll.checked = allChecked;
                processingSelectAll.indeterminate = anyChecked && !allChecked;
                updateProcessingButton();
            });
        });
        
        function updateProcessingButton() {
            const checkedCount = document.querySelectorAll('.processing-order-checkbox:checked').length;
            if (processingButton) {
                processingButton.disabled = checkedCount === 0;
                processingButton.innerHTML = `<i class="tio-checkmark"></i> Accept Selected Orders (${checkedCount})`;
            }
            if (selectedCount) {
                selectedCount.textContent = `${checkedCount} orders selected`;
            }
        }
    }
    
    // Handle accepted orders
    const acceptedSelectAll = document.getElementById('select-all-accepted-orders');
    const acceptedForm = document.getElementById('bulk-assign-form');
    const acceptedCheckboxes = acceptedForm ? acceptedForm.querySelectorAll('.accepted-order-checkbox') : [];
    const acceptedButton = document.getElementById('bulk-assign-btn');
    
    if (acceptedSelectAll && acceptedCheckboxes.length > 0) {
        acceptedSelectAll.addEventListener('change', function() {
            acceptedCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
            updateAcceptedButton();
        });
        
        acceptedCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(acceptedCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(acceptedCheckboxes).some(cb => cb.checked);
                acceptedSelectAll.checked = allChecked;
                acceptedSelectAll.indeterminate = anyChecked && !allChecked;
                updateAcceptedButton();
            });
        });
        
        function updateAcceptedButton() {
            const checkedCount = acceptedForm ? acceptedForm.querySelectorAll('.accepted-order-checkbox:checked').length : 0;
            if (acceptedButton) {
                acceptedButton.disabled = checkedCount === 0;
                acceptedButton.innerHTML = `<i class="tio-arrow-forward"></i> Process Selected (${checkedCount})`;
            }
        }
    }
    
    // Handle accept customers (preparing state)
    const acceptCustomersSelectAll = document.getElementById('select-all-accept-customers');
    const acceptCustomersCheckboxes = document.querySelectorAll('.accept-customer-checkbox');
    const acceptCustomersButton = document.getElementById('accept-selected-btn');
    
    if (acceptCustomersSelectAll && acceptCustomersCheckboxes.length > 0) {
        acceptCustomersSelectAll.addEventListener('change', function() {
            acceptCustomersCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
            updateAcceptCustomersButton();
        });
        
        acceptCustomersCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(acceptCustomersCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(acceptCustomersCheckboxes).some(cb => cb.checked);
                acceptCustomersSelectAll.checked = allChecked;
                acceptCustomersSelectAll.indeterminate = anyChecked && !allChecked;
                updateAcceptCustomersButton();
            });
        });
        
        function updateAcceptCustomersButton() {
            const checkedCount = document.querySelectorAll('.accept-customer-checkbox:checked').length;
            if (acceptCustomersButton) {
                acceptCustomersButton.disabled = checkedCount === 0;
            }
        }
    }
});

// Global functions for processing orders
function selectAllOrders() {
    const checkboxes = document.querySelectorAll('.processing-order-checkbox');
    const selectAllCheckbox = document.getElementById('select-all-processing-orders');
    checkboxes.forEach(checkbox => checkbox.checked = true);
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    }
    updateAcceptButton();
}

function deselectAllOrders() {
    const checkboxes = document.querySelectorAll('.processing-order-checkbox');
    const selectAllCheckbox = document.getElementById('select-all-processing-orders');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
    updateAcceptButton();
}

function updateAcceptButton() {
    const checkedCount = document.querySelectorAll('.processing-order-checkbox:checked').length;
    const acceptSelectedBtn = document.getElementById('accept-selected-btn');
    const selectedCountSpan = document.getElementById('selected-count');
    
    if (acceptSelectedBtn) {
        acceptSelectedBtn.disabled = checkedCount === 0;
        acceptSelectedBtn.innerHTML = `<i class="tio-checkmark"></i> Accept Selected Orders (${checkedCount})`;
    }
    if (selectedCountSpan) {
        selectedCountSpan.textContent = `${checkedCount} orders selected`;
    }
}

function acceptSingleOrder(orderId) {
    if (confirm('Are you sure you want to accept this order? This will subtract items from inventory.')) {
        fetch('{{ route("admin.b2b-order.accept-selected") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                order_ids: [orderId]
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Toastr.success('Order accepted successfully!');
                location.reload();
            } else {
                Toastr.error('Error accepting order: ' + data.message);
            }
        })
        .catch(error => {
            Toastr.error('Error accepting order');
        });
    }
}

function acceptSelectedOrders() {
    const checkedOrders = document.querySelectorAll('.processing-order-checkbox:checked');
    
    if (checkedOrders.length === 0) {
        alert('Please select at least one order to accept');
        return;
    }
    
    if (confirm(`Are you sure you want to accept ${checkedOrders.length} selected orders? This will subtract items from inventory.`)) {
        const orderIds = [];
        checkedOrders.forEach(checkbox => orderIds.push(checkbox.value));
        
        fetch('{{ route("admin.b2b-order.accept-selected") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                order_ids: orderIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show detailed message
                let message = data.message;
                
                // If there are skipped orders, show more details
                if (data.skipped_orders && data.skipped_orders.length > 0) {
                    message += '\n\nSkipped orders due to insufficient stock:';
                    data.skipped_orders.forEach(skipped => {
                        message += `\n- Order #${skipped.order_id}`;
                        if (skipped.items) {
                            skipped.items.forEach(item => {
                                message += `\n  * ${item.item_name}: Available ${item.available}, Required ${item.required}`;
                            });
                        }
                    });
                }
                
                alert(message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error accepting orders: ' + error.message);
        });
    }
}

function changeProcessingView() {
    const viewType = document.getElementById('processing-view-type').value;
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('processing_view_type', viewType);
    window.location.href = currentUrl.toString();
}

function acceptCustomerOrders(customerId) {
    if (confirm('Are you sure you want to accept all orders for this customer? This will subtract items from inventory.')) {
        fetch('{{ route("admin.b2b-order.accept-customers") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                customer_id: customerId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Toastr.success('Orders accepted successfully!');
                location.reload();
            } else {
                Toastr.error('Error accepting orders: ' + data.message);
            }
        })
        .catch(error => {
            Toastr.error('Error accepting orders');
        });
    }
}
</script>
@endpush
@endsection