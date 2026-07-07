@extends('layouts.admin.app')

@section('title', 'B2B Contracts')

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
                    B2B Contracts
                </span>
                <span class="badge badge-soft-dark ml-2">{{$contracts->count()}}</span>
            </h1>
            <div class="d-flex">
                <a href="{{ route('admin.users.b2b.clients.contract.bulk-create') }}" class="btn btn-success mr-2">
                    <i class="tio-add"></i> Bulk Create Contract
                </a>
                <a href="{{ route('admin.users.b2b.clients.contract.bulk-import') }}" class="btn btn-primary">
                    <i class="tio-upload"></i> Bulk Import Items
                </a>
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
                                    <th class="border-0">Contract Status</th>
                                    <th class="border-0">Contract End Date</th>
                                    <th class="border-0">Items Count</th>
                                    <th class="text-center border-0">{{translate('messages.action')}}</th>
                                </tr>   
                            </thead>
                            <tbody>
                                @forelse($contracts as $key => $contract)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="media-body ml-3">
                                                    <span class="d-block h5 text-hover-primary mb-0">{{ $contract->client ? $contract->client->name : 'N/A' }}</span>
                                                    <span class="d-block font-size-sm text-body">Contract ID: {{ $contract->id }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($contract->status === 'active')
                                                <span class="badge badge-soft-success">Active</span>
                                            @else
                                                <span class="badge badge-soft-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="d-block font-size-sm">{{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}</span>
                                            @if(\Carbon\Carbon::parse($contract->end_date)->isPast())
                                                <span class="badge badge-soft-warning">Expired</span>
                                            @elseif(\Carbon\Carbon::parse($contract->end_date)->diffInDays(now()) <= 30)
                                                <span class="badge badge-soft-info">Expiring Soon</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-primary">{{ $contract->items->count() }} Items</span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-xs btn-outline-primary mr-1" data-toggle="modal" data-target="#editContractModal-{{ $contract->id }}" title="Edit Contract">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('admin.users.b2b.contract.toggle', $contract->id) }}" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs {{ $contract->status === 'active' ? 'btn-outline-success' : 'btn-outline-danger' }} mr-1" title="{{ $contract->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                        @if($contract->status === 'active')
                                                            <i class="tio-toggle-on"></i>
                                                        @else
                                                            <i class="tio-toggle-off"></i>
                                                        @endif
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.users.b2b.clients.contract.export-items', $contract->id) }}" class="btn btn-xs btn-outline-info mr-1" title="Export Items">
                                                    <i class="tio-download"></i>
                                                </a>
                                                <button class="btn btn-xs btn-outline-danger" onclick="form_alert('contract-{{ $contract->id }}','Want to delete this B2B Contract ?')" title="Delete">
                                                    <i class="tio-delete-outlined"></i>
                                                </button>
                                                <form action="{{ route('admin.users.b2b.clients.contract.destroy', $contract->id) }}" method="post" id="contract-{{ $contract->id }}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Contract Modal for each contract -->
                                    <div class="modal fade" id="editContractModal-{{ $contract->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.users.b2b.contract.update', $contract->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Contract for {{ $contract->client ? $contract->client->name : 'N/A' }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="input-label">End Date</label>
                                                            <input type="date" name="end_date" value="{{ $contract->end_date }}" required class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">Items & Pricing</label>
                                                            <div id="contract-items-{{ $contract->id }}">
                                                                @if($contract->items->count() > 0)
                                                                    @foreach($contract->items as $idx => $contractItem)
                                                                        <div class="form-row align-items-center mb-2 contract-item-row">
                                                                            <div class="col">
                                                                                <select name="items[{{ $idx }}][item_id]" class="form-control" required>
                                                                                    @foreach($allItems as $item)
                                                                                        <option value="{{ $item->id }}" {{ $contractItem->item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col">
                                                                                <input type="number" step="0.01" name="items[{{ $idx }}][price]" value="{{ $contractItem->price }}" placeholder="Price" class="form-control" required>
                                                                            </div>
                                                                            <div class="col-auto">
                                                                                <button type="button" class="btn btn-danger btn-sm remove-item-btn" onclick="this.closest('.contract-item-row').remove();">
                                                                                    <i class="tio-delete-outlined"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <!-- Show at least one empty row if no items exist -->
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
                                                                @endif
                                                            </div>
                                                            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addContractItemRow({{ $contract->id }})">
                                                                        <i class="tio-add"></i> Add Item
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <img class="mb-3 w-160" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">
                                            <p class="mb-0">No contracts to show</p>
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
    @if($contracts->hasPages())
        <div class="page-area">
            {!! $contracts->links() !!}
        </div>
    @endif
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