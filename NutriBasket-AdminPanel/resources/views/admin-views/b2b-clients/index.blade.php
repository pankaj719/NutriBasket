@extends('layouts.admin.app')

@section('title', 'B2B Clients')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-users-switch"></i>
                </span>
                <span>
                    B2B Clients
                </span>
                <span class="badge badge-soft-dark ml-2">{{$clients->count()}}</span>
            </h1>
            <div class="d-flex">
                <a href="{{ route('admin.users.b2b.clients.contract.bulk-create') }}" class="btn btn-outline-success mr-2">
                    <i class="tio-add"></i> Bulk Create Contract
                </a>
                <a href="{{ route('admin.users.b2b.clients.contract.bulk-import') }}" class="btn btn-outline-primary mr-2">
                    <i class="tio-upload"></i> Bulk Import Items
                </a>
                <button class="btn btn--primary" data-toggle="modal" data-target="#addClientModal">
                    <i class="tio-add"></i>
                    Add B2B Client
                </button>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{translate('messages.sl')}}</th>
                                    <th class="border-0">Client Name</th>
                                    <th class="border-0">Default Deliveryman</th>
                                    <th class="border-0">Default Packager</th>
                                    <th class="border-0">Address</th>
                                    <th class="text-center border-0">{{translate('messages.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $key => $client)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="media align-items-center">
                                                
                                                <div class="media-body ml-3">
                                                    <span class="d-block h5 text-hover-primary mb-0">{{ $client->name }}</span>
                                                    <span class="d-block font-size-sm text-body">Client</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($client->defaultDeliveryman)
                                                    <div class="media align-items-center">
                                                       
                                                        <div class="media-body ml-2">
                                                            <span class="d-block font-size-sm">{{ $client->defaultDeliveryman->f_name }} {{ $client->defaultDeliveryman->l_name }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not Assigned</span>
                                                @endif
                                                <button class="btn btn-sm btn-outline-primary ml-2" data-toggle="modal" data-target="#assignDeliverymanModal-{{ $client->id }}">
                                                    <i class="tio-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($client->defaultPackager)
                                                    <div class="media align-items-center">
                                                       
                                                        <div class="media-body ml-2">
                                                            <span class="d-block font-size-sm">{{ $client->defaultPackager->name }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not Assigned</span>
                                                @endif
                                                <button class="btn btn-sm btn-outline-primary ml-2" data-toggle="modal" data-target="#assignPackagerModal-{{ $client->id }}">
                                                    <i class="tio-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            @if($client->address)
                                                <span class="d-block font-size-sm">{{ $client->address }}</span>
                                            @else
                                                <span class="text-muted">Not Available</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-outline-info mr-1" data-toggle="modal" data-target="#viewClientModal-{{ $client->id }}" title="View Details">
                                                    <i class="tio-visible"></i>
                                                </button>
                                                <button class="btn btn-xs btn-outline-danger" onclick="form_alert('client-{{ $client->id }}','Want to delete this B2B Client ?')" title="Delete">
                                                    <i class="tio-delete-outlined"></i>
                                                </button>
                                                <form action="{{ route('admin.users.b2b.clients.destroy', $client->id) }}" method="post" id="client-{{ $client->id }}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Assign Deliveryman Modal for each client -->
                                    <div class="modal fade" id="assignDeliverymanModal-{{ $client->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.users.b2b.clients.update-default-deliveryman', $client->id) }}">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Assign Deliveryman to {{ $client->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="input-label">Select Deliveryman</label>
                                                            <select name="default_deliveryman_id" class="form-control" required>
                                                                <option value="">Select Deliveryman</option>
                                                                @foreach($deliverymen as $dm)
                                                                    <option value="{{ $dm->id }}" {{ $client->default_deliveryman_id == $dm->id ? 'selected' : '' }}>
                                                                        {{ $dm->f_name ?? '' }} {{ $dm->l_name ?? '' }} (ID: {{ $dm->id }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                                                        <button type="submit" class="btn btn-primary">Assign</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Assign Packager Modal for each client -->
                                    <div class="modal fade" id="assignPackagerModal-{{ $client->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.users.b2b.clients.update-default-packager', $client->id) }}">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Assign Packager to {{ $client->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="input-label">Select Packager</label>
                                                            <select name="default_packager_id" class="form-control" required>
                                                                <option value="">Select Packager</option>
                                                                @foreach($packagers as $packager)
                                                                    <option value="{{ $packager->id }}" {{ $client->default_packager_id == $packager->id ? 'selected' : '' }}>
                                                                        {{ $packager->name }} (ID: {{ $packager->id }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                                                        <button type="submit" class="btn btn-primary">Assign</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- View Client Modal for each client -->
                                    <div class="modal fade" id="viewClientModal-{{ $client->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Client Details - {{ $client->name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <strong>Client Name:</strong>
                                                            <p>{{ $client->name }}</p>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <strong>Address:</strong>
                                                            <p>{{ $client->address ?? 'Not Available' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <strong>Default Deliveryman:</strong>
                                                            <p>
                                                                @if($client->defaultDeliveryman)
                                                                    {{ $client->defaultDeliveryman->f_name }} {{ $client->defaultDeliveryman->l_name }}
                                                                @else
                                                                    Not Assigned
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <strong>Default Packager:</strong>
                                                            <p>
                                                                @if($client->defaultPackager)
                                                                    {{ $client->defaultPackager->name }}
                                                                @else
                                                                    Not Assigned
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <img class="mb-3 w-160" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">
                                            <p class="mb-0">No data to show</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($clients->hasPages())
        <div class="page-area">
            {!! $clients->links() !!}
        </div>
    @endif
</div>

<!-- Add B2B Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.b2b.clients.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New B2B Client</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="input-label">Client Name</label>
                        <input name="name" class="form-control" placeholder="Client Name" required>
                    </div>
                    <div class="form-group">
                        <label class="input-label">Contract End Date</label>
                        <input type="date" name="contract_end_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="input-label">Default Deliveryman</label>
                        <select name="default_deliveryman_id" class="form-control">
                            <option value="">Select Deliveryman</option>
                            @foreach($deliverymen as $dm)
                                <option value="{{ $dm->id }}">{{ $dm->f_name ?? '' }} {{ $dm->l_name ?? '' }} (ID: {{ $dm->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="input-label">Default Packager</label>
                        <select name="default_packager_id" class="form-control">
                            <option value="">Select Packager</option>
                            @foreach($packagers as $packager)
                                <option value="{{ $packager->id }}">{{ $packager->name }} (ID: {{ $packager->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="input-label">Address</label>
                        <input name="address" class="form-control" placeholder="Address">
                    </div>
                    <div class="form-group">
                        <label class="input-label">Items & Pricing</label>
                        <div id="contract-items-new">
                            <!-- Show at least one empty row for new contract -->
                            <div class="form-row align-items-center mb-2 contract-item-row">
                                <div class="col">
                                    <select name="items[0][item_id]" class="form-control" required>
                                        <option value="">Select Item</option>
                                        @foreach($allItems as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <input type="number" step="0.01" name="items[0][price]" placeholder="Price" class="form-control" required>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-danger btn-sm remove-item-btn" onclick="this.closest('.contract-item-row').remove();">
                                        <i class="tio-delete-outlined"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addContractItemRow('new')">
                            <i class="tio-add"></i> Add Item
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
function addContractItemRow(contractId) {
    var container = document.getElementById('contract-items-' + contractId);
    var existingRows = container.querySelectorAll('.contract-item-row');
    var idx = existingRows.length;
    var html = `
        <div class="form-row align-items-center mb-2 contract-item-row">
            <div class="col">
                <select name="items[${idx}][item_id]" class="form-control" required>
                    @foreach($allItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <input type="number" step="0.01" name="items[${idx}][price]" placeholder="Price" class="form-control" required>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger btn-sm remove-item-btn" onclick="this.closest('.contract-item-row').remove();">
                    <i class="tio-delete-outlined"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// Function to reindex form fields after removing items
function reindexContractItems(container) {
    var rows = container.querySelectorAll('.contract-item-row');
    rows.forEach(function(row, index) {
        var itemIdSelect = row.querySelector('select[name^="items["]');
        var priceInput = row.querySelector('input[name^="items["]');
        
        if (itemIdSelect) {
            itemIdSelect.name = `items[${index}][item_id]`;
        }
        if (priceInput) {
            priceInput.name = `items[${index}][price]`;
        }
    });
}

// Add event listener for remove buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item-btn')) {
        var row = e.target.closest('.contract-item-row');
        var container = row.closest('[id^="contract-items-"]');
        row.remove();
        if (container) {
            reindexContractItems(container);
        }
    }
});

function form_alert(id, message) {
    Swal.fire({
        title: '{{translate('messages.are_you_sure')}}',
        text: message,
        type: 'warning',
        showCancelButton: true,
        cancelButtonColor: 'default',
        confirmButtonColor: '#FC6A57',
        cancelButtonText: '{{translate('messages.no')}}',
        confirmButtonText: '{{translate('messages.yes')}}',
        reverseButtons: true
    }).then((result) => {
        if (result.value) {
            $('#'+id).submit()
        }
    })
}
</script>
@endpush