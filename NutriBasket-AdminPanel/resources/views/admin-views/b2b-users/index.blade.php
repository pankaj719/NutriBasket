@extends('layouts.admin.app')

@section('title', 'B2B Managers')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-briefcase"></i>
                </span>
                <span>
                    B2B Managers
                </span>
                <span class="badge badge-soft-dark ml-2">{{$b2bUsers->total()}}</span>
            </h1>
            <button class="btn btn--primary" data-toggle="modal" data-target="#addB2BUserModal">
                <i class="tio-add"></i>
                Add B2B Manager
            </button>
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
                                    <th class="border-0">{{translate('messages.name')}}</th>
                                    <th class="border-0">{{translate('messages.email')}}</th>
                                    <th class="border-0">{{translate('messages.phone')}}</th>
                                    <th class="border-0">Login Credentials</th>
                                    <th class="border-0">Assigned Client(s)</th>
                                    <th class="text-center border-0">{{translate('messages.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($b2bUsers as $key => $user)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="media align-items-center">

                                                <div class="media-body ml-3">
                                                    <span class="d-block h5 text-hover-primary mb-0">{{ $user->f_name }} {{ $user->l_name }}</span>
                                                    <span class="d-block font-size-sm text-body">Manager</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? 'N/A' }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">Phone: {{ $user->phone ?? 'N/A' }}</small>
                                                <small class="text-muted">Password: <span class="text-info">Check success message</span></small>
                                                <small class="text-muted">Login via User App</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->b2bClients->count() > 0)
                                                @foreach($user->b2bClients as $client)
                                                    <span class="badge badge-soft-info">{{ $client->name }}</span>
                                                    @if(!$loop->last)<br>@endif
                                                @endforeach
                                            @else
                                                <span class="text-muted">{{translate('messages.not_assigned')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                @if($user->b2bClients->isEmpty())
                                                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#assignClientModal-{{ $user->id }}">
                                                        <i class="tio-add"></i>
                                                        {{translate('messages.assign')}}
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline-danger" onclick="form_alert('user-{{ $user->id }}','{{ translate('Want to delete this B2B Manager ?') }}')">
                                                    <i class="tio-delete-outlined"></i>
                                                </button>
                                                <form action="{{ route('admin.users.b2b.destroy', $user->id) }}" method="post" id="user-{{ $user->id }}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Assign Client Modal for each user -->
                                    @if($user->b2bClients->isEmpty())
                                    <div class="modal fade" id="assignClientModal-{{ $user->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('users.b2b.assign-client', $user->id) }}">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{translate('messages.assign_client_to')}} {{ $user->f_name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="input-label">{{translate('messages.select_client')}}</label>
                                                            <select name="client_id" class="form-control" required>
                                                                <option value="">{{translate('messages.select_client')}}</option>
                                                                @foreach($b2bClients as $client)
                                                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                                                        <button type="submit" class="btn btn-primary">{{translate('messages.assign')}}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <img class="mb-3 w-160" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">
                                            <p class="mb-0">{{translate('No_data_to_show')}}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer">
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            {{translate('messages.showing')}} {{$b2bUsers->firstItem()}} {{translate('messages.to')}} {{$b2bUsers->lastItem()}} {{translate('messages.of')}} {{$b2bUsers->total()}} {{translate('messages.results')}}
                        </div>
                        <div>
                            {{$b2bUsers->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add B2B Manager Modal -->
<div class="modal fade" id="addB2BUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.b2b.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{translate('messages.add_new_b2b_manager')}}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.first_name')}}</label>
                                <input name="f_name" class="form-control" placeholder="{{translate('messages.first_name')}}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.last_name')}}</label>
                                <input name="l_name" class="form-control" placeholder="{{translate('messages.last_name')}}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="input-label">{{translate('messages.email')}}</label>
                        <input name="email" type="email" class="form-control" placeholder="{{translate('messages.email')}}">
                    </div>
                    <div class="form-group">
                        <label class="input-label">{{translate('messages.phone')}}</label>
                        <input name="phone" type="text" class="form-control" placeholder="{{translate('messages.phone')}}" required>
                    </div>
                    <div class="form-group">
                        <label class="input-label">{{translate('messages.password')}}</label>
                        <input name="password" type="password" class="form-control" placeholder="{{translate('messages.password')}}" required>
                    </div>
                    <div class="form-group">
                        <label class="input-label">{{translate('messages.assign_to_client')}}</label>
                        <select name="client_id" class="form-control">
                            <option value="">{{translate('messages.select_client')}}</option>
                            @foreach($b2bClients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('messages.add')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
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
