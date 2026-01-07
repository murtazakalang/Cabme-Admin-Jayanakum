@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.edit_rental_package') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ url('rental-packages') }}">{{ trans('lang.rental_packages') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ trans('lang.edit_rental_package') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="success_top" style="display:none"></div>
            @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form action="{{ route('rental-packages.update',$package->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.package_details') }}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.package_name') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="title" name="title" value="{{ $package->title }}">
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_name') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.vehicle_type') }}</label>
                                <div class="col-7">
                                    <select class="form-control model" name="vehicleTypeId" id="vehicleTypeId">
                                        <option value="">{{ trans('lang.select_type') }}</option>
                                        @foreach ($vehicleType as $value)
                                            <option value="{{ $value->id }}" {{ $package->vehicleTypeId == $value->id ? 'selected="selected"' : '' }}>{{ $value->libelle }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.enter_vehicle_type') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.package_description') }}</label>
                                <div class="col-7">
                                    <textarea class="form-control" id="description" name="description" rows="5">{{ $package->description }}</textarea>
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_description') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.package_basefare_price') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="baseFare" name="baseFare" value="{{ $package->baseFare }}">
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_basefare_price') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.package_included_hours') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="includedHours" name="includedHours" value="{{ $package->includedHours }}">
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_included_hours') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.package_included_distance') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="includedDistance" name="includedDistance" value="{{ $package->includedDistance }}">
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_included_distance') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.package_extra_km_fare') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="extraKmFare" name="extraKmFare" value="{{ $package->extraKmFare }}">
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_extra_km_fare') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.package_extra_minute_fare') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="extraMinuteFare" name="extraMinuteFare" value="{{ $package->extraMinuteFare }}">
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_extra_minute_fare') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.package_ordering') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="ordering" name="ordering" value="{{ $package->ordering }}">
                                    <div class="form-text text-muted">{{ trans('lang.enter_package_ordering') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <div class="form-check width-100">
                                    <input type="checkbox" id="published" name="published" {{ $package->published == "true" ? 'checked' : "" }}>
                                    <label class="control-label" for="published">{{ trans('lang.published') }}</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                        {{ trans('lang.save') }}
                    </button>
                    <a href="{{ url('rental-packages') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection

@section('scripts')

@endsection