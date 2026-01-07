@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.administration_tools_currency') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.administration_tools') }}</li>
                    <li class="breadcrumb-item active">{{ trans('lang.administration_tools_currency') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/currency.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.administration_tools_currency') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.administration_tools_currency') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.currency_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('currency.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.currency_create') }}</a>
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
                                        <label>{{ trans('lang.search_by') }}
                                            <div class="form-group mb-0">
                                                <form action="{{ route('currency.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="libelle" {{ request('selected_search') == 'libelle' ? 'selected' : '' }}>
                                                            {{ trans('lang.Name') }}
                                                        </option>
                                                        <option value="symbole" {{ request('selected_search') == 'symbole' ? 'selected' : '' }}>
                                                            {{ trans('lang.currency_symbol') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input 
                                                            type="text" 
                                                            class="search form-control" 
                                                            name="search" 
                                                            id="search" 
                                                            value="{{ request('search') }}" 
                                                            placeholder="{{ trans('lang.search') }}..."
                                                        >
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ route('currency.index') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <div class="error_top"></div>
                                    @if ($errors->any())
                                        <div class="alert alert-danger" style="display:none;">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('currency.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.currency_name') }}</th>
                                                <th>{{ trans('lang.currency_symbol') }}</th>
                                                <th>{{ trans('lang.currency_status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($currencies) > 0)
                                                @foreach ($currencies as $currency)
                                                    <tr>
                                                        @can('currency.delete')
                                                            <td class="delete-all"><input type="checkbox" id="is_open_{{ $currency->id }}" class="is_open" dataid="{{ $currency->id }}"><label class="col-3 control-label" for="is_open_{{ $currency->id }}"></label></td>
                                                        @endcan
                                                        <td>{{ $currency->libelle }}</td>
                                                        <td>{{ $currency->symbole }}</td>
                                                        <td>
                                                            @if ($currency->statut == 'yes')
                                                                <label class="switch"><input type="checkbox" checked value="{{ $currency->statut }}" id="{{ $currency->id }}" name="isSwitch" class="switchToggal"><span class="slider round"></span></label>
                                                            @else
                                                                <label class="switch"><input type="checkbox" id="{{ $currency->id }}" name="isSwitch" value="{{ $currency->statut }}" class="switchToggal"><span class="slider round"></span></label><span>
                                                            @endif
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('edit_currency', ['id' => $currency->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                            @can('currency.delete')
                                                            <a href="{{ route('currency.delete', ['id' => $currency->id]) }}" class="delete-btn" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                        {{ $currencies->appends(request()->query())->links() }}
                                    </nav>
                                    {{ $currencies->Links('pagination.pagination') }}
                                  </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $currencies->firstItem() }} {{trans('lang.to_small')}} {{ $currencies->lastItem() }} {{trans('lang.of')}} {{ $currencies->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $currencies->links('pagination.pagination') }}
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
    <script>
        var status = $("input[name='isSwitch']").val();
        $(document).on("click", "input[name='isSwitch']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            var url = "{{ route('currency.switch') }}";
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                method: "POST",
                data: {
                    'ischeck': ischeck,
                    'id': id
                },
                success: function(data) {
                    console.log(data.error);
                    if (data.error) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").after("<div class='alert alert-danger'><ul><li>" + data.error + "</li></ul></div>");
                    }
                    window.location.reload();
                },
                error: function(response) {
                },
            });
            $("#example24 tr").each(function() {
                $(this).find(".switch input").not('#' + id).prop('checked', false);
            });
        });
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
                    var url = "{{ url('administration_tools/currency/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
    </script>
@endsection
