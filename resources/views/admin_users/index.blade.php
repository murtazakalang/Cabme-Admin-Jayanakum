@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor restaurantTitle">{{trans('lang.admin_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.admin_plural')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/zone.png') }}"></span>
                            <h3 class="mb-0">{{ trans('lang.admin_table') }}</h3>
                            <span class="counter ml-3 zone_count">{{$totalLength}}</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="table-list">

            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                           
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.admin_table') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.admin_user_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('admin-users.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_admin') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="userlist-topsearch-flex mb-3">
                                <div class="userlist-topsearch d-flex mb-0">
                                    <div id="users-table_filter" class="ml-auto">                                   
                                        <div class="form-group mb-0">
                                            <form method="GET" action="{{ url()->current() }}" id="perPageForm">
                                                <label for="per_page">{{ trans('lang.show') }}</label>
                                                <select name="per_page" id="per_page" class="form-control input-sm" onchange="document.getElementById('perPageForm').submit()">                                                    
                                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                                                    <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                                </select>
                                                <label>{{ trans('lang.entries') }}</label>
                                            </form>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive m-t-10">
                                <table id="adminTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            @can('admin-users.delete')
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                            class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                            @endcan
                                            <th>{{trans('lang.name')}}</th>
                                            <th>{{trans('lang.email')}}</th>
                                            <th>{{trans('lang.role')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                        @foreach($users as $user)
                                        <tr>
                                            @can('admin-users.delete')
                                            <td class="delete-all"><input type="checkbox" id="is_open_{{$user->id}}"
                                                    class="is_open" dataid="{{$user->id}}"><label
                                                    class="col-3 control-label" for="is_open_{{$user->id}}"></label>
                                            </td>
                                            @endcan
                                            <td>
                                                <a href="{{route('admin-users.edit', ['id' => $user->id])}}">{{ $user->name}}</a>
                                            </td>
                                            <td>
                                                {{ $user->email}}
                                            </td>
                                            <td>
                                                {{ $user->getRoleNames()->first() }}
                                            </td>
                                            <td class="action-btn">
                                                <a href="{{route('admin-users.edit', ['id' => $user->id])}}"><i
                                                        class="mdi mdi-lead-pencil" title="Edit"></i></a>
                                                @can('admin-users.delete')
                                                <a href="{{route('admin-users.delete', ['id' => $user->id])}}" class="delete-btn"><i
                                                        class="mdi mdi-delete"></i></a>
                                                        @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        {{trans('lang.showing')}} {{ $users->firstItem() }} {{trans('lang.to_small')}} {{ $users->lastItem() }} {{trans('lang.of')}} {{ $users->total() }} {{trans('lang.entries')}}
                                    </div>
                                   
                                    <div>
                                        {{ $users->links('pagination.pagination') }}
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
</div>
@endsection
@section('scripts')

<script type="text/javascript">

    $("#is_active").click(function () {
        $("#adminTable .is_open").prop('checked', $(this).prop('checked'));
    });
    
    $("#deleteAll").click(function () {
        if ($('#adminTable .is_open:checked').length) {
            if (confirm('Are You Sure want to Delete Selected Data ?')) {
                var arrayUsers = [];
                $('#adminTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');
                    arrayUsers.push(dataId);
                });
                arrayUsers = JSON.stringify(arrayUsers);
                var url = "{{url('admin-users/delete', 'id')}}";
                url = url.replace('id', arrayUsers);
                $(this).attr('href', url);
            }
        } else {
            alert('Please Select Any One Record .');
        }
    });

</script>
@endsection