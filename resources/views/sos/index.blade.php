@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.sos') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ trans('lang.sos') }}
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
                                <span class="icon mr-3"><img src="{{ asset('images/SOS.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.sos') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.sos') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.sos_table_text') }}</p>
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
                                    <div class="userlist-topsearch d-flex mb-3">
                                        <div id="users-table_filter" class="ml-auto">
                                            <label>{{ trans('lang.search_by') }}
                                                <div class="form-group">
                                                    <form action="{{ route('sos.index') }}" method="get">
                                                            <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                            <option value="status" {{ request('selected_search') == 'status' ? 'selected' : '' }}>
                                                                {{ trans('lang.status') }}
                                                            </option>
                                                            <option value="user" {{ request('selected_search') == 'user' ? 'selected' : '' }}>
                                                                {{ trans('lang.userName') }}
                                                            </option>
                                                            <option value="driver" {{ request('selected_search') == 'driver' ? 'selected' : '' }}>
                                                                {{ trans('lang.driver_name') }}
                                                            </option>
                                                            <option value="ride_id" {{ request('selected_search') == 'ride_id' ? 'selected' : '' }}>
                                                                {{ trans('lang.ride_id') }}
                                                            </option>
                                                        </select>
                                                        <div class="search-box position-relative">
                                                            <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                            <button type="submit" class="btn-flat position-absolute">
                                                                <i class="fa fa-search"></i>
                                                            </button>
                                                            <a class="btn btn-warning btn-flat" href="{{url('sos')}}">{{trans('lang.clear')}}</a>
                                                        </div>
                                                    </form>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('sos.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.ride_id') }}</th>
                                                <th>{{ trans('lang.userName') }}</th>
                                                <th>{{ trans('lang.driver_name') }}</th>
                                                <th>{{ trans('lang.sos_status') }}</th>
                                                <th>{{ trans('lang.created') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($sos) > 0)
                                                @foreach ($sos as $so)
                                                    <tr>
                                                        @can('sos.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $so->id }}" class="is_open" dataid="{{ $so->id }}"><label class="col-3 control-label" for="is_open_{{ $so->id }}"></label></td>
                                                        @endcan
                                                        <td>
                                                            {{-- <a href="{{route('ride.show', ['id' => $so->ride_id])}}">{{ $so->ride_id}}</a> --}}
                                                            <a href="{{ route('sos.show', ['id' => $so->id]) }}">{{ $so->ride_id }}</a>
                                                        </td>
                                                        <td><a href="{{ route('users.show', $so->id_user_app) }}">{{ $so->userPreNom }} {{ $so->userNom }}<a></td>
                                                        <td><a href="{{ route('driver.show', $so->id_conducteur) }}">{{ $so->driverPreNom }} {{ $so->driverNom }}</a></td>
                                                        <td>
                                                            @if ($so->status == 'initiated')
                                                                <span class="badge badge-warning">{{ $so->status }}</span>
                                                            @elseif($so->status == 'processing')
                                                                <span class="badge badge-primary">{{ $so->status }}</span>
                                                            @elseif($so->status == 'user feel not safe')
                                                                <span class="badge badge-danger">User Feel do not Safe</span>
                                                            @elseif($so->status == 'driver feel not safe')
                                                                <span class="badge badge-danger">Driver Feel do not Safe</span>
                                                            @else
                                                                <span class="badge badge-success">{{ $so->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="date">{{ date('d F Y', strtotime($so->creer)) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($so->creer)) }}</span>
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('sos.show', ['id' => $so->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view_details') }}"><i class="mdi mdi-eye"></i></a>
                                                            @can('sos.delete')
                                                            <a id="'+val.id+'" class="delete-btn" name="user-delete" href="{{ route('sos.delete', ['id' => $so->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="11" align="center">{{ trans('lang.no_result') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="table-page d-flex align-items-center justify-content-between mt-3"> 
                                        <nav aria-label="Page navigation example" class="custom-pagination">
                                            {{ $sos->appends(request()->query())->links() }}
                                        </nav>
                                        {{ $sos->links('pagination.pagination') }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $sos->firstItem() }} {{trans('lang.to_small')}} {{ $sos->lastItem() }} {{trans('lang.of')}} {{ $sos->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $sos->links('pagination.pagination') }}
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
        })
        $("#is_active").click(function() {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#example24 .is_open:checked').length) {
                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('sos/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert('{{ trans('lang.select_delete_alert') }}');
            }
        });
    </script>
@endsection
