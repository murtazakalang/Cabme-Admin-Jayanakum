@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor restaurantTitle">{{trans('lang.role_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.role_plural')}}</li>
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
                            <h3 class="mb-0">{{ trans('lang.role_table') }}</h3>
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
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.role_table') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.role_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('roles.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_role') }}</a>
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
                                <table id="roleTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            @can('roles.delete')
                                            <th class="delete-all">
                                                <input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="mdi mdi-delete"></i> {{trans('lang.all')}}
                                                    </a>
                                                </label>
                                            </th>
                                            @endcan
                                            <th>{{trans('lang.name')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                        @if (count($roles) > 0)
                                        @foreach($roles as $role)
                                                <tr>
                                                    @can('roles.delete')
                                                    <td class="delete-all">
                                                        @if($role->id != 1)
                                                        <input type="checkbox" id="is_open_{{$role->id}}" class="is_open" dataId="{{$role->id}}">
                                                        <label class="col-3 control-label" for="is_open_{{$role->id}}"></label>
                                                        @endif
                                                    </td>
                                                    @endcan
                                                    <td>
                                                        <a href="{{route('roles.edit', ['id' => $role->id])}}">{{ $role->name}}</a>
                                                    </td>
                                                    <td class="action-btn"> 
                                                        <a href="{{route('roles.edit', ['id' => $role->id])}}">
                                                            <i class="mdi mdi-lead-pencil" title="Edit"></i>
                                                        </a>
                                                        @can('roles.delete')
                                                        @if($role->id != 1)
                                                        <a href="{{route('roles.delete', ['id' => $role->id])}}">
                                                            <i class="mdi mdi-delete"></i>
                                                        </a>
                                                        @endif
                                                        @endcan
                                                    </td>
                                                </tr>
                                        @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" align="center">{{ trans('lang.no_result') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        {{trans('lang.showing')}} {{ $roles->firstItem() }} {{trans('lang.to_small')}} {{ $roles->lastItem() }} {{trans('lang.of')}} {{ $roles->total() }} {{trans('lang.entries')}}
                                    </div>
                                    <div>
                                        {{ $roles->links('pagination.pagination') }}
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
</div>
@endsection
@section('scripts')
<script type="text/javascript">
        $("#is_active").click(function () {
            $("#roleTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function () {
            let checked = $('#roleTable .is_open:checked');
            if (!checked.length) {
                alert("{{trans('lang.select_delete_alert')}}");
                return;
            }
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                let arrayUsers = [];
                checked.each(function () {
                    arrayUsers.push($(this).attr('dataId'));
                });
                // join with comma
                let ids = arrayUsers.join(',');
                let url = "{{ url('roles/delete', 'ids') }}";
                url = url.replace('ids', ids);
                $(this).attr('href', url);
            }
        });
</script>
@endsection