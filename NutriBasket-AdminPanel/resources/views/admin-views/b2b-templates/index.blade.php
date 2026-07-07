@extends('layouts.admin.app')

@section('title', 'B2B Templates')

@section('content')
<div class="content container-fluid">
    <!-- Client Filter -->
    <div class="row mb-3">
        <div class="col-md-4">
            <form method="GET" action="">
                <div class="form-group mb-0">
                    <label for="client_id" class="font-weight-bold">Filter by Client</label>
                    <select name="client_id" id="client_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @if(isset($clientId) && $clientId == $client->id) selected @endif>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-template"></i>
                </span>
                <span>B2B Templates</span>
                <span class="badge badge-soft-dark ml-2">{{$templates->count()}}</span>
            </h1>
            <button class="btn btn--primary" data-toggle="modal" data-target="#addTemplateModal">
                <i class="tio-add"></i>
                Add Template
            </button>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <i class="tio-template"></i>
                        </span>
                        <span>Template List</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{translate('messages.sl')}}</th>
                                    <th class="border-0">Template Name</th>
                                    <th class="border-0">User</th>
                                    <th class="border-0">Status</th>
                                    <th class="text-center border-0">{{translate('messages.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $key => $template)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="media-body ml-3">
                                                    <span class="d-block h5 text-hover-primary mb-0">{{ $template->name }}</span>
                                                    @if($template->description)
                                                        <span class="d-block font-size-sm text-muted">{{ Str::limit($template->description, 30) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="media align-items-center">
                                                    <div class="media-body ml-2">
                                                        <span class="d-block font-size-sm font-weight-bold">{{ $template->user->f_name ?? '' }} {{ $template->user->l_name ?? '' }}</span>
                                                        <span class="d-block font-size-xs text-muted">{{ $template->user->email ?? '' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge badge-soft-success">
                                                    <i class="tio-checkmark-circle"></i> Active
                                                </span>
                                            @else
                                                <span class="badge badge-soft-danger">
                                                    <i class="tio-clear"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <button class="btn btn-sm btn-outline-info mr-1" data-toggle="modal" data-target="#viewTemplateModal-{{ $template->id }}" title="View Items">
                                                    <i class="tio-visible"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary mr-1" data-toggle="modal" data-target="#editTemplateModal-{{ $template->id }}" title="Edit Template">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="form_alert('template-{{ $template->id }}','Want to delete this template ?')" title="Delete">
                                                    <i class="tio-delete-outlined"></i>
                                                </button>
                                                <form action="{{ route('admin.users.b2b-templates.destroy', $template->id) }}" method="post" id="template-{{ $template->id }}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="empty--data">
                                                <img src="{{asset('/public/assets/admin/img/empty.png')}}" alt="public">
                                                <h5>{{translate('messages.no_data_found')}}</h5>
                                                <p>{{translate('messages.no_templates_found')}}</p>
                                            </div>
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

    <!-- All Modals Rendered After Table -->
    @foreach($templates as $template)
        <!-- View Template Modal for each template -->
        <div class="modal fade" id="viewTemplateModal-{{ $template->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="tio-template mr-2"></i>
                            Template Details - {{ $template->name }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Template Name:</label>
                                    <p class="mb-0">{{ $template->name }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">User:</label>
                                    <p class="mb-0">{{ $template->user->f_name ?? '' }} {{ $template->user->l_name ?? '' }}</p>
                                    <small class="text-muted">{{ $template->user->email ?? '' }}</small>
                                </div>
                            </div>
                            @if($template->description)
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Description:</label>
                                    <p class="mb-0">{{ $template->description }}</p>
                                </div>
                            </div>
                            @endif
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Status:</label>
                                    <p class="mb-0">
                                        @if($template->is_active)
                                            <span class="badge badge-soft-success">
                                                <i class="tio-checkmark-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="badge badge-soft-danger">
                                                <i class="tio-clear"></i> Inactive
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">Created:</label>
                                    <p class="mb-0">{{ $template->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <i class="tio-list mr-2"></i>
                                Template Items ({{ $template->items->count() }})
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($template->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->item->image)
                                                        <img src="{{ asset('storage/app/public/product/'.$item->item->image) }}" 
                                                             alt="{{ $item->item->name }}" 
                                                             class="rounded-circle mr-2" 
                                                             style="width: 30px; height: 30px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-2" 
                                                             style="width: 30px; height: 30px;">
                                                            <i class="tio-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <span class="font-weight-bold">{{ $item->item->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-soft-info">{{ $item->quantity }}</span>
                                            </td>
                                            <td>
                                                @if($item->notes)
                                                    <span class="text-muted">{{ $item->notes }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Template Modal for each template -->
        <div class="modal fade" id="editTemplateModal-{{ $template->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.users.b2b-templates.update', $template->id) }}">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="tio-edit mr-2"></i>
                                Edit Template - {{ $template->name }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">Template Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $template->name }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">User</label>
                                        <select name="user_id" class="form-control" required>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $template->user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->f_name ?? '' }} {{ $user->l_name ?? '' }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="input-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ $template->description }}</textarea>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">
                                            <input type="checkbox" name="is_active" {{ $template->is_active ? 'checked' : '' }}>
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6>
                                <i class="tio-list mr-2"></i>
                                Template Items
                            </h6>
                            <div id="items-container-{{ $template->id }}">
                                @foreach($template->items as $index => $item)
                                    <div class="row item-row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label class="input-label">Item</label>
                                                <select name="items[{{ $index }}][item_id]" class="form-control" required>
                                                    @foreach($items as $product)
                                                        <option value="{{ $product->id }}" {{ $item->item_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label class="input-label">Quantity</label>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control" value="{{ $item->quantity }}" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label class="input-label">Notes</label>
                                                <input type="text" name="items[{{ $index }}][notes]" class="form-control" value="{{ $item->notes }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-1">
                                            <div class="form-group">
                                                <label class="input-label">&nbsp;</label>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item" onclick="removeItem(this)">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem({{ $template->id }})">
                                <i class="tio-add"></i> Add Item
                            </button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                            <button type="submit" class="btn btn-primary">Update Template</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Add Template Modal -->
    <div class="modal fade" id="addTemplateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.b2b-templates.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="tio-add mr-2"></i>
                            Add New Template
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="input-label">Template Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="input-label">User</label>
                                    <select name="user_id" class="form-control" required>
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->f_name ?? '' }} {{ $user->l_name ?? '' }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="input-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6>
                            <i class="tio-list mr-2"></i>
                            Template Items
                        </h6>
                        <div id="items-container">
                            <div class="row item-row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="input-label">Item</label>
                                        <select name="items[0][item_id]" class="form-control" required>
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="input-label">Quantity</label>
                                        <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="input-label">Notes</label>
                                        <input type="text" name="items[0][notes]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="form-group">
                                        <label class="input-label">&nbsp;</label>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" onclick="removeItem(this)">
                                            <i class="tio-delete"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                            <i class="tio-add"></i> Add Item
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                        <button type="submit" class="btn btn-primary">Create Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    let itemIndex = 1;
    function addItem(templateId = null) {
        const container = templateId ? document.getElementById(`items-container-${templateId}`) : document.getElementById('items-container');
        const newRow = document.createElement('div');
        newRow.className = 'row item-row';
        newRow.innerHTML = `
            <div class="col-sm-5">
                <div class="form-group">
                    <label class="input-label">Item</label>
                    <select name="items[${itemIndex}][item_id]" class="form-control" required>
                        <option value="">Select Item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label class="input-label">Quantity</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" required>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label class="input-label">Notes</label>
                    <input type="text" name="items[${itemIndex}][notes]" class="form-control">
                </div>
            </div>
            <div class="col-sm-1">
                <div class="form-group">
                    <label class="input-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" onclick="removeItem(this)">
                        <i class="tio-delete"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
    }
    function removeItem(button) {
        const itemRow = button.closest('.item-row');
        const container = itemRow.parentElement;
        if (container.querySelectorAll('.item-row').length > 1) {
            itemRow.remove();
        } else {
            alert('At least one item is required.');
        }
    }
</script>
@endpush 