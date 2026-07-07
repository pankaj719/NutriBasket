{{-- filepath: resources/views/admin-views/order/stock-difference.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Stock Difference Report')

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Stock Difference Report</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Total Ordered</th>
                        <th>Stock</th>
                        <th>Extra Needed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report as $key => $row)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $row['item_id'] }}</td>
                            <td>{{ $row['item_name'] }}</td>
                            <td>{{ $row['total_ordered'] }}</td>
                            <td>{{ $row['stock'] }}</td>
                            <td>{{ $row['extra_needed'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection