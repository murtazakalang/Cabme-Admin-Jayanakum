@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.rental_orders') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.rental_orders') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/parcel_order.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.rental_orders') }}</h3>
                                <span class="counter ml-3">{{ $totalLength }}</span>
                            </div>
                            <form action="{{ route('rental-orders.index') }}" method="get" id="filterForm">
                                <div class="d-flex top-title-right align-self-center">
                                    <div class="select-box pl-3">
                                        <select class="form-control status_selector filteredRecords" name="status_selector">
                                            <option value="">{{ trans('lang.status') }}</option>
                                            <option value="confirmed" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'confirmed' ? 'selected ' : '' }}>{{ trans('lang.confirmed') }}</option>
                                            <option value="new" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'new' ? 'selected ' : '' }}>{{ trans('lang.new') }}</option>
                                            <option value="on ride" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'on ride' ? 'selected ' : '' }}>{{ trans('lang.on_ride') }}</option>
                                            <option value="completed" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'completed' ? 'selected ' : '' }}>{{ trans('lang.completed') }}</option>
                                            <option value="canceled" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'canceled' ? 'selected ' : '' }}>{{ trans('lang.canceled') }}</option>
                                            <option value="rejected" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'rejected' ? 'selected ' : '' }}>{{ trans('lang.rejected') }}</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3">
                                        <input type="text" placeholder="dd-mm-yyyy" class="form-control filteredRecords" id="daterange" name="daterange" value="{{ request('daterange') }}" readonly />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--15">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 total_ride">{{ $totalRides }}</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.total_orders') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_rides.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--5">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 placed_ride">{{ $totalNewRides }}</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.new_orders') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/placed_rides.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--1">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 active_ride">{{ $totalOnRides }}</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.active_orders') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/active_rides.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--24">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 completed_ride">{{ $completed_rental_rides }}</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.complete_orders') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/complete_rides.png') }}"></span>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.rental_orders') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.parcel_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <h4 class="card-title"></h4>
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
                                        <div class="userlist-topsearch d-flex mb-0">
                                            <div id="users-table_filter" class="ml-auto">
                                                <label>{{ trans('lang.search_by') }}
                                                    <div class="form-group  mb-0">
                                                        <form action="{{ route('rental-orders.index') }}" method="get">
                                                            <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                                <option value="userName" {{ request('selected_search') == 'userName' ? 'selected' : '' }}>
                                                                    {{ trans('lang.userName') }}
                                                                </option>
                                                                <option value="driverName" {{ request('selected_search') == 'driverName' ? 'selected' : '' }}>
                                                                    {{ trans('lang.driver_name') }}
                                                                </option>
                                                                <option value="type" {{ request('selected_search') == 'type' ? 'selected' : '' }}>
                                                                    {{ trans('lang.booked_by') }}
                                                                </option>
                                                                <option value="orderId" {{ request('selected_search') == 'orderId' ? 'selected' : '' }}>
                                                                    {{ trans('lang.order_id') }}
                                                                </option>
                                                                <option value="orderNumber" {{ request('selected_search') == 'orderNumber' ? 'selected' : '' }}>
                                                                    {{ trans('lang.order_number') }}
                                                                </option>
                                                            </select>
                                                            <div class="search-box position-relative">
                                                                <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }} " style="{{ request('search') ? '' : (request('search') ? 'display:none' : '') }}" placeholder="{{ trans('lang.search') }}...">
                                                                <select id="ride_type" class="search form-control" name="ride_type" style="{{ request('selected_search') == 'type' ? '' : 'display:none' }}">
                                                                    <option value="customer" {{ request('ride_type') == 'customer' ? 'selected' : '' }}> 
                                                                        {{ trans('lang.customer') }}
                                                                    </option>
                                                                    <option value="dispatcher" {{ request('ride_type') == 'dispatcher' ? 'selected' : '' }}>
                                                                        {{ trans('lang.dispatcher') }}
                                                                    </option>
                                                                </select>
                                                                <button type="submit" class="btn-flat position-absolute"><i class="fa fa-search"></i></button>
                                                            </div>
                                                            <button onclick="searchtext();" class="btn btn-warning btn-flat">{{ trans('lang.search') }}</button>
                                                            <a class="btn btn-warning btn-flat" href="{{ route('rental-orders.index') }}">{{trans('lang.clear')}}</a>
                                                        </form>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="dropdown text-right">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-download"></i> {{ trans('lang.export_as') }}
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'ParcelOrder']) }}">{{ trans('lang.export_excel') }}</a></li>
                                                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'ParcelOrder']) }}">{{ trans('lang.export_pdf') }}</a></li>
                                                <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'ParcelOrder']) }}">{{ trans('lang.export_csv') }}</a></li>
                                            </ul>
                                        </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('rental-orders.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.order_id') }}</th>
                                                <th>{{ trans('lang.order_number') }}</th>
                                                <th>{{ trans('lang.userName') }}</th>
                                                <th>{{ trans('lang.driver_name') }}</th>
                                                <th>{{ trans('lang.cost_amount') }}</th>
                                                <th>{{ trans('lang.booked_by') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.created') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list12">
                                            @if (count($bookings) > 0)
                                                @foreach ($bookings as $booking)
                                                    <tr>
                                                         @can('rental-orders.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $booking->id }}" class="is_open" dataid="{{ $booking->id }}"><label class="col-3 control-label" for="is_open_{{ $booking->id }}"></label></td>
                                                          @endcan
                                                        <td><a href="{{ route('rental-orders.show', ['id' => $booking->id]) }}">{{ $booking->id }}</a></td>
                                                        <td><a href="{{ route('rental-orders.show', ['id' => $booking->id]) }}">{{ $booking->booking_number }}</a></td>  
                                                        <td>
                                                            <a href="{{ route('users.show', ['id' => $booking->id_user_app]) }}">
                                                                @if($booking->user)
                                                                    {{ $booking->user->prenom . ' ' . $booking->user->nom }}
                                                                @else
                                                                    {{ $booking->userNom ?? 'N/A' }}
                                                                @endif
                                                            </a>
                                                        </td>
                                                        <td>
                                                            @if (isset($booking->id_conducteur))
                                                                <a href="{{ route('driver.show', ['id' => $booking->id_conducteur]) }}">{{ $booking->driver ? $booking->driver->prenom.' '.$booking->driver->nom : 'N/A'}}</a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($currency->symbol_at_right == 'true')
                                                                {{ number_format(floatval($booking->total_price), $currency->decimal_digit) . '' . $currency->symbole }}
                                                            @else
                                                                {{ $currency->symbole . '' . number_format(floatval($booking->total_price), $currency->decimal_digit) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($booking->ride_type == 'dispatcher')
                                                                {{ trans('lang.dispatcher') }}
                                                            @else
                                                                {{ trans('lang.customer') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($booking->status == 'completed')
                                                                <span class="badge badge-success">{{ $booking->status }}<span>
                                                                    @elseif($booking->status == 'confirmed')
                                                                        <span class="badge badge-success">{{ $booking->status }}<span>
                                                                            @elseif($booking->status == 'new')
                                                                                <span class="badge badge-primary">{{ $booking->status }}<span>
                                                                                    @elseif($booking->status == 'rejected')
                                                                                        <span class="badge badge-danger">{{ $booking->status }}<span>
                                                                                            @elseif($booking->status == 'driver_rejected')
                                                                                                <span class="badge badge-danger">{{ trans('lang.driver_rejected') }}<span>
                                                                                                    @else
                                                                                                        <span class="badge badge-warning">{{ $booking->status }}<span>
                                                            @endif
                                                        </td>
                                                        <td class="dt-time"><span class="date">{{ date('d F Y', strtotime($booking->created_at)) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($booking->created_at)) }}</span>
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('rental-orders.show', ['id' => $booking->id]) }}" class="" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view_details') }}"><i class="mdi mdi-eye"></i></a>
                                                            @can('rental-orders.delete')
                                                            <a id="'+val.id+'" class="delete-btn" name="user-delete" href="{{ route('rental-orders.delete', ['id' => $booking->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                            {{trans('lang.showing')}} {{ $bookings->firstItem() }} {{trans('lang.to_small')}} {{ $bookings->lastItem() }} {{trans('lang.of')}} {{ $bookings->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $bookings->links('pagination.pagination') }}
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
        $('.status_selector').select2({
            placeholder: '{{ trans('lang.status') }}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('select').on("select2:unselecting", function(e) {
            var self = $(this);
            setTimeout(function() {
                self.select2('close');
            }, 0);
        });
        function setDate() {
            let initialDateRange = $('#daterange').val(); // Get the initial value from input
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY',
                    cancelLabel: "{{trans('lang.clear')}}"
                }
            });
            if (initialDateRange) {
                let dates = initialDateRange.split(' - ');
                $('#daterange').data('daterangepicker').setStartDate(dates[0]);
                $('#daterange').data('daterangepicker').setEndDate(dates[1]);
                $('#daterange').val(initialDateRange);
                $('#daterange').attr('readonly', true);
            }
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
                $('.filteredRecords').trigger('change');
            });
            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('.filteredRecords').trigger('change');
            });
        }
        setDate();
        $('.filteredRecords').change(async function() {
            $('#filterForm').submit();
        })
        $("#deleteAll").click(function() {
            if ($('#example24 .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('rental-orders/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
        
        $(document).ready(function() {
            if ($('#selected_search').val() == "type") {
                jQuery('#search').hide();
                jQuery('#ride_type').show();
            }
        })
        $(document.body).on('change', '#selected_search', function() {
            if ($('#selected_search').val() == "type") {
                jQuery('#search').hide();
                jQuery('#ride_type').show();
            }else{
                jQuery('#search').show();
                jQuery('#ride_type').hide();
            }
        });
    </script>
@endsection
