@extends('layouts.app')
@section('content')
    <div class="page-wrapper userdetail-page">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.owner_details') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{!! url('/dashboard') !!}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('owners.index') !!}">{{ trans('lang.owners') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.owner_details') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 total_orders" id="total_orders">{{ $totalRides }}</h4>
                                <p class="mb-0 small text-dark-2">{{ trans('lang.total_rides') }}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--24">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 total_orders" id="total_orders">{{ $totalParcelOrders }}</h4>
                                <p class="mb-0 small text-dark-2">{{ trans('lang.total_parcel_orders') }}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/dparcel.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--18">
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
                    <div class="card card-box-with-icon bg--21">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4">{{ count($ownerDrivers) }}</h4>
                                <p class="mb-0 small text-dark-2">{{ trans('lang.total_drivers') }}</p>
                            </div>
                            <span class="box-icon ab"><img src="{{ asset('images/home_driver.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--7">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="card-box-with-content">
                                <h4 class="text-dark-2 mb-1 h4 wallet_balance" id="wallet_balance">
                                    @if ($currency->symbol_at_right == 'true')
                                        @if (substr($owner->amount, 0, 1) == '-')
                                            <span style="color:red">-{{ number_format(floatval(substr($owner->amount, 1)), $currency->decimal_digit) . '' . $currency->symbole }}</span>
                                        @else
                                            <span style="color:green">{{ number_format(floatval($owner->amount), $currency->decimal_digit) . '' . $currency->symbole }}</span>
                                        @endif
                                    @else
                                        @if (substr($owner->amount, 0, 1) == '-')
                                            <span style="color:red">-{{ $currency->symbole . '' . number_format(floatval(substr($owner->amount, 1)), $currency->decimal_digit) }}</span>
                                        @else
                                            <span style="color:green">{{ $currency->symbole . '' . number_format(floatval($owner->amount), $currency->decimal_digit) }}</span>
                                        @endif
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
                                            @if (!empty($owner->photo_path) && file_exists(public_path('assets/images/driver' . '/' . $owner->photo_path)))
                                                <td><img class="profile-pic" src="{{ asset('assets/images/driver') . '/' . $owner->photo_path }}" alt="image"></td>
                                            @else
                                                <td><img class="profile-pic" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image"></td>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="user-title col-md-7">
                                        <h4 class="card-title"> {{trans('lang.details_of')}} {{ !empty($owner->prenom) ? $owner->prenom . ' ' . $owner->nom : 'Unknown Owner' }}</h4>
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
                                        <a href="#driver" aria-controls="driver" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'driver' ? 'active show' : '' }}">{{ trans('lang.drivers') }}</a>
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
                                        <a href="#vehicle_details" aria-controls="vehicle" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'vehicle' ? 'active show' : '' }}">{{ trans('lang.vehicle') }}</a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#transaction" aria-controls="transaction" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'transaction' ? 'active show' : '' }}">{{ trans('lang.wallet_transaction') }} </a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#subscription_history" aria-controls="subscription_history" role="tab" data-toggle="tab" class="{{ Request::get('tab') == 'subscription_history' ? 'active show' : '' }}">{{ trans('lang.subscription_history') }}
                                        </a>
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'information' || Request::get('tab') == '' ? 'active' : '' }}" id="information">
                                        <div class="mb-3">
                                            @if ($subscriptionModel || $commissionModel)
                                                <a href="javascript:void(0)" data-toggle="modal" data-target="#changeSubscriptionModal" class="text-white change-plan btn btn-success"><i class="fa fa-plus pr-2"></i>{{ trans('lang.change_subscription_plan') }}</a>
                                            @endif
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.user_phone') }}:</label>
                                                    <span>{{ $owner->phone }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.email') }}:</label>
                                                    <span>{{ $owner->email }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.bank_name') }}:</label>
                                                    <span>{{ $owner->bank_name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.branch_name') }}:</label>
                                                    <span>{{ $owner->branch_name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.holder_name') }}:</label>
                                                    <span>{{ $owner->holder_name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.account_no') }}
                                                        :</label>
                                                    <span>{{ $owner->account_no }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.ifsc_code') }}
                                                        :</label>
                                                    <span>{{ $owner->ifsc_code }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.Other_info') }}
                                                        :</label>
                                                    <span>{{ $owner->other_info }}</span>
                                                </div>
                                            </div>
                                             <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.zone') }}
                                                        :</label>
                                                    <span>{{ $zone_name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.service_type') }}:</label>
                                                    <span>{{ $owner->service_type }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.created_at') }} :</label>
                                                    <span class="date">{{ date('d F Y', strtotime($owner->creer)) }}</span>
                                                    <span class="time">{{ date('h:i A', strtotime($owner->creer)) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.edited') }}:</label>
                                                    @if ($owner->modifier != '0000-00-00 00:00:00')
                                                        <span class="date">{{ date('d F', strtotime($owner->modifier) ) }}</span>
                                                        <span class="time">{{ date('h:i', strtotime($owner->modifier) ) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for="" class="font-weight-bold">{{ trans('lang.status') }} :</label>
                                                    @if ($owner->statut == 'yes')
                                                        <span class="badge badge-success">{{trans('lang.enabled')}}</span>
                                                    @else
                                                        <span class="badge badge-warning">{{trans('lang.disabled')}}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group-btn">
                                                    @if ($owner->statut == 'no')
                                                        <a href="{{ route('driver.changeStatus', ['id' => $owner->id]) }}" class="btn btn-success btn-sm" data-toggle="tooltip" data-original-title="Activate">{{ trans('lang.enable_account') }}<i class="fa fa-check"></i> </a>
                                                    @else
                                                        <a href="{{ route('driver.changeStatus', ['id' => $owner->id]) }}" class="btn btn-warning btn-sm" data-toggle="tooltip" data-original-title="Activate"> Disable account <i class="fa fa-check"></i> </a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="col-group">
                                                    <div class="d-flex align-items-center pb-3">
                                                        <label for="" class="font-weight-bold">{{ trans('lang.active_subscription_plan') }}</label>
                                                        @if (!empty($owner->subscription_plan))
                                                            <a href="javascript:void(0)" data-toggle="modal" data-target="#updateLimitModal" class="btn-primary btn rounded-full update-limit text-white btn btn-sm btn-success mb-2 ml-auto">{{ trans('lang.update_plan_limit') }}</a>
                                                        @endif
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.plan_name') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? $owner->subscription_plan['name'] : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.plan_type') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? $owner->subscription_plan['type'] : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.total_booking_limit') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['bookingLimit'] == '-1' ? trans('lang.unlimited') : $owner->subscription_plan['bookingLimit']) : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.available_booking_limit') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? ($owner->subscriptionTotalOrders == '-1' ? trans('lang.unlimited') : $owner->subscriptionTotalOrders) : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.total_vehicle_limit') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['vehicle_limit'] == '-1' ? trans('lang.unlimited') : $owner->subscription_plan['vehicle_limit']) : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.available_vehicle_limit') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? ($owner->subscriptionTotalVehicle == '-1' ? trans('lang.unlimited') : $owner->subscriptionTotalVehicle) : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.total_driver_limit') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['driver_limit'] == '-1' ? trans('lang.unlimited') : $owner->subscription_plan['driver_limit']) : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.available_driver_limit') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? ($owner->subscriptionTotalDriver == '-1' ? trans('lang.unlimited') : $owner->subscriptionTotalDriver) : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.dispatcher_access') }}</label>
                                                                <span class="ml-auto">
                                                                    <span class="ml-auto">
                                                                        @if(!empty($owner->subscription_plan))
                                                                            @if($owner->subscription_plan['dispatcher_access'] == 'yes')
                                                                                <span class="badge badge-success">{{ trans('lang.yes') }}</span>
                                                                            @else
                                                                                <span class="badge badge-danger">{{ trans('lang.no') }}</span>
                                                                            @endif
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="col-12 col-group d-flex">
                                                                <label class="font-weight-bold">{{ trans('lang.plan_expires_at') }}</label>
                                                                <span class="ml-auto">
                                                                    {{ !empty($owner->subscription_plan) ? ($owner->subscriptionExpiryDate == null ? trans('lang.unlimited') : date('d F Y h:i A', strtotime($owner->subscriptionExpiryDate))) : 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="col-12 col-group d-flex-none">
                                                                <label class="font-weight-bold">{{ trans('lang.available_features') }}</label>
                                                                @if (!empty($owner->subscription_plan) && !empty($owner->subscription_plan['plan_points']))
                                                                    <ul class="user-features-list">
                                                                        @foreach ($owner->subscription_plan['plan_points'] as $point)
                                                                            <li>{{ $point }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'driver' ? 'active' : '' }}" id="driver">
                                        @if (count($ownerDrivers) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped exclude-css">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.extra_image') }}</th>
                                                            <th>{{ trans('lang.driver_name') }}</th>
                                                            <th>{{ trans('lang.email') }}</th>
                                                            <th>{{ trans('lang.user_phone') }}</th>
                                                            <th>{{ trans('lang.vehicle_type') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($ownerDrivers as $value)
                                                            <tr>
                                                                @if (!empty($value->photo_path) && file_exists(public_path('assets/images/driver' . '/' . $value->photo_path)))
                                                                    <td> <img class="rounded" style="width:50px" src="{{ asset('assets/images/driver') . '/' . $value->photo_path }}" alt="image"></td>
                                                                @else
                                                                    <td><img class="rounded" style="width:50px" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image"></td>
                                                                @endif
                                                                <td><a href="{{ route('driver.show', ['id' => $value->id]) }}"> {{ $value->prenom }} {{ $value->nom }}</a></td>
                                                                <td>{{ $value->email }}</td>
                                                                <td>{{ $value->phone }}</td>
                                                                <td>{{ $value->libelle }}</td>
                                                                <td class="action-btn">
                                                                    <a class="delete-btn" name="user-delete" href="{{ route('driver.delete', ['id' => $value->id]) }}"><i class="mdi mdi-delete"></i></a>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $ownerDrivers->appends(['tab' => 'driver'])->links() }}
                                                </nav>
                                            </div>
                                        @else
                                            <p>
                                                <center>{{trans('lang.no_result')}}</center>
                                            </p>
                                        @endif
                                    </div>
                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'rides' ? 'active' : '' }}" id="rides">
                                        @if (count($rides) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.ride_id') }}</th>
                                                            <th>{{ trans('lang.driver_name') }}</th>
                                                            <th>{{ trans('lang.ride_type') }}</th>
                                                            <th>{{ trans('lang.status') }}</th>
                                                            <th>{{ trans('lang.created') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($rides as $ride)
                                                            <tr>
                                                                <td><a href="{{ route('ride.show', ['id' => $ride->id]) }}">{{ $ride->id }}</a>
                                                                </td>
                                                                <td><a href="{{ route('driver.show', ['id' => $ride->driver_id]) }}">{{ $ride->driverPrenom }}
                                                                        {{ $ride->driverNom }}</a></td>
                                                                <td>
                                                                    @if ($ride->ride_type == 'dispatcher')
                                                                        {{ trans('lang.dispatcher') }}
                                                                    @else
                                                                        {{ trans('lang.normal') }}
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
                                                                    <a href="{{ route('ride.show', ['id' => $ride->id]) }}" class="" data-toggle="tooltip" data-original-title="Details"><i class="mdi mdi-eye"></i></a>
                                                                    <a id="'+val.id+'" class="do_not_delete" name="user-delete" href="{{ route('ride.delete', ['rideid' => $ride->id]) }}"><i class="mdi mdi-delete"></i></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $rides->appends(['tab' => 'rides'])->links() }}
                                                </nav>
                                                {{ $rides->appends(['tab' => 'rides'])->links('pagination.pagination') }}
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
                                                            <th>{{ trans('lang.parcel_id') }}</th>
                                                            <th>{{ trans('lang.userName') }}</th>
                                                            <th>{{ trans('lang.status') }}</th>
                                                            <th>{{ trans('lang.created') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($parcelOrders as $parcel)
                                                            <tr>
                                                                <td><a href="{{ route('parcel.show', ['id' => $parcel->id]) }}">{{ $parcel->id }}</a>
                                                                </td>
                                                                <td><a href="{{ route('users.show', ['id' => $parcel->user_id]) }}">{{ $parcel->userPrenom }}
                                                                        {{ $parcel->userNom }}</a></td>
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
                                                                <td>{{ date('d F Y h:i A', strtotime($parcel->created_at)) }}
                                                                </td>
                                                                <td class="action-btn">
                                                                    <a href="{{ route('parcel.show', ['id' => $parcel->id]) }}" class="" data-toggle="tooltip" data-original-title="Details"><i class="mdi mdi-eye"></i></a>
                                                                    <a id="'+val.id+'" class="do_not_delete" name="user-delete" href="{{ route('parcel.delete', ['rideid' => $parcel->id]) }}"><i class="mdi mdi-delete"></i></a>
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
                                                                <td><a href="{{ route('driver.show', ['id' => $rental->driver_id]) }}">{{ $rental->driverPrenom }} {{ $rental->driverNom }}</a></td>
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
                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'vehicle' ? 'active' : '' }}" id="vehicle_details">
                                        <div class="mb-3">
                                            <a href="javascript:void(0)" data-toggle="modal" data-target="#addVehicleModal" class="text-white  btn btn-success" onclick="checkVehicleLimit(event, {{ $vehicleCount }}, {{ $activePlan->vehicle_limit ?? 'null' }})"><i class="fa fa-plus pr-2"></i>{{ trans('lang.add_new_vehicle') }}</a>
                                        </div>
                                        @if (count($vehicle) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped exclude-css">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.vehicle_type') }}</th>
                                                            <th>{{ trans('lang.brand') }}</th>
                                                            <th>{{ trans('lang.vehicle_model') }}</th>
                                                            <th>{{ trans('lang.car_number') }}</th>
                                                            <th>{{ trans('lang.number_of_pessanger') }}</th>
                                                            <th>{{ trans('lang.vehicle_color') }}</th>
                                                            <th>{{ trans('lang.vehicle_milage') }}</th>
                                                            <th>{{ trans('lang.vehicle_km') }}</th>
                                                            <th>{{ trans('lang.driver') }}</th>
                                                            <th>{{ trans('lang.action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($vehicle as $value)
                                                            <tr>
                                                                <td>{{ $value->vehicle_type }}</td>
                                                                <td>{{ $value->brand }}</td>
                                                                <td>{{ $value->model }}</td>
                                                                <td>{{ $value->numberplate }}</td>
                                                                <td>{{ $value->passenger }}</td>
                                                                <td>{{ $value->color }}</td>
                                                                <td>{{ $value->milage }}</td>
                                                                <td>{{ $value->km }}</td>
                                                                @if (!empty($value->id_conducteur))
                                                                    <td><a href="{{ route('driver.show', ['id' => $value->id_conducteur]) }}"> {{ $value->prenom }} {{ $value->nom }}</a></td>
                                                                @else
                                                                    <td></td>
                                                                @endif
                                                                <td class="action-btn">
                                                                    <a href="javascript:void(0)" data-toggle="modal" id="{{ $value->id }}" name='edit-vehicle' data-target="#editVehicleModal" ><i class="mdi mdi-lead-pencil"></i></a>
                                                                    @if (!empty($value->id_conducteur))
                                                                        <a href="{{ route('vehicle.remove.driver', $value->id) }}" class="btn  btn-danger py-1 px-2 ml-2">{{ trans('lang.remove_driver') }}</a>
                                                                    @else
                                                                        <a href="javascript:void(0)" data-toggle="modal" id="{{ $value->id }}" name='assign-driver' data-target="#assignDriverModal" class="btn btn-sm btn-success py-1 px-2 ml-2">{{ trans('lang.assign_driver') }}</a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $transactions->appends(['tab' => 'transaction'])->links() }}
                                                </nav>
                                            </div>
                                        @else
                                            <p>
                                                <center>{{trans('lang.no_result')}}</center>
                                            </p>
                                        @endif
                                    </div>
                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'transaction' ? 'active' : '' }}" id="transaction">
                                        @if (count($transactions) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped exclude-css">
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
                                                                <td>{{ date('d F Y h:i A', strtotime($transaction->created_at)) }} </td>
                                                                @if ($transaction->image)
                                                                    <td><img class="rounded" style="{{ $transaction->image == 'paystack.png' ? 'width: 90px;' : 'width: 50px;' }}" src="{{ asset('/assets/images/payment_method/' . $transaction->image) }}" alt="image"></td>
                                                                @else
                                                                    <td>{{ $transaction->payment_method }}"</td>
                                                                @endif
                                                                <td>{{ $transaction->note }} </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $transactions->appends(['tab' => 'transaction'])->links() }}
                                                </nav>
                                            </div>
                                        @else
                                            <p>
                                                <center>{{trans('lang.no_result')}}</center>
                                            </p>
                                        @endif
                                    </div>
                                    <div role="tabpanel" class="tab-pane {{ Request::get('tab') == 'subscription_history' ? 'active' : '' }}" id="subscription_history">
                                        @if (count($history) > 0)
                                            <div class="table-responsive">
                                                <table class="display nowrap table table-hover table-striped table-bordered table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.plan_name') }}</th>
                                                            <th>{{ trans('lang.plan_type') }}</th>
                                                            <th>{{ trans('lang.plan_expires_at') }}</th>
                                                            <th>{{ trans('lang.purchase_date') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="append_list12">
                                                        @foreach ($history as $value)
                                                            <tr>
                                                                <td>
                                                                    {{ $value->subscription_plan['name'] }}
                                                                    @if($value->status == "active")
                                                                        <span class="badge badge-success">{{ $value->status }}</span>
                                                                    @else
                                                                        <span class="badge badge-danger">{{ $value->status }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($value->subscription_plan['type'] == 'free')
                                                                        <span class="badge badge-success">{{ trans('lang.free') }}</span>
                                                                    @else
                                                                        <span class="badge badge-danger">{{ trans('lang.paid') }}</span>
                                                                    @endif
                                                                </td>
                                                                @if (!empty($value->expiry_date))
                                                                    <td>{{ date('d F Y h:i A', strtotime($value->expiry_date)) }}
                                                                    </td>
                                                                @else
                                                                    <td>{{ trans('lang.unlimited') }}</td>
                                                                @endif
                                                                <td>{{ date('d F Y h:i A', strtotime($value->created_at)) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <nav aria-label="Page navigation example" class="custom-pagination">
                                                    {{ $history->appends(['tab' => 'subscription_history'])->links() }}
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
                    <form action="{{ route('driver.wallet', $owner->id) }}" method="post" class="">
                        @csrf
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.amount') }}</label>
                                    <div class="col-12">
                                        <input type="number" name="amount" class="form-control" id="amount" placeholder="Enter Amount">
                                        <div id="wallet_error" style="color:red"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="add-wallet-btn">{{ trans('submit') }}</a>
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
    <div class="modal fade" id="changeSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true" style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.business_plans') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-lg-12 ml-lg-auto mr-lg-auto">
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex top-title-section pb-4 mb-2 justify-content-between">
                                        <div class="d-flex top-title-left align-start-center">
                                            <div class="top-title">
                                                <h3 class="mb-0">{{ trans('lang.choose_your_business_plan') }}</h3>
                                                <p class="mb-0 text-dark-2">
                                                    {{ trans('lang.choose_your_business_plan_description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="row" id="default-plan">
                                        @foreach ($plans as $plan)
                                            @php
                                                $activeClass = $plan->id == $activeSubscriptionId ? '<span class="badge badge-success">' . trans('lang.active') . '</span>' : '';
                                                $buttonText = $plan->id == $activeSubscriptionId ? trans('lang.renew_plan') : trans('lang.select_plan');
                                            @endphp
                                            @if ($plan->id == '1')
                                                @if ($commissionModel)
                                                    <div class="col-md-3 mb-3 pricing-card pricing-card-commission">
                                                        <div class="pricing-card-inner">
                                                            <div class="pricing-card-top">
                                                                <div class="d-flex align-items-center pb-4">
                                                                    <span class="pricing-card-icon mr-4"><img src="{{ asset('assets/images/subscription') . '/' . $plan->image }}" alt="{{ $plan->name }}"></span>
                                                                </div>
                                                                <div class="pricing-card-price">
                                                                    <h3 class="text-dark-2">{!! $plan->name !!}
                                                                        {!! $activeClass !!}</h3>
                                                                    <span class="price-day">{{ $adminCommission ?? '' }}
                                                                        {{ trans('lang.commision_per_order') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="pricing-card-content pt-3 mt-3 border-top">
                                                                <ul class="pricing-card-list text-dark-2">
                                                                    <li><span class="mdi mdi-check"></span>{{ trans('lang.pay_commission_of') }}
                                                                        {{ $adminCommission ?? '' }}
                                                                        {{ trans('lang.on_each_order') }}
                                                                    </li>
                                                                    @foreach ($plan->plan_points as $point)
                                                                        <li><span class="mdi mdi-check"></span>{{ $point }}
                                                                        </li>
                                                                    @endforeach
                                                                    <li><span class="mdi mdi-check"></span>{{ trans('lang.unlimited') }}
                                                                        {{ trans('lang.bookings') }}
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="pricing-card-btm">
                                                                <a href="javascript:void(0)" onClick="chooseSubscriptionPlan('{{ $plan->id }}')" class="btn rounded-full active-btn btn-primary">{{ $buttonText }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                @if ($subscriptionModel)
                                                    <div class="col-md-3 mt-2 pricing-card pricing-card-subscription {{ $plan->name }}">
                                                        <div class="pricing-card-inner">
                                                            <div class="pricing-card-top">
                                                                <div class="d-flex align-items-center pb-4">
                                                                    <span class="pricing-card-icon mr-4"><img src="{{ asset('assets/images/subscription') . '/' . $plan->image }}" alt="{{ $plan->name }}"></span>
                                                                    <h2 class="text-dark-2">{!! $plan->name !!}
                                                                        {!! $activeClass !!}</h2>
                                                                </div>
                                                                <p class="text-muted">{{ $plan->description }}</p>
                                                                <div class="pricing-card-price">
                                                                    <h3 class="text-dark-2">
                                                                        {{ intval($plan->price == 0) ? trans('lang.free') : ($currency->symbol_at_right == 'true' ? number_format($plan->price, $currency->decimal_degit ?? 2) . $currency->symbole : $currency->symbole . number_format($plan->price, $currency->decimal_degit ?? 2)) }}
                                                                    </h3>
                                                                    <span class="price-day">{{ $plan->expiryDay == -1 ? trans('lang.unlimited') : $plan->expiryDay }}
                                                                        {{ trans('lang.days') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="pricing-card-content pt-3 mt-3 border-top">
                                                                <ul class="pricing-card-list text-dark-2">
                                                                    @foreach ($plan->plan_points as $point)
                                                                        <li><span class="mdi mdi-check"></span>{{ $point }}
                                                                        </li>
                                                                    @endforeach
                                                                    <li><span class="mdi mdi-check"></span>{{ $plan->bookingLimit == -1 ? trans('lang.unlimited') : $plan->bookingLimit }}
                                                                        {{ trans('lang.bookings') }}
                                                                    </li>
                                                                    <li><span class="mdi mdi-check"></span>{{ $plan->vehicle_limit }} {{ trans('lang.vehicle_limit') }} </li>
                                                                    <li><span class="mdi mdi-check"></span>{{ $plan->driver_limit }} {{ trans('lang.driver_limit') }}</li>
                                                                    @if($plan->dispatcher_access == "yes")
                                                                    <li><span class="mdi mdi-check"></span>{{ trans('lang.dispatcher_access') }}</li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                            <div class="pricing-card-btm">
                                                                <a href="javascript:void(0)" onClick="chooseSubscriptionPlan('{{ $plan->id }}')" class="btn rounded-full">{{ $buttonText }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="checkoutSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true" style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.shift_to_plan') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form class="">
                        <div class="subscription-section">
                            <div class="subscription-section-inner">
                                <div class="card-body">
                                    <div class="row" id="plan-details"></div>
                                    <div class="pay-method-section pt-4 manual_pay_div">
                                        <h6 class="text-dark-2 h6 mb-3 pb-3">{{ trans('lang.pay_via_online') }}</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="pay-method-box d-flex align-items-center">
                                                    <div class="pay-method-icon">
                                                        <img src="{{ asset('images/wallet_icon_ic.png') }}">
                                                    </div>
                                                    <div class="form-check ">
                                                        <h6 class="text-dark-2 h6 mb-0">{{ trans('lang.manual_pay') }}
                                                        </h6>
                                                        <input type="radio" id="manual_pay" name="payment_method" value="manual_pay" checked="">
                                                        <label class="control-label mb-0" for="manual_pay"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-top">
                                    <div class="align-items-center justify-content-between">
                                        <div class="edit-form-group btm-btn text-right">
                                            <div class="card-block-active-plan">
                                                <a href="" class="btn btn-default rounded-full mr-2" data-dismiss="modal">{{ trans('lang.cancel_plan') }}</a>
                                                <input type="hidden" id="plan_id" name="plan_id" value="">
                                                <button type="button" class="btn-primary btn rounded-full" onclick="finalCheckout()">{{ trans('lang.change_plan') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updateLimitModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.update_plan_limit') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('subscription-limit.update', $owner->id) }}" method="post" enctype="multipart/form-data" id="update-limit-form">
                        @csrf
                        @method('PUT')
                        @if (!empty($owner->subscription_plan))
                            <div class="form-row">
                                <div class="form-group row">
                                    <div class="form-group row width-100">
                                        <label class="control-label">{{ trans('lang.maximum_booking_limit') }}</label>
                                        <div class="form-check width-100">
                                            <input type="radio" id="unlimited_booking" name="set_booking_limit" value="unlimited" {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['bookingLimit'] == '-1' ? 'checked' : '') : '' }}>
                                            <label class="control-label" for="unlimited_booking">{{ trans('lang.unlimited') }}</label>
                                        </div>
                                        <div class="d-flex">
                                            <div class="form-check width-50 limited_booking_div">
                                                <input type="radio" id="limited_booking" name="set_booking_limit" value="limited" {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['bookingLimit'] != '-1' ? 'checked' : '') : '' }}>
                                                <label class="control-label" for="limited_booking">{{ trans('lang.limited') }}</label>
                                            </div>
                                            @if ($owner->subscription_plan['bookingLimit'] == '-1')
                                                @php $limitClass='d-none'@endphp
                                            @else
                                                @php $limitClass=''@endphp
                                            @endif
                                            <div class="form-check width-50 {{ $limitClass }} booking-limit-div">
                                                <input type="number" id="booking_limit" name="booking_limit" class="form-control" value="{{ $owner->subscription_plan['bookingLimit'] != '-1' ? $owner->subscription_plan['bookingLimit'] : '' }}" placeholder="{{ trans('lang.ex_1000') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="control-label">{{ trans('lang.maximum_vehicle_limit') }}</label>
                                        <div class="form-check width-100">
                                            <input type="radio" id="unlimited_vehicle" name="set_vehicle_limit" value="unlimited" {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['vehicle_limit'] == '-1' ? 'checked' : '') : '' }}>
                                            <label class="control-label" for="unlimited_vehicle">{{ trans('lang.unlimited') }}</label>
                                        </div>
                                        <div class="d-flex">
                                            <div class="form-check width-50 limited_vehicle_div">
                                                <input type="radio" id="limited_vehicle" name="set_vehicle_limit" value="limited" {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['vehicle_limit'] != '-1' ? 'checked' : '') : '' }}>
                                                <label class="control-label" for="limited_vehicle">{{ trans('lang.limited') }}</label>
                                            </div>
                                            @if ($owner->subscription_plan['vehicle_limit'] == '-1')
                                                @php $limitClass='d-none'@endphp
                                            @else
                                                @php $limitClass=''@endphp
                                            @endif
                                            <div class="form-check width-50 {{ $limitClass }} vehicle-limit-div">
                                                <input type="number" id="vehicle_limit" name="vehicle_limit" class="form-control" value="{{ $owner->subscription_plan['vehicle_limit'] != '-1' ? $owner->subscription_plan['vehicle_limit'] : '' }}" placeholder="{{ trans('lang.ex_1000') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="control-label">{{ trans('lang.maximum_driver_limit') }}</label>
                                        <div class="form-check width-100">
                                            <input type="radio" id="unlimited_driver" name="set_driver_limit" value="unlimited" {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['driver_limit'] == '-1' ? 'checked' : '') : '' }}>
                                            <label class="control-label" for="unlimited_driver">{{ trans('lang.unlimited') }}</label>
                                        </div>
                                        <div class="d-flex">
                                            <div class="form-check width-50 limited_vehicle_div">
                                                <input type="radio" id="limited_driver" name="set_driver_limit" value="limited" {{ !empty($owner->subscription_plan) ? ($owner->subscription_plan['driver_limit'] != '-1' ? 'checked' : '') : '' }}>
                                                <label class="control-label" for="limited_driver">{{ trans('lang.limited') }}</label>
                                            </div>
                                            @if ($owner->subscription_plan['driver_limit'] == '-1')
                                                @php $limitClass='d-none'@endphp
                                            @else
                                                @php $limitClass=''@endphp
                                            @endif
                                            <div class="form-check width-50 {{ $limitClass }} driver-limit-div">
                                                <input type="number" id="driver_limit" name="driver_limit" class="form-control" value="{{ $owner->subscription_plan['driver_limit'] != '-1' ? $owner->subscription_plan['driver_limit'] : '' }}" placeholder="{{ trans('lang.ex_1000') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <span class="limit_err text-danger font-weight-bold"></span>
                                </div>
                            </div>
                        @endif
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary update-plan-limit">{{ trans('submit') }}</a></button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                                {{ trans('close') }}</a>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addVehicleModal" tabindex="-1" role="dialog" aria-hidden="true" style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.car_details') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('owner-vehicle.create', $owner->id) }}" method="post" class="">
                        @csrf
                        <div class="form-row">                            
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_type') }}</label>
                                <div class="col-7">
                                    <select class="form-control model" name="id_type_vehicule" id="id_type_vehicule">
                                        <option value="">{{ trans('lang.select_type') }}</option>
                                        @foreach ($vehicleType as $value)
                                            <option value="{{ $value->id }}">{{ $value->libelle }}</option>
                                        @endforeach
                                    </select>
                                    <div class="vehicle_type_err d-none text-danger">{{ trans('lang.vehicle_type_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_brand') }}</label>
                                <div class="col-7">
                                    <select class="form-control brand_id" name="brand" id="brand">
                                        <option value="">{{ trans('lang.select_brand') }}</option>
                                        @foreach ($brand as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="brand_err d-none text-danger">{{ trans('lang.car_brand_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_model') }}</label>
                                <div class="col-7">
                                    <select class="form-control model" name="model" id="model">
                                        <option value="">{{ trans('lang.select_model') }}</option>                                    
                                    </select>
                                    <div class="car_model_err d-none text-danger">{{ trans('lang.car_model_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_km') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" name="km" id="km" value="{{ Request::old('km') }}">
                                    <div class="vehicle_km_err d-none text-danger">{{ trans('lang.vehicle_km_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_milage') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control model" name="milage" id="milage" value="{{ Request::old('milage') }}">
                                    <div class="vehicle_milage_err text-danger d-none">{{ trans('lang.vehicle_milage_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_numberplate') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control car_number" name="car_number" id="car_number" value="{{ Request::old('car_number') }}">
                                    <div class="vehicle_number_err text-danger d-none">{{ trans('lang.car_number_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_color') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control color" name="color" id="color" value="{{ Request::old('color') }}">
                                    <div class="vehicle_color_err text-danger d-none">
                                        {{ trans('lang.car_color_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.number_of_pessanger') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" name="passenger" id="passenger" value="{{ Request::old('passenger') }}">
                                    <div class="passenger_err text-danger d-none">
                                        {{ trans('lang.number_of_passenger_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.registration_year') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" name="registration_year" id="registration_year" value="{{ Request::old('registration_year') }}"  min="1980" max="{{ date('Y') }}" step="1" >
                                    <div class="registration_year_err text-danger d-none">
                                        {{ trans('lang.registration_year_help') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="add-vehicle-btn">{{ trans('submit') }}</a>
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
    <div class="modal fade" id="editVehicleModal" tabindex="-1" role="dialog" aria-hidden="true" style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.car_details') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form action="" method="post" class="">
                        @csrf
                        <div class="form-row">
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_type') }}</label>
                                <div class="col-7">
                                    <select class="form-control model" name="id_type_vehicule" id="id_type_vehicule">
                                        <option value="">{{ trans('lang.select_type') }}</option>
                                        @foreach ($vehicleType as $value)
                                            <option value="{{ $value->id }}">{{ $value->libelle }}</option>
                                        @endforeach
                                    </select>
                                    <div class="vehicle_type_err d-none text-danger">{{ trans('lang.vehicle_type_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_brand') }}</label>
                                <div class="col-7">
                                    <select class="form-control brand_id" name="brand" id="brand">
                                        <option value="">{{ trans('lang.select_brand') }}</option>
                                        @foreach ($brand as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="brand_err d-none text-danger">{{ trans('lang.car_brand_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_model') }}</label>
                                <div class="col-7">
                                    <select class="form-control model" name="model" id="model">
                                        <option value="">{{ trans('lang.select_model') }}</option>  
                                        @foreach ($model as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="car_model_err d-none text-danger">{{ trans('lang.car_model_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_km') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" name="km" id="km" value="{{ Request::old('km') }}">
                                    <div class="vehicle_km_err d-none text-danger">{{ trans('lang.vehicle_km_help') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_milage') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control model" name="milage" id="milage" value="{{ Request::old('milage') }}">
                                    <div class="vehicle_milage_err text-danger d-none">{{ trans('lang.vehicle_milage_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_numberplate') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control car_number" name="car_number" id="car_number" value="{{ Request::old('car_number') }}">
                                    <div class="vehicle_number_err text-danger d-none">{{ trans('lang.car_number_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_color') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control color" name="color" id="color" value="{{ Request::old('color') }}">
                                    <div class="vehicle_color_err text-danger d-none">
                                        {{ trans('lang.car_color_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.number_of_pessanger') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" name="passenger" id="passenger" value="{{ Request::old('passenger') }}">
                                    <div class="passenger_err text-danger d-none">
                                        {{ trans('lang.number_of_passenger_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.registration_year') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" name="registration_year" id="registration_year" value="{{ Request::old('registration_year') }}"  min="1900" max="{{ date('Y') }}" step="1" >
                                    <div class="registration_year_err text-danger d-none">
                                        {{ trans('lang.registration_year_help') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="edit-vehicle-btn">{{ trans('submit') }}</button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">{{ trans('close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="assignDriverModal" tabindex="-1" role="dialog" aria-hidden="true" style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.select_driver') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('vehicle.assign.driver') }}" method="post" class="">
                        @csrf
                        <div class="form-row">
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.select_driver') }}</label>
                                <div class="col-7">
                                    <select class="form-control model" name="driver" id="assign_driver">
                                        <option value="">{{ trans('lang.select_driver') }}</option>
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->prenom }} {{ $driver->nom }}</option>
                                        @endforeach
                                    </select>
                                    <div class="driver_err d-none text-danger">{{ trans('lang.select_driver') }}</div>
                                </div>
                            </div>
                            <input type="hidden" id="vehicleId" name="vehicleId">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="assign-driver-btn">{{ trans('submit') }}</a>
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
        $("#checkoutSubscriptionModal").on('hide.bs.modal', function() {
            $("#plan-details").html('');
        });
        function chooseSubscriptionPlan(planId) {
            $("#changeSubscriptionModal").modal('hide');
            $("#checkoutSubscriptionModal").modal('show');
            showPlanDetail(planId);
        }
        $('#checkoutSubscriptionModal [data-dismiss="modal"]').on('click', function () {
            $('#checkoutSubscriptionModal').modal('hide');
        });
        async function showPlanDetail(planId) {
            $("#plan_id").val(planId);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('get-plan-detail') }}",
                method: "POST",
                data: {
                    'planId': planId,
                    'driverId': "{{ $owner->id }}"
                },
                success: function(response) {
                    var choosedPlan = response.planData;
                    var activePlan = '';
                    if (response.activePlan) {
                        activePlan = response.activePlan.subscription_plan;
                    }
                    const basePath = "{{ asset('assets/images/subscription') }}/";
                    let html = '';
                    let choosedPlan_price;
                    if (parseInt(choosedPlan.price) != 0) {
                        choosedPlan_price = "{{ $currency->symbol_at_right == 'true' }}" ? parseFloat(
                                choosedPlan.price).toFixed("{{ $currency->decimal_digit }}") +
                            "{{ $currency->symbole }}" :
                            "{{ $currency->symbole }}" + parseFloat(choosedPlan.price).toFixed(
                                "{{ $currency->decimal_digit }}");
                        $('.manual_pay_div').removeClass('d-none');
                    } else {
                        choosedPlan_price = 'Free';
                        $('.manual_pay_div').addClass('d-none');
                    }
                    if (activePlan) {
                        let activePlan_price;
                        if (parseInt(activePlan.price) != 0) {
                            activePlan_price = "{{ $currency->symbol_at_right == 'true' }}" ?
                                parseFloat(activePlan.price).toFixed("{{ $currency->decimal_digit }}") +
                                "{{ $currency->symbole }}" :
                                "{{ $currency->symbole }}" + parseFloat(activePlan.price).toFixed(
                                    "{{ $currency->decimal_digit }}");
                        } else {
                            activePlan_price = 'Free';
                        }
                        html += ` 
            <div class="col-md-8">
                <div class="subscription-card-left"> 
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${basePath}${activePlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${activePlan.id=="1"? "{{ trans('lang.commission') }}":activePlan.name}</h2>
                                </div>
                                <h3 class="text-dark-2">${activePlan.id=="1"? "{{ $adminCommission }}"+" {{ trans('lang.base_plan') }}":activePlan_price}</h3>
                                <p class="text-center">${activePlan.id=="1"? "Free":activePlan.expiryDay==-1? "{{ trans('lang.unlimited') }}":activePlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <img src="{{ asset('images/left-right-arrow.png') }}">
                        </div>
                        <div class="col-md-5">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${basePath}${choosedPlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${choosedPlan.name}
                                    </h2>
                                </div>
                                <h3 class="text-dark-2">${choosedPlan_price}</h3>
                                <p class="text-center">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="subscription-card-right">
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.validity') }}</span>
                        <span class="font-weight-semibold">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${choosedPlan_price}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                        <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                    </div>
                </div>
            </div>`;
                    } else {
                        html += ` 
            <div class="col-md-6">
                <div class="subscription-card-left"> 
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${basePath}${choosedPlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${choosedPlan.name}
                                    </h2>
                                </div>
                                <h3 class="text-dark-2">${choosedPlan_price}</h3>
                                <p class="text-center">${choosedPlan.id=="1"? "Free":choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="subscription-card-right">
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.validity') }}</span>
                        <span class="font-weight-semibold">${choosedPlan.id=="1"? "Unlimited":choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${choosedPlan_price}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                        <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                    </div>
                </div>
            </div>`;
                    }
                    $("#plan-details").html(html);
                },
                error: function(xhr) {
                }
            });
        }
        async function finalCheckout() {
            let planId = $("#plan_id").val();
            if (planId != undefined && planId != '' && planId != null) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('subscription-checkout') }}',
                    method: "POST",
                    data: {
                        'planId': planId,
                        'driverId': "{{ $owner->id }}"
                    },
                    success: function(response) {
                        window.location.reload();
                    }
                });
            }
        }
        $("#add-wallet-btn").click(function() {
            var amount = $('#amount').val();
            if (amount == '') {
                $('#wallet_error').text('{{ trans('lang.add_wallet_amount_error') }}');
                return false;
            }
        });
        $('input[name="set_booking_limit"]').on('change', function() {
            if ($('#limited_booking').is(':checked')) {
                $('.booking-limit-div').removeClass('d-none');
            } else {
                $('.booking-limit-div').addClass('d-none');
            }
        });
        $('input[name="set_vehicle_limit"]').on('change', function() {
            if ($('#limited_vehicle').is(':checked')) {
                $('.vehicle-limit-div').removeClass('d-none');
            } else {
                $('.vehicle-limit-div').addClass('d-none');
            }
        });
        $('input[name="set_driver_limit"]').on('change', function() {
            if ($('#limited_driver').is(':checked')) {
                $('.driver-limit-div').removeClass('d-none');
            } else {
                $('.driver-limit-div').addClass('d-none');
            }
        });
        $(document).on('submit', '#update-limit-form', function(event) {
            let bookingLimit = $('#booking_limit').val().trim();
            var set_booking_limit = $('input[name="set_booking_limit"]:checked').val();
            if (set_booking_limit == 'limited' && bookingLimit === '') {
                event.preventDefault();
                $('.limit_err').html('{{ trans('lang.enter_booking_limit') }}');
                return false;
            }
            let vehicleLimit = $('#vehicle_limit').val().trim();
            var set_vehicle_limit = $('input[name="set_vehicle_limit"]:checked').val();
            if (set_vehicle_limit == 'limited' && vehicleLimit === '') {
                event.preventDefault();
                $('.limit_err').html('{{ trans('lang.enter_vehicle_limit') }}');
                return false;
            }
            let driverLimit = $('#driver_limit').val().trim();
            var set_driver_limit = $('input[name="set_driver_limit"]:checked').val();
            if (set_driver_limit == 'limited' && driverLimit === '') {
                event.preventDefault();
                $('.limit_err').html('{{ trans('lang.enter_driver_limit') }}');
                return false;
            }
        });
        $('#addVehicleModal select[name="id_type_vehicule"]').on('change', function() {
            $('#addVehicleModal select[name="model"]').empty();
            $('#addVehicleModal select[name="model"]').append('<option value="">{{ trans('lang.select_model') }}</option>');
        });
        $('#editVehicleModal select[name="id_type_vehicule"]').on('change', function() {
            $('#editVehicleModal select[name="model"]').empty();
            $('#editVehicleModal select[name="model"]').append('<option value="">{{ trans('lang.select_model') }}</option>');
        });
        $('#addVehicleModal select[name="brand"]').on('change', function() {
            var brand_id = $(this).val();
            var id_type_vehicule = $('#addVehicleModal select[name="id_type_vehicule"]').val();
            var url = "{{ route('driver.model', ':brandId') }}";
            url = url.replace(':brandId', brand_id);
            if (brand_id) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        id_type_vehicule: id_type_vehicule,
                        _token: '{{ csrf_token() }}',
                    },
                    dataType: 'json',
                    success: function(data) {
                        $('#addVehicleModal select[name="model"]').empty();
                        $('#addVehicleModal select[name="model"]').append('<option value="">{{ trans('lang.select_model') }}</option>');
                        $.each(data.model, function(key, value) {
                            $('#addVehicleModal select[name="model"]').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                });
            } else {
                $('#addVehicleModal select[name="model"]').empty();
            }
        });
        $('#editVehicleModal select[name="brand"]').on('change', function() {
            var brand_id = $(this).val();
            var id_type_vehicule = $('#editVehicleModal  select[name="id_type_vehicule"]').val();
            var url = "{{ route('driver.model', ':brandId') }}";
            url = url.replace(':brandId', brand_id);
            if (brand_id) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        id_type_vehicule: id_type_vehicule,
                        _token: '{{ csrf_token() }}',
                    },
                    dataType: 'json',
                    success: function(data) {
                        $('#editVehicleModal select[name="model"]').empty();
                        $('#editVehicleModal select[name="model"]').append('<option value="">{{ trans('lang.select_model') }}</option>');
                        $.each(data.model, function(key, value) {
                            $('select[name="model"]').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                });
            } else {
                $('#editVehicleModal  select[name="model"]').empty();
            }
        });
        $(document).on("click", "a[name='assign-driver']", function(e) {
            var vehicleId = this.id;
            $('#vehicleId').val(vehicleId);
        });
        $('#add-vehicle-btn').on('click', function() {
            var vehicleType = $('#addVehicleModal select[name="id_type_vehicule"]').val();
            var brand = $('#addVehicleModal select[name="brand"]').val();
            var model = $('#addVehicleModal select[name="model"]').val();
            var km = $('#addVehicleModal input[name="km"]').val();
            var milage = $('#addVehicleModal input[name="milage"]').val();
            var carNumber = $('#addVehicleModal input[name="car_number"]').val();
            var color = $('#addVehicleModal input[name="color"]').val();
            var passenger = $('#addVehicleModal input[name="passenger"]').val();
            var regYear = $('#addVehicleModal input[name="registration_year"]').val();
            var driver =  $('#addVehicleModal select[name="driver"]').val();           
            if (driver == '') {
                $('.driver_err').removeClass('d-none');
                return false;
            } else if (vehicleType == '') {
                $('.vehicle_type_err').removeClass('d-none');
                return false;
            } else if (brand == '') {
                $('.brand_err').removeClass('d-none');
                return false;
            } else if (model == '') {
                $('.car_model_err').removeClass('d-none');
                return false;
            } else if (km == '') {
                $('.vehicle_km_err').removeClass('d-none');
                return false;
            } else if (milage == '') {
                $('.vehicle_milage_err').removeClass('d-none');
                return false;
            } else if (color == '') {
                $('.vehicle_color_err').removeClass('d-none');
                return false;
            } else if (passenger == '') {
                $('.passenger_err').removeClass('d-none');
                return false;
            } else if (carNumber == '') {
                $('.vehicle_number_err').removeClass('d-none');
                return false;
            }else if (regYear == '') {
                $('.registration_year_err').removeClass('d-none');
                return false;
            }
        });
        $('#assign-driver-btn').on('click', function() {
            var driver = $('#assign_driver').val();
            if (driver == '') {
                $('.driver_err').removeClass('d-none');
                return false;
            }
        })
        $(document).on('click', '[name="edit-vehicle"]', function() {
            let vehicleId = $(this).attr('id');
            $.ajax({
                url: '/owner-vehicle/edit/' + vehicleId,
                type: 'GET',
                success: function(data) {
                    $('#editVehicleModal select[name="id_type_vehicule"]').val(data.vehicle.id_type_vehicule);
                    $('#editVehicleModal select[name="brand"]').val(data.vehicle.brand);
                    $('#editVehicleModal input[name="km"]').val(data.vehicle.km);
                    $('#editVehicleModal input[name="milage"]').val(data.vehicle.milage);
                    $('#editVehicleModal input[name="car_number"]').val(data.vehicle.numberplate);
                    $('#editVehicleModal input[name="color"]').val(data.vehicle.color);
                    $('#editVehicleModal input[name="passenger"]').val(data.vehicle.passenger);
                    $('#editVehicleModal input[name="registration_year"]').val(data.vehicle.make_year);
                    let driverSelect = $('#editVehicleModal select[name="driver"]');
                    driverSelect.empty();
                    driverSelect.append('<option value="">-- Select Driver --</option>');
                    data.drivers.forEach(driver => {
                        let selected = (driver.id === data.currentDriverId) ? 'selected' : '';
                        driverSelect.append(`<option value="${driver.id}" ${selected}>${driver.prenom} ${driver.nom}</option>`);
                    });
                    let modelUrl = "{{ route('driver.model', ':brandId') }}".replace(':brandId', data.vehicle.brand);
                    $.ajax({
                        url: modelUrl,
                        type: "POST",
                        data: {
                            id_type_vehicule: data.vehicle.id_type_vehicule,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            let $modelSelect = $('#editVehicleModal select[name="model"]');
                            $modelSelect.empty().append('<option value="">{{ trans('lang.select_model') }}</option>');
                            $.each(response.model, function(index, model) {
                                $modelSelect.append('<option value="' + model.id + '">' + model.name + '</option>');
                            });
                            $modelSelect.val(data.vehicle.model);
                        }
                    });
                    // Update form action dynamically if needed
                    $('#editVehicleModal form').attr('action', '/owner-vehicle/update/' + vehicleId);
                }
            });
        });
        function checkVehicleLimit(e, vehicleCount, vehicleLimit) {
            e.preventDefault(); 
            e.stopPropagation(); 
            if (vehicleLimit !== null && vehicleCount >= vehicleLimit) {
                alert("{{ trans('lang.owner_vehicle_limit_exceed_error') }}");
                return false;
            }
            $('#addVehicleModal').modal('show');
        }
        $(document).on('click', '#addVehicleModal .close, #addVehicleModal [data-dismiss="modal"]', function() {
            $('#addVehicleModal').modal('hide');
        });
    </script>
@endsection
