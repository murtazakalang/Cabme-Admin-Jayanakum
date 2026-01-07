@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.all_rides') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.all_rides') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/rides.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.all_rides') }}</h3>
                                <span class="counter ml-3">{{ $totalRides }}</span>
                            </div>
                            <form action="{{ route('rides.index') }}" method="get" id="filterForm">
                                <div class="d-flex top-title-right align-self-center">
                                    <div class="select-box pl-3">
                                        <select class="form-control status_selector filteredRecords" name="status_selector">
                                            <option value="">{{ trans('lang.status') }}</option>
                                            <option value="confirmed" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'confirmed' ? 'selected ' : '' }}>{{ trans('lang.confirmed') }}</option>
                                            <option value="new" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'new' ? 'selected ' : '' }}>{{ trans('lang.new') }}</option>
                                            <option value="on ride" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'on ride' ? 'selected ' : '' }}>{{ trans('lang.on_ride') }}</option>
                                            <option value="completed" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'completed' ? 'selected ' : '' }}>{{ trans('lang.completed') }}</option>
                                            <option value="canceled" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'canceled' ? 'selected ' : '' }}>{{ trans('lang.canceled') }}</option>
                                            <option value="rejected" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'rejected' ? 'selected ' : '' }}>{{ trans('lang.driver_rejected') }}</option>
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
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_totals_trip') }}</p>
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
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.new_rides') }}</p>
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
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.active_ride') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/active_rides.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--24">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 completed_ride">{{ $totalCompletedRides }}</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.complete_rides') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/complete_rides.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--7">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 completed_ride">{{ $totalCancelledRides }}</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_cancelled_trip') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/cancel_rides.png') }}"></span>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.all_rides') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.rides_table_text') }}</p>
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
                                            <label>{{ trans('lang.search_by') }}</label>
                                            <div class="form-group  mb-0">
                                                @if ($id != '')
                                                    <form action="{{ route('rides.index', ['id' => $id]) }}" method="get">
                                                    @else
                                                    <form action="{{ route('rides.index') }}" method="get">
                                                @endif
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
                                                    <option value="rideId" {{ request('selected_search') == 'rideId' ? 'selected' : '' }}>
                                                        {{ trans('lang.ride_id') }}
                                                    </option>
                                                    <option value="orderId" {{ request('selected_search') == 'orderId' ? 'selected' : '' }}>
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
                                                    <a class="btn btn-warning btn-flat" href="{{ route('rides.index') }}">{{trans('lang.clear')}}</a>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown text-right">
                                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-download"></i> {{ trans('lang.export_as') }}
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'Requests']) }}">{{ trans('lang.export_excel') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'Requests']) }}">{{ trans('lang.export_pdf') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'Requests']) }}">{{ trans('lang.export_csv') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('ride.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.ride_id') }}</th>
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
                                            @if (count($rides) > 0)
                                                @foreach ($rides as $ride)
                                                    <tr>
                                                        @can('ride.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $ride->id }}" class="is_open" dataid="{{ $ride->id }}"><label class="col-3 control-label" for="is_open_{{ $ride->id }}"></label></td>
                                                        @endcan
                                                        <td><a href="{{ route('ride.show', ['id' => $ride->id]) }}">{{ $ride->id }}</a>
                                                        <td><a href="{{ route('ride.show', ['id' => $ride->id]) }}">{{ $ride->booking_number }}</a>
                                                        </td>
                                                        <td>
                                                            @if ($ride->user_id != null)
                                                                <a href="{{ route('users.show', ['id' => $ride->user_id]) }}">{{ $ride->userPrenom }} {{ $ride->userNom }}
                                                                </a>
                                                            @else
                                                                @php
                                                                    $userInfo = json_decode($ride->user_info, true);
                                                                @endphp
                                                                {{ $userInfo ? $userInfo['name'] : '' }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($ride->driver_id)
                                                                <a href="{{ route('driver.show', ['id' => $ride->driver_id]) }}">{{ $ride->driverPrenom }} {{ $ride->driverNom }}</a>
                                                            @endif
                                                        </td>
                                                       <?php 
                                                            $montant = floatval($ride->montant);
                                                            $total_price = $montant;
                                                            $discount = $ride->discount;
                                                            if ($discount) {
                                                                $total_price -= floatval($discount);
                                                            }
                                                            $tax = json_decode($ride->tax, true);
                                                            $totalTaxAmount = 0;
                                                            if (!empty($tax)) {
                                                                foreach ($tax as $data) {
                                                                    if ($data['type'] == 'Percentage') {
                                                                        $taxValue = (floatval($data['value']) * $total_price) / 100;
                                                                    } else {
                                                                        $taxValue = floatval($data['value']);
                                                                    }
                                                                    $totalTaxAmount += floatval($taxValue);
                                                                }
                                                                $total_price += $totalTaxAmount;
                                                            }
                                                            if ($ride->tip_amount) {
                                                                $total_price += floatval($ride->tip_amount);
                                                            }
                                                        ?>
                                                        <td>
                                                            @if ($currency->symbol_at_right == 'true')
                                                                {{ number_format(floatval($total_price), $currency->decimal_digit) . '' . $currency->symbole }}
                                                            @else
                                                                {{ $currency->symbole . '' . number_format(floatval($total_price), $currency->decimal_digit) }}
                                                            @endif
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if ($ride->ride_type == 'dispatcher')
                                                                {{ trans('lang.dispatcher') }}
                                                            @else
                                                                {{ trans('lang.customer') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                                @if ($ride->statut == 'completed')
                                                                    <span class="badge badge-success">{{ $ride->statut }}<span>
                                                                        @elseif($ride->statut == 'confirmed')
                                                                            <span class="badge badge-secondary">{{ $ride->statut }}<span>
                                                                                @elseif($ride->statut == 'new')
                                                                                    <span class="badge badge-primary">{{ $ride->statut }}<span>
                                                                                        @elseif($ride->statut == 'rejected')
                                                                                            <span class="badge badge-danger">{{ $ride->statut }}<span>
                                                                                                @elseif($ride->statut == 'driver_rejected')
                                                                                                    <span class="badge badge-danger">{{ trans('lang.driver_rejected') }}<span>
                                                                                                        @else
                                                                                                            <span class="badge badge-warning">{{ $ride->statut }}<span>
                                                                @endif
                                                        </td>
                                                        <td class="dt-time"><span class="date">{{ date('d F Y', strtotime($ride->creer)) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($ride->creer)) }}</span>
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('ride.show', ['id' => $ride->id]) }}" class="" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view_details') }}"><i class="mdi mdi-eye"></i></a>
                                                            @can('ride.delete')
                                                                <a id="'+val.id+'" class="delete-btn" name="user-delete" href="{{ route('ride.delete', ['rideid' => $ride->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                            {{trans('lang.showing')}} {{ $rides->firstItem() }} {{trans('lang.to_small')}} {{ $rides->lastItem() }} {{trans('lang.of')}} {{ $rides->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $rides->links('pagination.pagination') }}
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
                    cancelLabel: 'Clear'
                }
            });
            if (initialDateRange) {
                let dates = initialDateRange.split(' - ');
                $('#daterange').data('daterangepicker').setStartDate(dates[0]);
                $('#daterange').data('daterangepicker').setEndDate(dates[1]);
                $('#daterange').val(initialDateRange);
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
                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('ride/delete', 'rideid') }}";
                    url = url.replace('rideid', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert('{{trans("lang.select_delete_alert")}}');
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
