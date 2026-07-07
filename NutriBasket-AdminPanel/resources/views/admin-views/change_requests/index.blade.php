{{-- resources/views/admin/change_requests/index.blade.php --}}
@extends('layouts.admin.app')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <div class="d-flex align-items-center">
                    <i class="tio-swap-horizontal mr-2"></i>
                    <h1 class="page-header-title">{{translate('messages.order_change_requests')}}</h1>
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="d-flex gap-2">
                    <span class="badge badge-soft-info badge-pill">
                        {{translate('messages.total_requests')}}: {{ $requests->count() }}
                    </span>
                    <span class="badge badge-soft-warning badge-pill">
                        {{translate('messages.pending')}}: {{ $requests->where('status', 'pending')->count() }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Card -->
    <div class="card" style="min-height: 60vh;">
        <div class="table-responsive datatable-custom" style="min-height: 60vh; max-height: 80vh; overflow-y: auto;">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light sticky-top">
                    <tr>
                        <th>{{translate('messages.sl')}}</th>
                        <th>{{translate('messages.request_info')}}</th>
                        <th>{{translate('messages.order_details')}}</th>
                        <th>{{translate('messages.item_details')}}</th>
                        <th>{{translate('messages.quantity_change')}}</th>
                        <th>{{translate('messages.status')}}</th>
                        <th>{{translate('messages.date')}}</th>
                        <th class="text-center">{{translate('messages.action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grouped = $requests->groupBy('order_id');
                        $serialNumber = 1;
                    @endphp

                    @forelse($grouped as $orderId => $orderRequests)
                        @php
                            $pendingCount = $orderRequests->where('status', 'pending')->count();
                            $totalRequests = $orderRequests->count();
                        @endphp
                        
                        <!-- Order Header Row -->
                        <tr class="table-divider">
                            <td colspan="8" class="bg-light py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="tio-shopping-cart mr-2"></i>
                                        <strong>{{translate('messages.order_id')}}: #{{ $orderId }}</strong>
                                        <span class="badge badge-soft-info badge-pill ml-2">
                                            {{ $totalRequests }} {{translate('messages.requests')}}
                                        </span>
                                        @if($pendingCount > 0)
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{ $pendingCount }} {{translate('messages.pending')}}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($pendingCount > 0)
                                        <div class="btn--container">
                                            <a class="btn btn-sm btn--success btn-outline-success form-alert" 
                                               href="javascript:" 
                                               data-id="approve-order-{{ $orderId }}" 
                                               data-message="{{translate('messages.want_to_approve_all_pending_requests_for_this_order')}}" 
                                               title="{{translate('messages.approve_all')}}">
                                                <i class="tio-done mr-1"></i>
                                                {{translate('messages.approve_all')}}
                                            </a>
                                            <a class="btn btn-sm btn--danger btn-outline-danger form-alert" 
                                               href="javascript:" 
                                               data-id="reject-order-{{ $orderId }}" 
                                               data-message="{{translate('messages.want_to_reject_all_pending_requests_for_this_order')}}" 
                                               title="{{translate('messages.reject_all')}}">
                                                <i class="tio-clear mr-1"></i>
                                                {{translate('messages.reject_all')}}
                                            </a>
                                            <form action="{{ route('admin.change_requests.order.approve', $orderId) }}" 
                                                  method="POST" 
                                                  id="approve-order-{{ $orderId }}">
                                                @csrf
                                            </form>
                                            <form action="{{ route('admin.change_requests.order.reject', $orderId) }}" 
                                                  method="POST" 
                                                  id="reject-order-{{ $orderId }}">
                                                @csrf
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Individual Request Rows -->
                        @foreach($orderRequests as $request)
                        <tr style="height: 60px;">
                            <td>{{ $serialNumber++ }}</td>
                            <td>
                                <div class="py-2">
                                    <span class="d-block text--title">{{translate('messages.request')}} #{{ $request->id }}</span>
                                    @if($request->reason)
                                        <span class="d-block font-size-sm text-body mt-1" title="{{ $request->reason }}">
                                            {{ Str::limit($request->reason, 30, '...') }}
                                        </span>
                                    @else
                                        <span class="d-block font-size-sm text-muted mt-1">{{translate('messages.no_reason_provided')}}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="py-2">
                                    <span class="d-block text-dark">#{{ $request->order_id }}</span>
                                    @if($request->order)
                                        <span class="d-block font-size-sm text-body mt-1">
                                            {{ $request->order->store->name ?? translate('messages.store_not_found') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="py-2">
                                    <span class="d-block text-dark">{{translate('messages.item_id')}}: #{{ $request->item_id }}</span>
                                    <span class="d-block font-size-sm text-body mt-1">{{translate('messages.order_item')}}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center py-2">
                                    <span class="badge badge-soft-info badge-pill mr-2">
                                        {{translate('messages.new')}}: {{ $request->new_quantity }}
                                    </span>
                                    <i class="tio-arrow-forward text-muted"></i>
                                </div>
                            </td>
                            <td>
                                <div class="py-2">
                                    @if($request->status == 'pending')
                                        <span class="badge badge-soft-warning">
                                            <i class="tio-clock mr-1"></i>
                                            {{translate('messages.pending')}}
                                        </span>
                                    @elseif($request->status == 'approved')
                                        <span class="badge badge-soft-success">
                                            <i class="tio-done mr-1"></i>
                                            {{translate('messages.approved')}}
                                        </span>
                                    @elseif($request->status == 'declined')
                                        <span class="badge badge-soft-danger">
                                            <i class="tio-clear mr-1"></i>
                                            {{translate('messages.declined')}}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="py-2">
                                    <div>{{ $request->created_at->format('M d, Y') }}</div>
                                    <div class="font-size-sm text-body mt-1">{{ $request->created_at->format('h:i A') }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center py-2">
                                    @if($request->status == 'pending')
                                        <a class="btn action-btn btn--success btn-outline-success form-alert" 
                                           href="javascript:" 
                                           data-id="approve-{{ $request->id }}" 
                                           data-message="{{translate('messages.want_to_approve_this_change_request')}}" 
                                           title="{{translate('messages.approve')}}">
                                            <i class="tio-done"></i>
                                        </a>
                                        <a class="btn action-btn btn--danger btn-outline-danger form-alert" 
                                           href="javascript:" 
                                           data-id="reject-{{ $request->id }}" 
                                           data-message="{{translate('messages.want_to_reject_this_change_request')}}" 
                                           title="{{translate('messages.reject')}}">
                                            <i class="tio-clear"></i>
                                        </a>
                                        <form action="{{ route('admin.change_requests.approve', $request->id) }}" 
                                              method="POST" 
                                              id="approve-{{ $request->id }}">
                                            @csrf
                                        </form>
                                        <form action="{{ route('admin.change_requests.reject', $request->id) }}" 
                                              method="POST" 
                                              id="reject-{{ $request->id }}">
                                            @csrf
                                        </form>
                                    @else
                                        <span class="text-muted font-size-sm">{{translate('messages.no_actions_available')}}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="height: 400px;">
                                <div class="empty--data d-flex flex-column justify-content-center align-items-center h-100">
                                    <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="No data" class="w-160 mb-3">
                                    <h5>{{translate('messages.no_change_requests_found')}}</h5>
                                    <p class="text-muted">{{translate('messages.no_change_requests_description')}}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- End Table -->
    </div>
    <!-- End Card -->
</div>
@endsection

@push('script_2')
<script src="{{asset('public/assets/admin')}}/js/view-pages/common.js"></script>
@endpush