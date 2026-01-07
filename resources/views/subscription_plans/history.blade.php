@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.subscription_history') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.subscription_history_table') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.subscription_history') }}</h3>
                                <span class="counter ml-3 zone_count">{{ $totalLength }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                                    {{ trans('lang.processing') }}
                                </div>
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
                                        <label>{{ trans('lang.search_by') }}
                                            <div class="form-group mb-0">
                                                <form action="{{ route('subscription-history.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="name" {{ request('selected_search') == 'name' ? 'selected' : '' }}>
                                                            {{ trans('lang.plan_name') }}
                                                        </option>
                                                        <option value="driver" {{ request('selected_search') == 'driver' ? 'selected' : '' }}>
                                                            {{ trans('lang.driver/owner') }}
                                                        </option>
                                                        <option value="paymentMethod" {{ request('selected_search') == 'paymentMethod' ? 'selected' : '' }}>
                                                            {{ trans('lang.payment_method') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" class="search form-control" name="search" id="search"
                                                            value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ route('subscription-history.index') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="subscriptionHistoryTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label>
                                                </th>
                                                <th>{{ trans('lang.driver/owner') }}</th>
                                                <th>{{ trans('lang.plan_name') }}</th>
                                                <th>{{ trans('lang.plan_type') }}</th>
                                                <th>{{ trans('lang.payment_method') }}</th>
                                                <th>{{ trans('lang.plan_expires_at') }}</th>
                                                <th>{{ trans('lang.purchase_date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($history) > 0)
                                            @foreach ($history as $value)
                                                <tr>
                                                    <td class="delete-all">
                                                        <input type="checkbox" id="is_open_{{ $value->id }}" class="is_open" dataid="{{ $value->id }}"><label class="col-3 control-label" for="is_open_{{ $value->id }}"></label>
                                                    </td>
                                                    <td>
                                                    <a href="{{ $value->isOwner == 'true' ? route('owners.show', ['id' => $value->user_id]) : route('driver.show', ['id' => $value->user_id]) }}"">{{ $value->prenom . ' ' . $value->nom }}</a></td>
                                                    <td>{{ $value->subscription_plan['name'] }}</td>
                                                    <td>
                                                        @if ($value->subscription_plan['type'] == 'free')
                                                            <span class="badge badge-success">{{ $value->subscription_plan['type'] }}</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ $value->subscription_plan['type'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($value->plan_type  == "free")     
                                                            {{ trans('lang.free') }}
                                                        @else
                                                            {{ $value->payment_name }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($value->expiry_date == null)
                                                            {{ trans('lang.unlimited') }}
                                                        @else
                                                            <span class="date">{{ date('d F Y', strtotime($value->expiry_date)) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($value->expiry_date)) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="date">{{ date('d F Y', strtotime($value->created_at)) }}</span>
                                                        <span class="time">{{ date('h:i A', strtotime($value->created_at)) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                             @else
                                                <tr>
                                                    <td colspan="6" align="center">{{ trans('lang.no_result') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $history->firstItem() }} {{trans('lang.to_small')}} {{ $history->lastItem() }} {{trans('lang.of')}} {{ $history->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $history->links('pagination.pagination') }}
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
    <script>
        $("#is_active").click(function() {
            $("#subscriptionHistoryTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#subscriptionHistoryTable .is_open:checked').length) {
                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                    var arrayUsers = [];
                    $('#subscriptionHistoryTable .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('subscription-history/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
    </script>
@endsection
