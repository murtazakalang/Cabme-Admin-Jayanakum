@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor plan_title">{{ trans('lang.current_subscriber_list_of') }}
                {{ $subscriptionPlan->name }}
            </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ url('/subscription-plans') }}">{{ trans('lang.subscription_plans') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.current_subscriber_list') }} </li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                        class="fa fa-list mr-2"></i>{{ trans('lang.current_subscriber_list') }}</a>
                            </li>
                        </ul>
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
                                        <form action="{{ route('current-subscriber.list',$subscriptionPlan->id) }}" method="get">
                                            <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                <option value="driver" {{ request('selected_search') == 'driver' ? 'selected' : '' }}>
                                                    {{ trans('lang.driver') }}
                                                </option>
                                                <option value="planName" {{ request('selected_search') == 'planName' ? 'selected' : '' }}>
                                                    {{ trans('lang.plan_name') }}
                                                </option>
                                                <option value="planType" {{ request('selected_search') == 'planType' ? 'selected' : '' }}>
                                                    {{ trans('lang.plan_type') }}
                                                </option>
                                            </select>
                                            <div class="search-box position-relative">
                                                <input type="text" class="search form-control" name="search" id="search"
                                                    value="{{ request('search') }}">
                                                <button type="submit" class="btn-flat position-absolute">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                                <a class="btn btn-warning btn-flat"
                                                href="{{ route('current-subscriber.list', $subscriptionPlan->id) }}">
                                                    {{ trans('lang.clear') }}
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive m-t-10">
                            <table id="subscriptionHistoryTable"
                                class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('lang.driver_or_owner') }}</th>
                                        <th>{{ trans('lang.plan_name') }}</th>
                                        <th>{{ trans('lang.plan_type') }}</th>
                                        <th>{{ trans('lang.booking_limit') }}</th>
                                        @if(isset($subscriptionPlan) && $subscriptionPlan->plan_for == 'owner')
                                        <th>{{ trans('lang.driver_limit') }}</th>
                                        <th>{{ trans('lang.vehicle_limit') }}</th>
                                        @endif
                                        <th>{{ trans('lang.plan_expires_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($currentSubscribers) > 0)
                                    @foreach ($currentSubscribers as $value)
                                    <tr>                                        
                                        <td>
                                            @if($value->role == 'owner' && !empty($value->id))       
                                                <a href="{{ route('owners.show', ['id' => $value->id]) }}">                                     
                                                    {{ $value->prenom }} {{ $value->nom }}
                                                </a>
                                            @elseif(!empty($value->id))
                                                <a href="{{ route('driver.show', ['id' => $value->id]) }}">  
                                                    {{ $value->prenom }} {{ $value->nom }}
                                                </a>
                                            @else
                                                {{ $value->prenom }} {{ $value->nom }}
                                            @endif
                                        </td>
                                        <td>{{ $value->subscription_plan['name'] }}</td>
                                        <td>{{ $value->subscription_plan['type'] }}</td>
                                        <td>
                                            <span>{{ trans('lang.total') }} :
                                                {{ $value->subscription_plan['bookingLimit'] == '-1' ? trans('lang.unlimited') : $value->subscription_plan['bookingLimit'] }}</span><br>
                                            <span>{{ trans('lang.available') }} :
                                                {{ $value->subscriptionTotalOrders == '-1' ? trans('lang.unlimited') : $value->subscriptionTotalOrders }}</span>
                                        </td>
                                        @if(isset($subscriptionPlan) && $subscriptionPlan->plan_for == 'owner')
                                        <td>
                                            <span>{{ trans('lang.total') }} :
                                                {{ $value->subscription_plan['driver_limit'] == '-1' ? trans('lang.unlimited') : $value->subscription_plan['driver_limit'] }}</span><br>
                                            <span>{{ trans('lang.available') }} : {{ $value->subscriptionTotalDriver  }}
                                                </span>
                                        </td>
                                        <td> <span>{{ trans('lang.total') }} :
                                                {{ $value->subscription_plan['vehicle_limit'] == '-1' ? trans('lang.unlimited') : $value->subscription_plan['vehicle_limit'] }}</span><br>
                                            <span>{{ trans('lang.available') }} : {{ $value->subscriptionTotalVehicle  }}
                                                </span>
                                        </td>
                                        @endif
                                        <td>
                                            @if ($value->subscriptionExpiryDate == null)
                                            {{ trans('lang.unlimited') }}
                                            @else
                                            <span
                                                class="date">{{ date('d F Y', strtotime($value->subscriptionExpiryDate)) }}</span>
                                            <span
                                                class="time">{{ date('h:i A', strtotime($value->subscriptionExpiryDate)) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    {{trans('lang.showing')}} {{ $currentSubscribers->firstItem() }} {{trans('lang.to_small')}} {{ $currentSubscribers->lastItem() }} {{trans('lang.of')}} {{ $currentSubscribers->total() }} {{trans('lang.entries')}}
                                </div>
                                <div>
                                    {{ $currentSubscribers->links('pagination.pagination') }}
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
<script></script>
@endsection