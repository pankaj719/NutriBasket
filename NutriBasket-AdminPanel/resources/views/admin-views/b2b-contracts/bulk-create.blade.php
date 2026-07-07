@extends('layouts.admin.app')

@section('title', 'Bulk Create Contract')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-file-text"></i>
                </span>
                <span>
                    Bulk Create Contract
                </span>
            </h1>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Instructions</h5>
                            <ul class="list-unstyled">
                                <li><i class="tio-check text-success"></i> Download the template file below</li>
                                <li><i class="tio-check text-success"></i> Fill in the ItemId and Price columns</li>
                                <li><i class="tio-check text-success"></i> ItemId must be a valid item ID from your system</li>
                                <li><i class="tio-check text-success"></i> Price must be a positive number</li>
                                <li><i class="tio-check text-success"></i> Fill in the contract details below</li>
                                <li><i class="tio-check text-success"></i> Upload the completed file</li>
                                <li><i class="tio-check text-success"></i> Click "Create Contract" to create contract with all items</li>
                            </ul>
                            <div class="alert alert-info mt-3">
                                <strong>Note:</strong> This will create a new contract with all the items and pricing in one operation.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Template Download</h5>
                            <div class="btn-group">
                                <a href="{{ route('admin.users.b2b.clients.contract.bulk-create.template') }}" class="btn btn-primary">
                                    <i class="tio-download"></i> Download Template
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <form action="{{ route('admin.users.b2b.clients.contract.bulk-create.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Select Client</label>
                                    <select name="client_id" class="form-control" required>
                                        <option value="">Select a client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Contract Name</label>
                                    <input type="text" name="contract_name" class="form-control" placeholder="Enter contract name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Upload Items File</label>
                                    <input type="file" name="items_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                    <small class="form-text text-muted">Supported formats: Excel (.xlsx, .xls) and CSV</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="tio-add"></i> Create Contract with Items
                                </button>
                                <a href="{{ route('admin.users.b2b.clients.contracts') }}" class="btn btn-secondary">
                                    <i class="tio-arrow-back"></i> Back to Contracts
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
// Add any additional JavaScript if needed
</script>
@endpush 