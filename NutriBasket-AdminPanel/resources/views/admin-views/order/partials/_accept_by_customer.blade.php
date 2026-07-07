<form id="accept-preparing-orders-form" method="POST" action="{{ route('admin.b2b-order.preparing.accept-orders') }}">
    @csrf
    <button type="submit" class="btn btn-primary mb-2" id="accept-selected-btn" disabled>Accept Orders</button>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-accept-customers"></th>
                    <th>Order ID</th>
                    <th>Customer Name</th>
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
                                @foreach($order->details as $detail)
                                    <li>{{ $detail->item->name ?? 'N/A' }} ({{ $detail->quantity }})</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form> 