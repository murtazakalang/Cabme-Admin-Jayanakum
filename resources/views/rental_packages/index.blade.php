@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">{{ trans('lang.rental_packages') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.rental_packages') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.rental_packages') }}</h3>
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
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.rental_packages') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.rental_packages_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('rental-packages.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_rental_package') }}</a>
                                    </div>
                                </div>
                            </div>
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
                                                <form action="{{ route('rental-packages.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="title" {{ request('selected_search') == 'title' ? 'selected="selected"' : ''}}>{{ trans('lang.package_name') }}</option>
                                                        <option value="farePrice" {{ request('selected_search') == 'farePrice' ? 'selected="selected"' : ''}}>{{ trans('lang.package_basefare_price') }}</option>
                                                        <option value="vehicleType" {{ request('selected_search') == 'vehicleType' ? 'selected="selected"' : ''}}>{{ trans('lang.vehicle_type') }}</option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ route('rental-packages.index') }}">{{trans('lang.clear')}}</a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="rentalPackagesTable" class="display nowrap table table-hover table-striped table-bordered table table-striped dataTable no-footer dtr-inline collapsed" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                 @can('rental-packages.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i>
                                                            {{ trans('lang.all') }}</a></label>
                                                  @endcan
                                                </th>
                                                <th>{{ trans('lang.package_name') }}</th>
                                                <th>{{ trans('lang.package_basefare_price') }}</th>
                                                <th>{{ trans('lang.vehicle_type') }}</th>
                                                <th>{{ trans('lang.published') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($packages) > 0)
                                                @foreach ($packages as $package)
                                                    <tr>
                                                        @can('rental-packages.delete')
                                                        <td class="delete-all">
                                                            <input type="checkbox" id="is_open_{{ $package->id }}" class="is_open" dataid="{{ $package->id }}"><label class="col-3 control-label" for="is_open_{{ $package->id }}"></label>
                                                        </td>
                                                         @endcan
                                                        <td>
                                                            <a href="{{ route('rental-packages.edit', ['id' => $package->id]) }}">{{ $package->title}}</a>
                                                        </td>
                                                        <td>
                                                            @if ($currency->symbol_at_right == 'true')
                                                                {{ number_format($package->baseFare, $currency->decimal_digit) . '' . $currency->symbole }}
                                                            @else
                                                                {{ $currency->symbole . '' . number_format($package->baseFare, $currency->decimal_digit) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $package->vehicleType ? $package->vehicleType->libelle : '' }}
                                                        </td>
                                                        <td>
                                                            @if ($package->published == 'true')
                                                                <label class="switch"><input type="checkbox" id="{{ $package->id }}" name="isActive" checked><span class="slider round"></span></label>
                                                            @else
                                                                <label class="switch"><input type="checkbox" id="{{ $package->id }}" name="isActive"><span class="slider round"></span></label>
                                                            @endif
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('rental-packages.edit', ['id' => $package->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                             @can('rental-packages.delete')
                                                            <a href="{{ route('rental-packages.delete', ['id' => $package->id]) }}" class="delete-btn" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $packages->firstItem() }} {{trans('lang.to_small')}} {{ $packages->lastItem() }} {{trans('lang.of')}} {{ $packages->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $packages->links('pagination.pagination') }}
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
        $("#is_active").click(function() {
            $("#rentalPackagesTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#rentalPackagesTable .is_open:checked').length) {
                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                    var ids = [];
                    $('#rentalPackagesTable .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        ids.push(dataId);
                    });
                    ids = JSON.stringify(ids);
                    var url = "{{ url('rental-packages/delete', 'id') }}";
                    url = url.replace('id', ids);
                    $(this).attr('href', url);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
        $(document).on("click", "input[name='isActive']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'rental-packages-switch',
                method: "POST",
                data: {
                    'ischeck': ischeck,
                    'id': id
                },
                success: function(response) {
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message);
                    location.reload();
                }
            });
        });
    </script>
@endsection
