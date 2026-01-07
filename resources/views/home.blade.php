@extends('layouts.app')

@section('content')
    <div id="main-wrapper" class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.welcome_admin_note') }}{{ Auth::user()->name }}!</h3>
                <p>{{ trans('lang.welcome_admin_note2') }}</p>
            </div>
        </div>
        <!-- start container-->

        <div class="container-fluid">

            <!-- start row -->

            <div class="card mb-3 mt-4 business-analytics shadow-none">

                <div class="card-body p-0">

                    <div class="row trip-info total top">



                        <!-- column -->

                        <div class="col-lg-12">

                            <form method="GET" action="{{ route('home') }}" id="dashboardFilterForm">
                            <div class="sis-card-head-select-box d-flex align-items-center gap-2 mb-4">
                                <div class="head-select-box">
                                    <label class="mb-0">{{ trans('lang.filter_by') }}</label>
                                    <select id="viewFilter" name="view" class="form-control">
                                        <option value="year" {{ request('view') == 'year' ? 'selected' : '' }}>{{trans('lang.view_full_year')}}</option>
                                        <option value="month" {{ request('view') == 'month' ? 'selected' : '' }}>{{trans('lang.view_by_month')}}</option>
                                        <option value="custom" {{ request('view') == 'custom' ? 'selected' : '' }}>{{trans('lang.custom_date_range')}}</option>
                                    </select>
                                </div>

                                <div id="monthYearFilters" class="head-select-box" style="display:inline-block;">
                                    <select id="monthFilter" name="month" class="form-control" style="display: none;">
                                        @for($i=1;$i<=12;$i++)
                                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                        @endfor
                                    </select>
                                    <select id="yearFilter" name="year" class="form-control">
                                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div id="customDateFilters" class="head-select-box" style="display: none;">
                                    <input class="form-control" type="date" name="start_date" id="startDate" value="{{ request('start_date') }}" />
                                    <input class="form-control" type="date" name="end_date" id="endDate" value="{{ request('end_date') }}" />
                                </div>
                                <button type="submit" class="btn btn-primary">
                                Apply Filter
                            </button>
                             <a href="{{ route('home') }}" class="btn btn-secondary">
                                                Clear Filter
                                            </a>
                            </div>
                        </form>



                        </div>


                        <!-- New column -->

                        <div class="col-lg-3">

                            <div class="card card-box-with-icon ">

                                <div class="card-body icon-orange">

                                    <div class="d-flex">

                                        <div class="card-left">

                                            <h4 class="card-left-title text-dark font-semibold">
                                                {{ trans('lang.dashboard_total_earnings') }}
                                            </h4>

                                            <h3 class="m-b-0 text-dark font-bold mb-3 total_earning" id="total_earnings">
                                                @if ($currency->symbol_at_right == 'true')
                                                    {{ number_format($total_earnings, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                                @else
                                                    {{ $currency->symbole . ' ' . number_format($total_earnings, $currency->decimal_digit) }}
                                                @endif
                                            </h3>

                                            <input type="hidden" id="total_earning" value="{{ $total_earnings }}">
                                             @if ($earnings_percentage_change > 0)
                                                <h6 class="green up-down-list font-semibold"><i class="fa fa-arrow-up"></i>
                                                    {{ $earnings_percentage_change }}% vs last month</h6>
                                            @else
                                                <h6 class="red up-down-list font-semibold"><i class="fa fa-arrow-down"></i>
                                                    {{ $earnings_percentage_change }}% vs last month</h6>
                                            @endif


                                        </div>

                                        <div class="card-right ml-auto">

                                            <img src="{{ asset('images/total-earning.png') }}">

                                        </div>
                                    </div>

                                    <div class="total-earnings pt-2 text-dark font-semibold">
                                        <span>{{ trans('lang.dashboard_ride_total_earnings') }} :
                                            @if ($currency->symbol_at_right == 'true')
                                                {{ number_format($total_ride_earnings, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                            @else
                                                {{ $currency->symbole . ' ' . number_format($total_ride_earnings, $currency->decimal_digit) }}
                                            @endif</span>
                                        |
                                        <span>{{ trans('lang.dashboard_parcel_total_earnings') }} :
                                            @if ($currency->symbol_at_right == 'true')
                                                {{ number_format($total_parcel_earnings, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                            @else
                                                {{ $currency->symbole . ' ' . number_format($total_parcel_earnings, $currency->decimal_digit) }}
                                            @endif</span>
                                        |
                                        <span>{{ trans('lang.dashboard_rental_total_earnings') }} :
                                            @if ($currency->symbol_at_right == 'true')
                                                {{ number_format($total_rental_earnings, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                            @else
                                                {{ $currency->symbole . ' ' . number_format($total_rental_earnings, $currency->decimal_digit) }}
                                            @endif</span>
                                    </div>

                                </div>

                            </div>

                        </div>
                        <!-- New column -->

                        <div class="col-lg-3">

                            <div class="card card-box-with-icon ">

                                <div class="card-body icon-orange">

                                    <div class="d-flex">

                                        <div class="card-left">

                                            <h4 class="card-left-title text-dark font-semibold">
                                                {{ trans('lang.dashboard_total_admin_commission') }}
                                            </h4>

                                            <h3 class="m-b-0 text-dark font-bold mb-3 admin_commission" id="admin_commission_text">
                                                @if ($currency->symbol_at_right == 'true')
                                                    {{ number_format($total_admin_commission, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                                @else
                                                    {{ $currency->symbole . ' ' . number_format($total_admin_commission, $currency->decimal_digit) }}
                                                @endif
                                            </h3>

                                            <input type="hidden" id="admin_commission" value="{{ $total_admin_commission }}">

                                            @if ($percentage_change_admin_commission > 0)
                                                <h6 class="green up-down-list font-semibold"><i class="fa fa-arrow-up"></i>
                                                    {{ $percentage_change_admin_commission }}% vs last month</h6>
                                            @else
                                                <h6 class="red up-down-list font-semibold"><i class="fa fa-arrow-down"></i>
                                                    {{ $percentage_change_admin_commission }}% vs last month</h6>
                                            @endif
                                        </div>

                                        <div class="card-right ml-auto">

                                            <img src="{{ asset('images/admin-commission.png') }}">

                                        </div>
                                    </div>

                                    <div class="total-earnings pt-2 text-dark font-semibold">
                                        <span>{{ trans('lang.dashboard_ride_total_earnings') }} :
                                            @if ($currency->symbol_at_right == 'true')
                                                {{ number_format($total_ride_commission, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                            @else
                                                {{ $currency->symbole . ' ' . number_format($total_ride_commission, $currency->decimal_digit) }}
                                            @endif
                                        </span>
                                        |
                                        <span>{{ trans('lang.dashboard_parcel_total_earnings') }} :
                                            @if ($currency->symbol_at_right == 'true')
                                                {{ number_format($total_parcel_admin_commission, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                            @else
                                                {{ $currency->symbole . ' ' . number_format($total_parcel_admin_commission, $currency->decimal_digit) }}
                                            @endif
                                        </span>
                                        |
                                        <span>{{ trans('lang.dashboard_rental_total_earnings') }} :
                                            @if ($currency->symbol_at_right == 'true')
                                                {{ number_format($total_rental_admin_commission, $currency->decimal_digit) . ' ' . $currency->symbole }}
                                            @else
                                                {{ $currency->symbole . ' ' . number_format($total_rental_admin_commission, $currency->decimal_digit) }}
                                            @endif
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- column -->

                        <div class="col-lg-3">

                            <div class="card card-box-with-icon ">

                                <div class="card-body icon-orange">

                                    <div class="d-flex">

                                        <div class="card-left">

                                            <h4 class="card-left-title text-dark font-semibold">
                                                {{ trans('lang.dashboard_active_users') }}
                                            </h4>

                                            <h3 class="m-b-0 text-dark font-bold mb-3 admin_commission" id="active_users">
                                                {{ ($total_users ?? 0) + ($total_drivers ?? 0) + ($total_owners ?? 0) + ($total_fleet_drivers ?? 0) }}
                                            </h3>

                                            @if ($total_users_change > 0)
                                                <h6 class="green up-down-list font-semibold"><i class="fa fa-arrow-up"></i> {{ $total_users_change }}% vs
                                                    last month</h6>
                                            @else
                                                <h6 class="red up-down-list font-semibold"><i class="fa fa-arrow-down"></i> {{ $total_users_change }}% vs last
                                                    month</h6>
                                            @endif



                                        </div>

                                        <div class="card-right ml-auto">

                                            <img src="{{ asset('images/active-users.png') }}">

                                        </div>
                                    </div>

                                    <div class="total-earnings pt-2 text-dark font-semibold">
                                        <span>{{ trans('lang.dashboard_total_users') }} :
                                            {{ $total_users ?? 0}}
                                        </span>
                                        |
                                        <span>{{ trans('lang.dashboard_total_drivers') }} :
                                            {{ $total_drivers ?? 0 }}
                                        </span>
                                        |
                                        <span>{{ trans('lang.owner_plural') }} :
                                            {{ $total_owners ?? 0 }}
                                        </span>
                                        |
                                        <span>{{ trans('lang.fleet_drivers') }} :
                                            {{ $total_fleet_drivers ?? 0 }}
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-lg-3">

                            <div class="card card-box-with-icon ">

                                <div class="card-body icon-orange">

                                    <div class="d-flex">

                                        <div class="card-left">

                                            <h4 class="card-left-title text-dark font-semibold">
                                                {{ trans('lang.dashboard_totals_trip') }}
                                            </h4>

                                            <h3 class="m-b-0 text-dark font-bold mb-3 admin_commission" id="total_rides">
                                                {{ ($total_rides ?? 0) + ($total_parcel ?? 0) + ($total_rental ?? 0)  }}
                                            </h3>

                                            @if ($rides_parcels_rentals_change > 0)
                                                <h6 class="green up-down-list font-semibold"><i class="fa fa-arrow-up"></i>
                                                    {{ $rides_parcels_rentals_change }}% vs last month</h6>
                                            @else
                                                <h6 class="red up-down-list font-semibold"><i class="fa fa-arrow-down"></i>
                                                    {{ $rides_parcels_rentals_change }}% vs last month</h6>
                                            @endif


                                        </div>

                                        <div class="card-right ml-auto">

                                            <img src="{{ asset('images/total-trips.png') }}">

                                        </div>
                                    </div>

                                    <div class="total-earnings pt-2 text-dark font-semibold">
                                        <span>{{ trans('lang.rides') }} :
                                            {{ $total_rides ?? 0}}
                                        </span>
                                        |
                                        <span>{{ trans('lang.dashboard_parcel_total_earnings') }} :
                                            {{ $total_parcel ?? 0 }}
                                        </span>
                                        |
                                        <span>{{ trans('lang.rental') }} :
                                            {{ $total_rental ?? 0 }}
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <!-- end row -->



            <!--charts Start-->

            <div class="daes-sec-sec mt-3">

                <div class="row">

                   <div class="col-md-4 col-lg-4">

                    <div class="card">

                        <div class="card-header no-border">

                            <div class="d-flex justify-content-between">

                                <h3 class="card-title">{{ trans('lang.total_sales') }}</h3>

                            </div>

                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                </div>
                            </div>
                            <div class="position-relative mb-4">

                                <canvas id="sales-chart" height="200"></canvas>

                            </div>

                        </div>

                    </div>

                </div>
                 
                    <div class="col-md-4 col-lg-4">

                        <div class="card">

                            <div class="card-header no-border">

                                <div class="d-flex justify-content-between">

                                    <h3 class="card-title">{{ trans('lang.service_overview') }}</h3>

                                </div>

                            </div>

                            <div class="card-body">

                                <div class="flex-row">

                                    <canvas id="visitors" height="200"></canvas>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 col-lg-4">

                        <div class="card">

                            <div class="card-header no-border">

                                <div class="d-flex justify-content-between">

                                    <h3 class="card-title">{{ trans('lang.sales_overview') }}</h3>

                                </div>

                            </div>

                            <div class="card-body">

                                <div class="flex-row">

                                    <canvas id="commissions" height="200"></canvas>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <!--charts End-->

            <div class="row mt-3">

                <div class="col-lg-12 mt-3">

                    <div class="card">

                        <div class="card-header no-border">

                            <div class="d-flex justify-content-between">

                                <h3 class="card-title">{{ trans('lang.latest_rides') }}</h3>

                            </div>

                        </div>

                        <div class="card-body">
                            <div class="table-responsive m-t-10">

                                <table id="example24"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">

                                    <thead>

                                        <tr>

                                            <th>{{ trans('lang.ride_id') }}</th>

                                            <th>{{ trans('lang.order_number') }}</th>

                                            <th>{{ trans('lang.userName') }}</th>

                                            <th>{{ trans('lang.driver_name') }}</th>

                                            <th>{{ trans('lang.cost_amount') }}</th>

                                            <th>{{ trans('lang.status') }}</th>

                                            <th>{{ trans('lang.created') }}</th>

                                        </tr>

                                    </thead>

                                    <tbody id="append_list12">

                                        @if (count($rides) > 0)
                                            @foreach ($rides as $ride)
                                                                            <tr>

                                                                                <td><a href="{{ route('ride.show', ['id' => $ride->id]) }}">{{ $ride->id }}</a>

                                                                                <td><a
                                                                                        href="{{ route('ride.show', ['id' => $ride->id]) }}">{{ $ride->booking_number }}</a>

                                                                                </td>

                                                                                <td>

                                                                                    @if ($ride->user_id != null)
                                                                                        <a href="{{ route('users.show', ['id' => $ride->user_id]) }}">{{ $ride->userPrenom }}
                                                                                            {{ $ride->userNom }}

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
                                                                                        <a href="{{ route('driver.show', ['id' => $ride->driver_id]) }}">{{ $ride->driverPrenom }}
                                                                                            {{ $ride->driverNom }}</a>
                                                                                    @endif
                                                                                </td>


                                                                                <?php        $montant = floatval($ride->montant);

                                                $total_price = $montant;

                                                $discount = floatval($ride->discount);

                                                if ($discount) {
                                                    $total_price = $montant - $discount;
                                                }

                                                $tax = json_decode($ride->tax, true);

                                                $totalTaxAmount = 0;

                                                if (!empty($tax)) {
                                                    for ($i = 0; $i < sizeof($tax); $i++) {
                                                        $data = $tax[$i];

                                                        if ($data['type'] == 'Percentage') {
                                                            $taxValue = (floatval($data['value']) * $total_price) / 100;
                                                        } else {
                                                            $taxValue = floatval($data['value']);
                                                        }

                                                        $totalTaxAmount += floatval(number_format($taxValue, $currency->decimal_digit));
                                                    }

                                                    $total_price = floatval($total_price) + $totalTaxAmount;
                                                }

                                                if ($ride->tip_amount) {
                                                    $total_price = floatval($total_price) + floatval($ride->tip_amount);
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

                                                                                    @if ($ride->statut == 'completed')
                                                                                        <span class="badge badge-success">{{ $ride->statut }}<span>
                                                                                    @elseif($ride->statut == 'confirmed')
                                                                                                <span class="badge badge-secondary">{{ $ride->statut }}<span>
                                                                                            @elseif($ride->statut == 'new')
                                                                                                        <span class="badge badge-primary">{{ $ride->statut }}<span>
                                                                                                    @elseif($ride->statut == 'rejected')
                                                                                                                <span
                                                                                                                    class="badge badge-danger">{{ $ride->statut }}<span>
                                                                                                            @elseif($ride->statut == 'driver_rejected')
                                                                                                                        <span
                                                                                                                            class="badge badge-danger">{{ trans('lang.driver_rejected') }}<span>
                                                                                                                    @else
                                                                                                                                <span
                                                                                                                                    class="badge badge-warning">{{ $ride->statut }}<span>
                                                                                                                            @endif

                                                                                </td>

                                                                                <td class="dt-time"><span
                                                                                        class="date">{{ date('d F Y', strtotime($ride->creer)) }}</span>

                                                                                    <span class="time">{{ date('h:i A', strtotime($ride->creer)) }}</span>

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

                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-lg-12 mt-3">
                    <div class="card">
                        <div class="card-header no-border">

                            <div class="d-flex justify-content-between">

                                <h3 class="card-title">{{ trans('lang.top_drivers') }}</h3>

                            </div>

                        </div>

                        <div class="card-body">
                            <div class="table-responsive m-t-10">

                                <table id="example24"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">

                                    <thead>

                                        <tr>

                                            <th>{{trans('lang.driver_name')}}</th>

                                            <th>{{trans('lang.email')}}</th>

                                            <th>{{trans('lang.user_phone')}}</th>

                                            <th>{{trans('lang.total_ride')}}</th>


                                        </tr>

                                    </thead>

                                    <tbody id="append_list12">

                                        @if(count($topDrivers) > 0)

                                            @foreach($topDrivers as $driver)
                                                <tr>

                                                    <td>
                                                        @if (file_exists(public_path('assets/images/driver' . '/' . $driver->photo_path)) && !empty($driver->photo_path))
                                                            <img class="rounded" style="width:50px"
                                                                src="{{ asset('assets/images/driver') . '/' . $driver->photo_path }}"
                                                                alt="image">
                                                        @else
                                                            <img class="rounded" style="width:50px"
                                                                src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image">
                                                        @endif
                                                        <a href="{{ route('driver.show', ['id' => $driver->id]) }}">
                                                            {{ $driver->prenom }} {{ $driver->nom }}
                                                        </a>
                                                        @if($driver->is_verified)
                                                            <i class="mdi mdi-verified verified-icon" title="Verified"></i>
                                                        @endif
                                                    </td>
                                                    <td>{{ $driver->email}}</td>
                                                    <td>{{ $driver->phone}}</td>
                                                    <td>{{ $driver->total_rides }}</td>
                                                </tr>

                                            @endforeach

                                        @else

                                            <tr>
                                                <td colspan="11" align="center">{{trans("lang.no_result")}}</td>
                                            </tr>

                                        @endif

                                    </tbody>

                                </table>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- end container -->

    </div>

    <!-- end page-wrapper -->
@endsection

@section('scripts')
    <script src="{{ asset('js/chart.js') }}"></script>

    <script type="text/javascript">
        var currency = '<?php echo $currency->symbole . ' '; ?>';

        var decimal_point = '<?php echo $currency->decimal_digit . ' '; ?>';

        var symbol_at_right = '<?php echo $currency->symbol_at_right; ?>';
        let myChart = null;
        $(document).ready(function () {
            setVisitors();
            setCommision();
            getTotalSales();
        });

        function getTotalSales() {
            const view = viewFilter.value;
            const year = parseInt(yearFilter.value);
            const month = parseInt(monthFilter.value);
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            $.ajax({

                url: "home/sales_overview",

                method: "GET",
                data: {
                    view: view,
                    year: year,
                    month: month,
                    start_date: startDate,
                    end_date: endDate
                },

                success: function (response) {
                    var rideData = [];
                    var parcelData = [];
                    var rentalData = [];
                    for (let i = 1; i <= 12; i++) {
                        rideData.push(parseFloat(response.ride['v' + i]));
                        parcelData.push(parseFloat(response.parcel['v' + i]));
                        rentalData.push(parseFloat(response.rental['v' + i]));
                    }

                    var labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

                    var $salesChart = $('#sales-chart');

                    renderChart($salesChart, rideData, parcelData, rentalData, labels);

                }



            });

        }


        function renderChart(chartNode, rideData, parcelData, rentalData, labels) {
            var ticksStyle = {
                fontColor: '#495057',
                fontStyle: 'bold'
            };

            var mode = 'index';
            var intersect = true;

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(chartNode, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "{{trans('lang.ride_sales')}}",
                        backgroundColor: '#2EC7D9',
                        borderColor: '#2EC7D9',
                        data: rideData
                    },
                    {
                        label: "{{trans('lang.parcel_sales')}}",
                        backgroundColor: '#B6A2DE',
                        borderColor: '#B6A2DE',
                        data: parcelData
                    },
                    {
                        label: "{{trans('lang.rental_sales')}}",
                        backgroundColor: '#B1DB6F',
                        borderColor: '#B1DB6F',
                        data: rentalData
                    },
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        mode: mode,
                        intersect: intersect
                    },
                    hover: {
                        mode: mode,
                        intersect: intersect
                    },
                    legend: {
                        display: true // Show legend now that we have 2 datasets
                    },
                    scales: {
                        yAxes: [{
                            gridLines: {
                                display: true,
                                lineWidth: '4px',
                                color: 'rgba(0, 0, 0, .2)',
                                zeroLineColor: 'transparent'
                            },
                            ticks: $.extend({
                                beginAtZero: true,
                                callback: function (value, index, values) {
                                    return +value.toFixed(decimal_point);
                                }
                            }, ticksStyle)
                        }],
                        xAxes: [{
                            display: true,
                            gridLines: {
                                display: false
                            },
                            ticks: ticksStyle
                        }]
                    },
                    tooltips: {
                        mode: mode,
                        intersect: intersect,
                        callbacks: {
                            label: function (tooltipItem, data) {
                                let datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                let value = parseFloat(tooltipItem.yLabel).toFixed(2);
                                return datasetLabel + ': ' + value;
                            }
                        }
                    }

                }
            });

            return myChart;
        }


        function setVisitors() {



            const data = {

                labels: [

                    "{{ trans('lang.dashboard_total_users') }}",
                    "{{ trans('lang.individual_drivers') }}",
                    "{{ trans('lang.owner_plural') }}",
                    "{{ trans('lang.fleet_drivers') }}",

                ],

                datasets: [{

                    data: [{{ $total_users ?? 0}},  {{ $total_drivers ?? 0 }}, {{ $total_owners ?? 0 }}, {{ $total_fleet_drivers ?? 0 }}],

                    backgroundColor: [
                        '#218be1',
                        '#B1DB6F',
                        '#feb84d',
                        '#9b77f8',



                    ],

                    hoverOffset: 4

                }]

            };



            return new Chart('visitors', {

                type: 'doughnut',

                data: data,

                options: {

                    maintainAspectRatio: false,

                }

            })

        }

        function setCommision() {
            const data = {
                labels: [
                    "{{ trans('lang.dashboard_total_earnings') }}",
                    "{{ trans('lang.admin_commission') }}"
                ],
                datasets: [{
                    data: [
                        parseFloat(jQuery("#total_earning").val()),
                        parseFloat(jQuery("#admin_commission").val())
                    ],
                    backgroundColor: [
                        '#feb84d',
                        '#9b77f8',
                        '#fe95d3'
                    ],
                    hoverOffset: 4
                }]
            };

            return new Chart('commissions', {
                type: 'doughnut',
                data: data,
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, chartData) {
                                let rawValue = chartData.datasets[0].data[tooltipItem.index];
                                let formattedValue = parseFloat(rawValue).toLocaleString(undefined, {
                                    minimumFractionDigits: decimal_point,
                                    maximumFractionDigits: decimal_point
                                });

                                let amount = (symbol_at_right === "true")
                                    ? formattedValue + ' ' + currency
                                    : currency + ' ' + formattedValue;

                                return chartData.labels[tooltipItem.index] + ': ' + amount;
                            }
                        }
                    }
                }
            });
        }
//         const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "Octomber", "November", "December"];
//         const monthFilter = document.getElementById('monthFilter');
//         const yearFilter = document.getElementById('yearFilter');
//         const viewFilter = document.getElementById('viewFilter');
//         const customDateFilters = document.getElementById('customDateFilters');
//         const monthYearFilters = document.getElementById('monthYearFilters');
//         const startDateInput = document.getElementById('startDate');
//         const endDateInput = document.getElementById('endDate');

//         const currentYear = new Date().getFullYear();
//         const currentMonth = new Date().getMonth() + 1;

//         for (let y = currentYear; y >= currentYear - 5; y--) {
//             yearFilter.innerHTML += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
//         }

//         months.forEach((m, index) => {
//             monthFilter.innerHTML += `<option value="${index + 1}" ${index + 1 === currentMonth ? 'selected' : ''}>${m}</option>`;
//         });

//         viewFilter.addEventListener('change', () => {
//             const view = viewFilter.value;

//             if (view === 'month') {
//                 monthFilter.style.display = 'inline-block';
//                 yearFilter.style.display = 'inline-block';
//                 customDateFilters.style.display = 'none';
//             } else if (view === 'year') {
//                 monthFilter.style.display = 'none';
//                 yearFilter.style.display = 'inline-block';
//                 customDateFilters.style.display = 'none';
//             } else if (view === 'custom') {
//                 monthFilter.style.display = 'none';
//                 yearFilter.style.display = 'none';
//                 customDateFilters.style.display = 'inline-block';
//             }
//             customDateFilters.style.display = view === 'custom' ? 'inline-block' : 'none';
//             getTotalSales();
//         });
//         [monthFilter, yearFilter, startDateInput, endDateInput].forEach(el => {
//             el.addEventListener('change', getTotalSales);
//         });
// $('#viewFilter, #monthFilter, #yearFilter, #startDate, #endDate').on('change', function() {
//     $('#dashboardFilterForm').submit();
// });

// Show/hide month/year/custom inputs
function updateFilterVisibility() {
    const view = $('#viewFilter').val();
    if(view === 'month') {
        $('#monthFilter').show();
        $('#yearFilter').show();
        $('#customDateFilters').hide();
    } else if(view === 'year') {
        $('#monthFilter').hide();
        $('#yearFilter').show();
        $('#customDateFilters').hide();
    } else {
        $('#monthFilter').hide();
        $('#yearFilter').hide();
        $('#customDateFilters').show();
    }
}
$('#viewFilter').on('change', updateFilterVisibility);
$(document).ready(updateFilterVisibility);
       

    </script>
@endsection