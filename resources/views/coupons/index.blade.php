@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.coupon_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ trans('lang.coupon_table') }}
                    </li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.coupon_table') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.coupon_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.coupon_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('coupons.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.coupon_create') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                                    {{trans('lang.processing')}}
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
                                                <form action="{{ route('coupons.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="code" {{ request('selected_search') == 'code' ? 'selected' : '' }}>
                                                            {{ trans('lang.coupon_code') }}
                                                        </option>
                                                        <option value="discount" {{ request('selected_search') == 'discount' ? 'selected' : '' }}>
                                                            {{ trans('lang.coupon_discount') }}
                                                        </option>
                                                        <option value="coupon_type" {{ request('selected_search') == 'coupon_type' ? 'selected' : '' }}>
                                                            {{ trans('lang.coupon_type') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ route('coupons.index') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                <th>{{ trans('lang.coupon_code') }}</th>
                                                <th>{{ trans('lang.coupon_discount') }}</th>
                                                <th>{{ trans('lang.coupon_type') }}</th>
                                                <th>{{ trans('lang.coupon_description') }}</th>
                                                <th>{{ trans('lang.coupon_expires_at') }}</th>
                                                <th>{{ trans('lang.coupon_enabled') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($discounts) > 0)
                                                @foreach ($discounts as $discount)
                                                    <tr>
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $discount->id }}" class="is_open" dataid="{{ $discount->id }}"><label class="col-3 control-label" for="is_open_{{ $discount->id }}"></label></td>
                                                        <td>{{ $discount->code }}</td>
                                                        <td>
                                                            @if($discount->type == "Fixed")
                                                                @if($currency->symbol_at_right == "true")
                                                                    {{ $discount->discount }}{{ $currency->symbole }}
                                                                @else
                                                                    {{ $currency->symbole }}{{ $discount->discount }}   
                                                                @endif
                                                            @else
                                                                {{ $discount->discount }}%  
                                                            @endif
                                                        </td>
                                                        <td>{{ $discount->coupon_type }}</td>
                                                        <td>{{ $discount->discription }}</td>
                                                        <td>
                                                            <span class="date">{{ date('d F Y', strtotime($discount->expire_at)) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($discount->expire_at)) }}</span>
                                                        </td>
                                                        <td>
                                                            @if ($discount->statut == 'yes')
                                                                <label class="switch"><input type="checkbox" checked id="{{ $discount->id }}" name="isActive"><span class="slider round"></span></label>
                                                            @else
                                                                <label class="switch"><input type="checkbox" id="{{ $discount->id }}" name="isActive"><span class="slider round"></span></label>
                                                            @endif
                                                        </td>
                                                        <td class="action-btn">                                                          
                                                            <a href="{{ route('coupons.edit', ['id' => $discount->id]) }}" class="do_not_edit" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a><a id="'+val.id+'" class="delete-btn" name="coupon-delete" href="{{ route('coupons.delete', ['id' => $discount->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $discounts->firstItem() }} {{trans('lang.to_small')}} {{ $discounts->lastItem() }} {{trans('lang.of')}} {{ $discounts->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $discounts->links('pagination.pagination') }}
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
                    var url = "{{ url('coupons/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert('{{ trans('lang.select_delete_alert') }}');
            }
        });
        /* toggal publish action code start*/
        $(document).on("click", "input[name='isActive']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'coupon/switch',
                method: "POST",
                data: {
                    'ischeck': ischeck,
                    'id': id
                },
                success: function(data) {
                },
            });
        });
        /*toggal publish action code end*/
    </script>
@endsection
