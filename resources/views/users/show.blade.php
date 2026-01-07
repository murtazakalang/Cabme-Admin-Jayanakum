@extends('layouts.app')

@section('content')

    <div class="page-wrapper userdetail-page">

        <div class="row page-titles">

            <div class="col-md-5 align-self-center">

                <h3 class="text-themecolor">{{trans('lang.user_details')}}</h3>

            </div>

            <div class="col-md-7 align-self-center">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item"><a href="{!! url('/dashboard') !!}">{{trans('lang.dashboard')}}</a></li>

                    <li class="breadcrumb-item"><a href="{!! url('users') !!}">{{trans('lang.user_plural')}}</a></li>

                    <li class="breadcrumb-item active">{{trans('lang.user_details')}}</li>

                </ol>

            </div>

        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4">{{ $totalRides }}</h4>
                                <p class="mb-0 small text-dark-2">{{ trans('lang.total_rides') }}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--21">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4">{{ $totalParcelOrders }}</h4>
                                <p class="mb-0 small text-dark-2">{{ trans('lang.total_parcel_orders') }}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/dparcel.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--24">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4">{{ $totalrentalOrders }}</h4>
                                <p class="mb-0 small text-dark-2">{{ trans('lang.total_rental_orders') }}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/active_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 wallet_balance" id="wallet_balance">
                                    @if ($currency->symbol_at_right == 'true')
                                        {{ number_format($user->amount, $currency->decimal_digit) . '' . $currency->symbole }}
                                    @else
                                        {{ $currency->symbole . '' . number_format($user->amount, $currency->decimal_digit) }}
                                    @endif
                                </h4>
                                <p class="mb-0 small text-dark-2">{{ trans('lang.wallet_balance') }}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_payment.png') }}"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body p-0 pb-5">

                            <div class="user-top">

                                <div class="row align-items-center">

                                    <div class="user-profile col-md-2">

                                        <div class="profile-img">

                                            {{-- @if (file_exists('assets/images/users' . '/' . $user->photo_path) && !empty($user->photo_path)) --}}
                                            @if (file_exists(public_path('assets/images/users' . '/' . $user->photo_path)) && !empty($user->photo_path))
                                                <td><img class="profile-pic" src="{{ asset('assets/images/users') . '/' . $user->photo_path }}" alt="image"></td>
                                            @else
                                                <td><img class="profile-pic" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image"></td>
                                            @endif
                                        </div>

                                    </div>
                                    <div class="user-title col-md-7">
                                        <h4 class="card-title"> Details of {{ $user->prenom }} {{ $user->nom }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#addWalletModal" class="text-white add-wallate btn btn-success"><i class="fa fa-plus"></i> {{trans('lang.add_wallet_amount')}}</a>
                                    </div>
                                </div>
                            </div>

                            <div class="user-detail" role="tabpanel">

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs">

                                    <li role="presentation" class="">
                                        <a href="#information" aria-controls="information" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'information' || Request::get('tab') == '' ? 'active show' : '' }}">{{trans('lang.information')}}</a>
                                    </li>

                                    <li role="presentation" class="">
                                        <a href="#rides" aria-controls="rides" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'rides' ? 'active show' : '' }}">{{trans('lang.rides')}}</a>
                                    </li>

                                    <li role="presentation" class="">
                                        <a href="#parcels" aria-controls="parcels" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'parcels' ? 'active show' : '' }}">{{ trans('lang.parcel') }}</a>
                                    </li>

                                    <li role="presentation" class="">
                                        <a href="#rentals" aria-controls="parcels" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'rentals' ? 'active show' : '' }}">{{ trans('lang.rental') }}</a>
                                    </li>

                                    <li role="presentation" class="">
                                        <a href="#transactions" aria-controls="transactions" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'transactions' ? 'active show' : '' }}">{{trans('lang.wallet_transaction_plural')}}</a>
                                    </li>

                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">

                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'information' || Request::get('tab') == '' ? 'active' : '' }}" id="information">

                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.user_phone') }}:</label>
                                                    <span>{{ $user->country_code }}{{ $user->phone }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.email') }}:</label>
                                                    <span>{{ $user->email }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.status') }} :</label>
                                                    @if ($user->statut == 'yes')
                                                        <span class="badge badge-success">{{trans('lang.enabled')}}</span>
                                                    @else
                                                        <span class="badge badge-warning">{{trans('lang.disabled')}}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.created_at') }} :</label>
                                                    <span class="date">{{ date('d F Y', strtotime($user->creer)) }}</span>
                                                    <span class="time">{{ date('h:i A', strtotime($user->creer)) }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.edited') }} :</label>
                                                    @if ($user->modifier != '0000-00-00 00:00:00')
                                                        <span class="date">{{ date('d F Y', strtotime($user->modifier)) }}</span>
                                                        <span class="time">{{ date('h:i A', strtotime($user->modifier)) }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.rating') }} :</label>
                                                    <span><i class="fa fa-star" style="color:yellow"></i> {{ $user->average_rating ? $user->average_rating : 0.0 }} ({{ $user->review_count ? $user->review_count : 0}})</span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="col-group-btn">
                                                    @if ($user->statut == 'no')
                                                        <a href="{{ route('users.changeStatus', ['id' => $user->id]) }}" class="btn btn-success btn-sm direct-click-btn"> {{ trans('lang.enable_account') }}<i class="fa fa-check"></i> </a>
                                                    @else
                                                        <a href="{{ route('users.changeStatus', ['id' => $user->id]) }}" class="btn btn-warning btn-sm direct-click-btn" > {{trans('lang.disable_account')}} <i class="fa fa-check"></i> </a>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'rides' ? 'active' : '' }}" id="rides">
                                        @if (count($rides) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.order_id') }}</th>
                                                            <th>{{ trans('lang.driver_name') }}</th>
                                                            <th>{{ trans('lang.status') }}</th>
                                                            <th>{{ trans('lang.created') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($rides as $ride)
                                                            <tr>
                                                                <td><a href="{{ route('ride.show', ['id' => $ride->id]) }}">{{ $ride->id }}</a></td>
                                                                <td>
                                                                    @if($ride->driver_id)
                                                                    <a href="{{ route('driver.show', ['id' => $ride->driver_id]) }}">{{ $ride->driverPrenom }} {{ $ride->driverNom }}</a>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($ride->statut == 'completed')
                                                                        <span class="badge badge-success">{{ $ride->statut }}<span>
                                                                            @elseif($ride->statut == 'rejected')
                                                                                <span class="badge badge-danger">{{ $ride->statut }}<span>
                                                                                    @else
                                                                                        <span class="badge badge-warning">{{ $ride->statut }}<span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ date('d F Y h:i A', strtotime($ride->creer)) }}</td>
                                                                <td class="action-btn">
                                                                    <a href="{{ route('ride.show', ['id' => $ride->id]) }}" class="" data-toggle="tooltip" data-bs-original-title="Details"><i class="mdi mdi-eye"></i></a>
                                                                    <a class="delete-btn" name="user-delete" href="{{ route('ride.delete', ['rideid' => $ride->id]) }}" data-toggle="tooltip" data-bs-original-title="Delete"><i class="mdi mdi-delete"></i></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $rides->appends(['tab' => 'rides'])->links() }}
                                                </nav>
                                            </div>
                                        @else
                                            <p>
                                                <center>{{trans('lang.no_result')}}</center>
                                            </p>
                                        @endif
                                    </div>

                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'parcels' ? 'active' : '' }}" id="parcels">
                                        @if (count($parcelOrders) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.order_id') }}</th>
                                                            <th>{{ trans('lang.driver_name') }}</th>
                                                            <th>{{ trans('lang.status') }}</th>
                                                            <th>{{ trans('lang.created') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($parcelOrders as $parcel)
                                                            <tr>
                                                                <td><a href="{{ route('parcel.show', ['id' => $parcel->id]) }}">{{ $parcel->id }}</a></td>
                                                                <td>
                                                                    @if($parcel->driver_id)
                                                                    <a href="{{ route('driver.show', ['id' => $parcel->driver_id]) }}">{{ $parcel->driverPrenom }} {{ $parcel->driverNom }}</a>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($parcel->status == 'completed')
                                                                        <span class="badge badge-success">{{ $parcel->status }}<span>
                                                                            @elseif($parcel->status == 'confirmed')
                                                                                <span class="badge badge-success">{{ $parcel->status }}<span>
                                                                                    @elseif($parcel->status == 'new')
                                                                                        <span class="badge badge-primary">{{ $parcel->status }}<span>
                                                                                            @elseif($parcel->status == 'rejected')
                                                                                                <span class="badge badge-danger">{{ $parcel->status }}<span>
                                                                                                    @elseif($parcel->status == 'driver_rejected')
                                                                                                        <span class="badge badge-danger">{{ trans('lang.driver_rejected') }}<span>
                                                                                                            @else
                                                                                                                <span class="badge badge-warning">{{ $parcel->status }}<span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ date('d F Y h:i A', strtotime($parcel->created_at)) }}</td>
                                                                <td class="action-btn">
                                                                    <a href="{{ route('parcel.show', ['id' => $parcel->id]) }}" class="" data-toggle="tooltip" data-bs-original-title="Details"><i class="mdi mdi-eye"></i></a>
                                                                    <a class="delete-btn" name="user-delete" href="{{ route('parcel.delete', ['rideid' => $parcel->id]) }}" data-toggle="tooltip" data-bs-original-title="Delete"><i class="mdi mdi-delete"></i></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $parcelOrders->appends(['tab' => 'parcels'])->links() }}

                                                </nav>
                                                {{ $parcelOrders->appends(['tab' => 'parcels'])->links('pagination.pagination') }}

                                            </div>
                                        @else
                                            <p>
                                                <center>{{trans('lang.no_result')}}</center>
                                            </p>
                                        @endif
                                    </div>

                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'rentals' ? 'active' : '' }}" id="rentals">
                                        @if (count($rentalOrders) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.order_id') }}</th>
                                                            <th>{{ trans('lang.driver_name') }}</th>
                                                            <th>{{ trans('lang.status') }}</th>
                                                            <th>{{ trans('lang.created') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($rentalOrders as $rental)
                                                            <tr>
                                                                <td><a href="{{ route('rental-orders.show', ['id' => $rental->id]) }}">{{ $rental->id }}</a></td>
                                                                <td>
                                                                    @if($rental->driver_id)
                                                                    <a href="{{ route('driver.show', ['id' => $rental->driver_id]) }}">{{ $rental->driverPrenom }} {{ $rental->driverNom }}</a>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($rental->status == 'completed')
                                                                        <span class="badge badge-success">{{ $rental->status }}<span>
                                                                     @elseif($rental->status == 'confirmed')
                                                                        <span class="badge badge-success">{{ $rental->status }}<span>
                                                                    @elseif($rental->status == 'new')
                                                                        <span class="badge badge-primary">{{ $rental->status }}<span>
                                                                    @elseif($rental->status == 'rejected')
                                                                        <span class="badge badge-danger">{{ $rental->status }}<span>
                                                                    @elseif($rental->status == 'driver_rejected')
                                                                        <span class="badge badge-danger">{{ trans('lang.driver_rejected') }}<span>
                                                                    @else
                                                                        <span class="badge badge-warning">{{ $rental->status }}<span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ date('d F Y h:i A', strtotime($rental->created_at)) }}</td>
                                                                <td class="action-btn">
                                                                    <a href="{{ route('rental-orders.show', ['id' => $rental->id]) }}" class="" data-toggle="tooltip" data-bs-original-title="Details"><i class="mdi mdi-eye"></i></a>
                                                                    <a class="delete-btn" name="user-delete" href="{{ route('rental-orders.delete', ['id' => $rental->id]) }}" data-toggle="tooltip" data-bs-original-title="Delete"><i class="mdi mdi-delete"></i></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $rentalOrders->appends(['tab' => 'rentals'])->links() }}
                                                </nav>
                                                {{ $rentalOrders->appends(['tab' => 'rentals'])->links('pagination.pagination') }}
                                            </div>
                                        @else
                                            <p>
                                                <center>{{trans('lang.no_result')}}</center>
                                            </p>
                                        @endif
                                    </div>

                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'transactions' ? 'active' : '' }}" id="transactions">
                                        @if (count($transactions) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.transaction_id') }}</th>
                                                            <th>{{ trans('lang.amount') }}</th>
                                                            <th>{{ trans('lang.created_at') }}</th>
                                                            <th>{{ trans('lang.payment_method') }}</th>
                                                            <th>{{ trans('lang.note') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($transactions as $transaction)
                                                            <tr>
                                                                <td>{{ $transaction->id }}</td>

                                                                <td>
                                                                @if ($currency->symbol_at_right == 'true')
                                                                    @if ($transaction->is_credited == '0')
                                                                        <span style="color:red">(-{{ number_format(floatval($transaction->amount), $currency->decimal_digit) . '' . $currency->symbole }})</span>
                                                                    @else
                                                                        <span style="color:green">{{ number_format(floatval($transaction->amount), $currency->decimal_digit) . '' . $currency->symbole }}</span>
                                                                    @endif
                                                                @else
                                                                    @if ($transaction->is_credited == '0')
                                                                        <span style="color:red">(-{{ $currency->symbole . '' . number_format(floatval($transaction->amount), $currency->decimal_digit) }})</span>
                                                                    @else
                                                                        <span style="color:green">{{ $currency->symbole . '' . number_format(floatval($transaction->amount), $currency->decimal_digit) }}</span>
                                                                    @endif
                                                                @endif
                                                                </td>
                                                                
                                                                <td>{{ date('d F Y h:i A', strtotime($transaction->created_at)) }}</td>

                                                                @if ($transaction->image)
                                                                    <td><img class="rounded" style="width:50px" src="{{ asset('/assets/images/payment_method/' . $transaction->image) }}" alt="image"></td>
                                                                @else
                                                                    <td>{{ $transaction->payment_method }}"</td>
                                                                @endif

                                                                <td>
                                                                   {{ $transaction->note }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $transactions->appends(['tab' => 'transactions'])->links() }}
                                                </nav>
                                            </div>
                                        @else
                                            <p>
                                                <center>{{trans('lang.no_result')}}</center>
                                            </p>
                                        @endif
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered location_modal">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title locationModalTitle">{{ trans('lang.add_wallet_amount') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <form action="{{ route('users.wallet', $user->id) }}" method="post" class="">
                        @csrf
                        <div class="row">
                          <label class="col-md-12 control-label">{{ trans('lang.amount') }}</label>
                            <div class="col-md-12">
                                <input type="number" name="amount" class="form-control" id="amount" placeholder="Enter Amount">
                                <div id="wallet_error" style="color:red"></div>
                            </div>
                        </div>



                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary save-form-btn" id="add-wallet-btn">{{ trans('submit') }}</a>
                            </button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                                {{ trans('close') }}</a>
                            </button>

                        </div>
                    </form>

                </div>
            </div>

        </div>

    </div>

@endsection

@section('scripts')
    <script>
        $("#add-wallet-btn").click(function() {
            var amount = $('#amount').val();
            if (amount == '') {
                $('#wallet_error').text('{{ trans('lang.add_wallet_amount_error') }}');
                return false;
            }

        });
    </script>
@endsection
