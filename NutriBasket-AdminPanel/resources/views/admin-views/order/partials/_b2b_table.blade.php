@foreach($orders as $key=>$order)

<tr class="status-{{$order['order_status']}} class-all">
    <td class="">
        {{$key+$orders->firstItem()}}
    </td>
    <td class="table-column-pl-0">
        <a href="{{route('admin.b2b-order.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
    </td>
    <td>
        <div>
            <div>
                {{ \App\CentralLogics\Helpers::date_format($order->created_at) }}
            </div>
            <div class="d-block text-uppercase">
                {{ \App\CentralLogics\Helpers::time_format($order->created_at) }}
            </div>
        </div>
    </td>
    <td>
        @if($order->is_guest)
            @php($customer_details = json_decode($order['delivery_address'],true))
            <strong>{{$customer_details['contact_person_name']}}</strong>
            <a href="tel:{{$customer_details['contact_person_number']}}">
                <div>{{$customer_details['contact_person_number']}}</div>
            </a>
        @elseif($order->customer)
            <a class="text-body" href="{{route('admin.customer.view',[$order['user_id']])}}">
                <strong>
                    <div> {{$order->customer['f_name'].' '.$order->customer['l_name']}}</div>
                </strong>
            </a>
            <a href="tel:{{$order->customer['phone']}}">
                <div>{{$order->customer['phone']}}</div>
            </a>
        @else
            <label class="badge badge-danger">{{translate('messages.invalid_customer_data')}}</label>
        @endif
    </td>
    <td>
        @if ($order->store)
            <div><a class="text--title" href="{{route('admin.store.view', $order->store_id)}}" alt="view store">{{Str::limit($order->store?$order->store->name:translate('messages.store deleted!'),20,'...')}}</a></div>
        @else
            <div>{{Str::limit(translate('messages.not_found'),20,'...')}}</div>
        @endif
    </td>
    <td class="text-center border-0">
        {{ $order?->details()?->count() }}
    </td>
    <td>
        <div class="text-right mw--85px">
            <div>
                {{\App\CentralLogics\Helpers::format_currency($order['order_amount'])}}
            </div>
            @if($order->payment_status=='paid')
            <strong class="text-success">
                {{translate('messages.paid')}}
            </strong>
            @elseif($order->payment_status=='partially_paid')
            <strong class="text-success">
                {{translate('messages.partially_paid')}}
            </strong>
            @else
            <strong class="text-danger">
                {{translate('messages.unpaid')}}
            </strong>
            @endif
        </div>
    </td>
    <td class="text-capitalize text-center">
        @if($order['order_status']=='pending')
            <span class="badge badge-soft-info">
              {{translate('messages.pending')}}
            </span>
        @elseif($order['order_status']=='confirmed')
            <span class="badge badge-soft-info">
              {{translate('messages.confirmed')}}
            </span>
        @elseif($order['order_status']=='processing')
            <span class="badge badge-soft-warning">
              {{translate('messages.processing')}}
            </span>
        @elseif($order['order_status']=='picked_up')
            <span class="badge badge-soft-warning">
              {{translate('messages.out_for_delivery')}}
            </span>
        @elseif($order['order_status']=='delivered')
            <span class="badge badge-soft-success">
              {{translate('messages.delivered')}}
            </span>
        @elseif($order['order_status']=='failed')
            <span class="badge badge-soft-danger">
              {{translate('messages.payment_failed')}}
            </span>
        @elseif($order['order_status']=='handover')
            <span class="badge badge-soft-danger">
              {{translate('messages.handover')}}
            </span>
        @elseif($order['order_status']=='canceled')
            <span class="badge badge-soft-danger">
              {{translate('messages.canceled')}}
            </span>
        @elseif($order['order_status']=='accepted')
            <span class="badge badge-soft-danger">
              {{translate('messages.accepted')}}
            </span>
        @elseif($order['order_status']=='refund_requested')
            <span class="badge badge-soft-danger">
              {{translate('messages.refund_requested')}}
            </span>
        @else
            <span class="badge badge-soft-danger">
              {{str_replace('_',' ',$order['order_status'])}}
            </span>
        @endif
        @if($order['order_type']=='take_away')
            <div class="text-info mt-1">
                {{translate('messages.take_away')}}
            </div>
        @else
            <div class="text-title mt-1">
              {{translate('messages.home Delivery')}}
            </div>
        @endif
    </td>
    <td>
        <div class="btn--container justify-content-center">
            <a class="ml-2 btn btn-sm btn--warning btn-outline-warning action-btn" href="{{route('admin.b2b-order.details',['id'=>$order['id']])}}">
                <i class="tio-invisible"></i>
            </a>
            <a class="ml-2 btn btn-sm btn--primary btn-outline-primary action-btn" href="{{route('admin.b2b-order.generate-invoice',['id'=>$order['id']])}}">
                <i class="tio-print"></i>
            </a>
        </div>
    </td>
</tr>

@endforeach

@if(count($orders) === 0)
<tr>
    <td colspan="9">
        <div class="empty--data">
            <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
            <h5>
                {{translate('no_data_found')}}
            </h5>
        </div>
    </td>
</tr>
@endif 