@extends('layouts.admin.app')

@section('title', translate('Inventory Management'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('Inventory Management')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Nav Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="inventoryTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="purchase-tab" data-toggle="tab" href="#purchase" role="tab" aria-controls="purchase" aria-selected="true">
                                    <i class="tio-shopping-cart"></i> {{translate('Purchase Management')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="wastage-tab" data-toggle="tab" href="#wastage" role="tab" aria-controls="wastage" aria-selected="false">
                                    <i class="tio-delete"></i> {{translate('Wastage Management')}}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="inventoryTabContent">
                            <!-- Purchase Management Tab -->
                            <div class="tab-pane fade show active" id="purchase" role="tabpanel" aria-labelledby="purchase-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">{{translate('Add Purchase')}}</h5>
                                            </div>
                                            <div class="card-body">
                                                <a href="{{ route('admin.inventory.purchase.index') }}" class="btn btn-primary">
                                                    <i class="tio-plus"></i> {{translate('Add Purchase')}}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Wastage Management Tab -->
                            <div class="tab-pane fade" id="wastage" role="tabpanel" aria-labelledby="wastage-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">{{translate('Add Wastage')}}</h5>
                                            </div>
                                            <div class="card-body">
                                                <a href="{{ route('admin.inventory.wastage.index') }}" class="btn btn-warning">
                                                    <i class="tio-delete"></i> {{translate('Add Wastage')}}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title">{{translate('Wastage Categories')}}</h5>
                                            </div>
                                            <div class="card-body">
                                                <a href="{{ route('admin.inventory.category.index') }}" class="btn btn-secondary">
                                                    <i class="tio-category"></i> {{translate('Manage Categories')}}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Initialize tabs
        $('#inventoryTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>
@endpush 