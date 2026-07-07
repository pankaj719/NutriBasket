@extends('layouts.admin.app')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <div class="d-flex align-items-center">
                    <i class="tio-cube mr-2"></i>
                    <h1 class="page-header-title">B2B Packagers</h1>
                </div>
            </div>
            <div class="col-sm-auto">
                <button class="btn btn-primary" data-toggle="modal" data-target="#addPackagerModal">
                    <i class="tio-add-circle mr-1"></i>
                    Add B2B Packager
                </button>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Card -->
    <div class="card">
        <div class="table-responsive datatable-custom">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th>{{translate('messages.sl')}}</th>
                        <th>Packager Information</th>
                        <th>Contact Details</th>
                        <th>Created Date</th>
                        <th class="text-center">{{translate('messages.action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packagers as $key => $packager)
                    <tr>
                        <td>{{$key+$packagers->firstItem()}}</td>
                        <td>
                            <div class="table-rest-info">
                                <div class="info">
                                    <span class="d-block text--title" title="{{ $packager->name }}">
                                        {{ Str::limit($packager->name, 20, '...') }}
                                    </span>
                                    <span class="d-block text-body font-size-sm">
                                        ID: #{{ $packager->id }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div>
                                    <a href="mailto:{{ $packager->email }}" class="text-dark">{{ $packager->email }}</a>
                                </div>
                                <div>
                                    <a href="tel:{{ $packager->phone }}" class="deco-none">{{ $packager->phone }}</a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div>{{ $packager->created_at->format('M d, Y') }}</div>
                                <div class="font-size-sm text-body">{{ $packager->created_at->format('h:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="btn--container justify-content-center">
                                <a class="btn action-btn btn--primary btn-outline-primary" 
                                   href="javascript:" 
                                   data-toggle="modal" 
                                   data-target="#editPackagerModal-{{ $packager->id }}"
                                   title="{{translate('messages.edit')}}">
                                    <i class="tio-edit"></i>
                                </a>
                                <a class="btn action-btn btn--danger btn-outline-danger form-alert" 
                                   href="javascript:" 
                                   data-id="packager-{{ $packager->id }}" 
                                   data-message="Want to delete this B2B packager?" 
                                   title="{{translate('messages.delete')}}">
                                    <i class="tio-delete-outlined"></i>
                                </a>
                                <form action="{{ route('admin.users.b2b-packagers.destroy', $packager->id) }}" 
                                      method="POST" 
                                      id="packager-{{ $packager->id }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="empty--data">
                                <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="No data" class="w-160">
                                <h5>{{translate('no_data_found')}}</h5>
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

    <!-- Pagination -->
    @if($packagers->hasPages())
        <div class="page-area">
            {!! $packagers->links() !!}
        </div>
    @endif
    <!-- End Pagination -->

    <!-- Add Packager Modal -->
    <div id="addPackagerModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.b2b-packagers.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Add New B2B Packager</h4>
                        <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal">
                            <i class="tio-clear tio-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="js-form-message form-group">
                                    <label class="input-label" for="packager-name">
                                        Full Name <i class="tio-help-outlined text-body ml-1" title="Packager full name"></i>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="name" 
                                           id="packager-name"
                                           placeholder="Enter packager full name" 
                                           required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="js-form-message form-group">
                                    <label class="input-label" for="packager-email">
                                        Email Address <i class="tio-help-outlined text-body ml-1" title="Must be unique email"></i>
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           name="email" 
                                           id="packager-email"
                                           placeholder="Enter email address" 
                                           required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="js-form-message form-group">
                                    <label class="input-label" for="packager-phone">Phone Number</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="phone" 
                                           id="packager-phone"
                                           placeholder="Enter phone number" 
                                           required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="js-form-message form-group">
                                    <label class="input-label" for="packager-password">
                                        Password <i class="tio-help-outlined text-body ml-1" title="Minimum 6 characters"></i>
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           name="password" 
                                           id="packager-password"
                                           placeholder="Enter password (minimum 6 characters)" 
                                           required 
                                           minlength="6">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal">{{translate('messages.cancel')}}</button>
                        <button type="submit" class="btn btn-primary">Add Packager</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Add Packager Modal -->

    <!-- Edit Packager Modals -->
    @foreach($packagers as $packager)
    <div id="editPackagerModal-{{ $packager->id }}" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.b2b-packagers.update', $packager->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h4 class="modal-title">Edit B2B Packager</h4>
                        <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal">
                            <i class="tio-clear tio-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="js-form-message form-group">
                                    <label class="input-label" for="edit-name-{{ $packager->id }}">Full Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="name" 
                                           id="edit-name-{{ $packager->id }}"
                                           value="{{ $packager->name }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="js-form-message form-group">
                                    <label class="input-label" for="edit-email-{{ $packager->id }}">Email Address</label>
                                    <input type="email" 
                                           class="form-control" 
                                           name="email" 
                                           id="edit-email-{{ $packager->id }}"
                                           value="{{ $packager->email }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="js-form-message form-group">
                                    <label class="input-label" for="edit-phone-{{ $packager->id }}">Phone Number</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="phone" 
                                           id="edit-phone-{{ $packager->id }}"
                                           value="{{ $packager->phone }}" 
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal">{{translate('messages.cancel')}}</button>
                        <button type="submit" class="btn btn-primary">Update Packager</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
    <!-- End Edit Packager Modals -->
</div>
@endsection

@push('script_2')
<script src="{{asset('public/assets/admin')}}/js/view-pages/common.js"></script>
@endpush