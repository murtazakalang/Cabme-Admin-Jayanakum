@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.notification') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ trans('lang.notification') }}
                    </li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.notification') }}</h3>
                                <span class="counter ml-3">{{ $totalLength }}</span>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.notification') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.notification_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('notifications.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_notification') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="userlist-topsearch d-flex mb-3">
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
                                    <div id="users-table_filter" class="ml-auto">
                                        <div class="form-group">
                                            <form action="{{ route('notifications.index') }}" method="get">
                                                <label>{{ trans('lang.search_by') }}</label>
                                                <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                    <option value="title" {{ request('selected_search') == 'title' ? 'selected' : '' }}>
                                                        {{ trans('lang.title') }}
                                                    </option>
                                                    <option value="message" {{ request('selected_search') == 'message' ? 'selected' : '' }}>
                                                        {{ trans('lang.message') }}
                                                    </option>
                                                </select>
                                                <div class="search-box position-relative">
                                                    <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                    <button type="submit" class="btn-flat position-absolute"><i class="fa fa-search"></i></button>
                                                    <a class="btn btn-warning btn-flat" href="{{ url('notification') }}">{{ trans('lang.clear') }}</a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('notifications.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> All</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.title') }}</th>
                                                <th>{{ trans('lang.message') }}</th>
                                                <th>{{ trans('lang.created') }}</th>
                                                @can('notifications.delete')
                                                <th>{{ trans('lang.actions') }}</th>
                                                @endcan
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($notifications) > 0)
                                                @foreach ($notifications as $notification)
                                                    <tr>
                                                        @can('notifications.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $notification->id }}" class="is_open" dataid="{{ $notification->id }}"><label class="col-3 control-label" for="is_open_{{ $notification->id }}"></label>
                                                        </td>
                                                        @endcan
                                                        <td>{{ $notification->title }}</td>
                                                        <td class="address-td">{{ $notification->message }}</td>
                                                        <td class="dt-time"><span class="date">{{ date(
                                                            'd F
                                                                                                                                                                Y',
                                                            strtotime($notification->created_at),
                                                        ) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($notification->updated_at)) }}</span>
                                                        </td>   
                                                        @can('notifications.delete')
                                                        <td class="action-btn">
                                                            <a id="'+val.id+'" class="delete-btn" name="user-delete" href="{{ route('notifications.delete', ['id' => $notification->id]) }}"><i class="mdi mdi-delete" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"></i></a>
                                                        </td>
                                                        @endcan
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" align="center">{{ trans('lang.no_result') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $notifications->firstItem() }} {{trans('lang.to_small')}} {{ $notifications->lastItem() }} {{trans('lang.of')}} {{ $notifications->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $notifications->links('pagination.pagination') }}
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
        $(document).ready(function() {
            $(".shadow-sm").hide();
        })
        $("#is_active").click(function() {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#example24 .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('notification/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
    </script>
@endsection
