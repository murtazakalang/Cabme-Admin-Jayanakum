@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.edit_subscription_plan') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ url('subscription-plans') }}">{{ trans('lang.subscription_plans') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ trans('lang.edit_subscription_plan') }}</li>
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
            <form action="{{ route('subscription-plans.update',$subscriptionPlan->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.plan_details') }}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.plan_name') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="plan_name" name="planName"
                                        placeholder="{{ trans('lang.enter_plan_name') }}" value="{{$subscriptionPlan->name}}">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label" for="">{{ trans('lang.plan_type') }}</label>
                                <div class="form-check width-50">
                                    <input type="radio" id="free_type" name="planType" value="free" {{$subscriptionPlan->type=='free' ? 'checked' : ''}}>
                                    <label class="control-label" for="free_type">{{ trans('lang.free') }}</label>
                                </div>
                                <div class="form-check width-50 paid-plan-div">
                                    <input type="radio" id="paid_type" name="planType" value="paid" {{$subscriptionPlan->type=='paid' ? 'checked' : ''}}>
                                    <label class="control-label" for="paid_type">{{ trans('lang.paid') }}</label>
                                </div>
                            </div>
                            <div class="form-group row width-100 d-none plan_price_div">
                                <label class="col-3 control-label">{{ trans('lang.plan_price') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="plan_price" name="planPrice" value="{{$subscriptionPlan->price}}"
                                        placeholder="{{ trans('lang.enter_plan_price') }}">
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.plan_validity_days') }}</label>
                                <div class="form-check width-100">
                                    <input type="radio" id="unlimited_days" name="plan_validity_days" value="unlimited"
                                        {{$subscriptionPlan->expiryDay=='-1' ? 'checked' : ''}}>
                                    <label class="control-label" for="unlimited_days">{{ trans('lang.unlimited') }}</label>
                                </div>
                                <div class="d-flex">
                                    <div class="form-check width-50 limited_days_div">
                                        <input type="radio" id="limited_days" name="plan_validity_days" value="limited" {{$subscriptionPlan->expiryDay!='-1' ? 'checked' : ''}}>
                                        <label class="control-label" for="limited_days">{{ trans('lang.limited') }}</label>
                                    </div>
                                    <div class="form-check width-50 d-none expiry-limit-div">
                                        <input type="number" id="plan_validity" class="form-control" name="plan_validity"
                                            placeholder="{{ trans('lang.ex_365') }}" value="{{$subscriptionPlan->expiryDay}}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.description') }}</label>
                                <div class="col-7">
                                    <textarea class="form-control" id="description" name="description" rows="5">{{$subscriptionPlan->description}}</textarea>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.order') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="order" name="order"
                                        placeholder="{{ trans('lang.enter_display_order') }}" value="{{$subscriptionPlan->place}}">
                                </div>
                            </div>

                            <div class="form-group row width-100 status-div">
                                <div class="form-check width-100">
                                    <input type="checkbox" id="status" name="status" {{$subscriptionPlan->isEnable=='true' ? 'checked' : ''}}>
                                    <label class="control-label" for="status">{{ trans('lang.status') }}</label>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.image') }}</label>
                                <div class="col-7">
                                    <input type="file" class="form-control" name="image" onchange="readURL(this);">
                                    @if (file_exists(public_path('assets/images/subscription'.'/'.$subscriptionPlan->image)) && !empty($subscriptionPlan->image))
                                    <img class="rounded" style="width:50px" id="uploding_image" src="{{asset('assets/images/subscription').'/'.$subscriptionPlan->image}}" alt="image">
                                    @else
                                    <img class="rounded" style="width:50px" id="uploding_image" src="{{asset('assets/images/placeholder_image.jpg')}}" alt="image">
                                    @endif

                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="commissionPlan-features-div">
                            <legend>{{ trans('lang.plan_points') }}</legend>
                            <div class="form-group row options-div ">
                                <div id="options-container">
                                    @if(! empty($subscriptionPlan->plan_points))
                                    @php $i = 0;
                                    $optionLength = count($subscriptionPlan->plan_points) - 1;
                                    @endphp
                                    @foreach($subscriptionPlan->plan_points as $key=>$value)

                                    <div class="row d-flex ml-1 mt-3 option-row">
                                        <div class="col-10">
                                            <input type="text" class="form-control plan_points"
                                                name="plan_points[]" id="plan_points" value="{{$value}}">
                                        </div>

                                        <div class="col-2 d-flex">
                                            @if($optionLength == $key)
                                            <button class="btn btn-success" type="button"
                                                onclick=addRow()><span class="fa fa-plus"></span></button>
                                            @endif
                                            
                                            @if($key!=0)
                                            <button class="btn btn-danger ml-2" type="button"
                                                onclick=deleteRow(this)><span
                                                    class="fa fa-trash"></span></button>
                                            @endif

                                        </div>
                                    </div>
                                    @php $i++; @endphp
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>{{ trans('lang.maximum_booking_limit') }}</legend>
                            <div class="form-group row width-100">
                                <div class="form-check width-100">
                                    <input type="radio" id="unlimited_booking" name="set_booking_limit" value="unlimited"
                                        {{$subscriptionPlan->bookingLimit=='-1' ? 'checked' : ''}}>
                                    <label class="control-label"
                                        for="unlimited_booking">{{ trans('lang.unlimited') }}</label>
                                </div>
                                <div class="d-flex ">
                                    <div class="form-check width-50 limited_booking_div  ">
                                        <input type="radio" id="limited_booking" name="set_booking_limit" value="limited" {{$subscriptionPlan->bookingLimit!='-1' ? 'checked' : ''}}>
                                        <label class="control-label"
                                            for="limited_booking">{{ trans('lang.limited') }}</label>
                                    </div>
                                    <div class="form-check width-50 d-none booking-limit-div">
                                        <input type="number" id="booking_limit" class="form-control" name="booking_limit"
                                            placeholder="{{ trans('lang.ex_1000') }}" value="{{$subscriptionPlan->bookingLimit}}">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        @if($subscriptionPlan->id != 1)
                        <fieldset>
                            <legend>{{ trans('lang.plan_for') }}</legend>
                            <div class="form-group row width-100">
                                <div class="form-check width-100">
                                    <input type="radio" id="driver" name="plan_for" value="driver" {{$subscriptionPlan->plan_for=='driver' ? 'checked' : ''}}>
                                    <label class="control-label" for="driver">{{ trans('lang.individual_driver') }}</label>
                                </div>
                                <div class="form-check width-100">
                                    <input type="radio" id="owner" name="plan_for" value="owner" {{$subscriptionPlan->plan_for == 'owner' ? 'checked' : ''}}>
                                    <label class="control-label" for="owner">{{ trans('lang.owner') }}</label>
                                </div>
                                <div class="form-check width-50 owner-field {{$subscriptionPlan->plan_for == 'owner' ? '' : 'd-none'}}">
                                    <label class="control-label" for="driver">{{ trans('lang.max_vehicle_create_limit') }}</label>
                                    <input type="number" id="vehicle_limit" class="form-control" name="vehicle_limit" placeholder="{{ trans('lang.ex_1000_and_minus_one') }}" value="{{$subscriptionPlan->vehicle_limit}}">
                                </div>
                                <div class="form-check width-50 owner-field {{$subscriptionPlan->plan_for == 'owner' ? '' : 'd-none'}}">
                                    <label class="control-label" for="driver">{{ trans('lang.max_driver_create_limit') }}</label>
                                    <input type="number" id="driver_limit" class="form-control" name="driver_limit" placeholder="{{ trans('lang.ex_1000_and_minus_one') }}" value="{{$subscriptionPlan->driver_limit}}">
                                </div>
                                <div class="form-group row width-50 mt-2 owner-field {{$subscriptionPlan->plan_for == 'owner' ? '' : 'd-none'}}">
                                    <label class="col-3 control-label" for="">{{ trans('lang.dispatcher_panel_access') }}</label>
                                    <div class="form-check width-50">
                                        <input type="radio" id="dispatcher_access_yes" name="dispatcher_access" value="yes" {{$subscriptionPlan->dispatcher_access == 'yes' ? 'checked' : ''}}>
                                        <label class="control-label" for="dispatcher_access_yes">{{ trans('lang.yes') }}</label>
                                    </div>
                                    <div class="form-check width-50">
                                        <input type="radio" id="dispatcher_access_no" name="dispatcher_access" value="no" {{$subscriptionPlan->dispatcher_access == 'no' ? 'checked' : ''}}>
                                        <label class="control-label" for="dispatcher_access_no">{{ trans('lang.no') }}</label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        @endif
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                        {{ trans('lang.save') }}
                    </button>
                    <a href="{{ url('subscription-plans') }}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(async function() {

        $('input[name="plan_validity_days"]').on('change', function() {
            if ($('#limited_days').is(':checked')) {
                $('.expiry-limit-div').removeClass('d-none');
            } else {
                $('.expiry-limit-div').addClass('d-none');
            }
        });
        $('input[name="set_booking_limit"]').on('change', function() {
            if ($('#limited_booking').is(':checked')) {
                $('.booking-limit-div').removeClass('d-none');
            } else {
                $('.booking-limit-div').addClass('d-none');
            }
        });
        $('input[name="planType"]').on('change', function() {
            if ($('input[name="planType"]:checked').val() == 'free') {
                $('.plan_price_div').addClass('d-none');
                $("#plan_price").val(0);
            } else {
                $('.plan_price_div').removeClass('d-none');
            }
        });
        $('input[name="plan_for"]').on('change', function() {
            if ($('input[name="plan_for"]:checked').val() == 'owner') {
                $('.owner-field').removeClass('d-none');
            } else {
                $('.owner-field').addClass('d-none');
            }
        });
        
    });
    let optionIndex = 1;

    function addRow() {
        const container = document.getElementById('options-container');
        const newRow = document.createElement('div');
        newRow.className = 'row d-flex ml-1 option-row mt-3';
        newRow.innerHTML = `
        <div class="col-10">
            <input type="text" class="form-control plan_points" name="plan_points[]">
        </div>
        <div class="col-2 d-flex">
            <button class="btn btn-success" type="button" onclick="addRow()"><span class="fa fa-plus"></span></button>
            <button class="btn btn-danger ml-2" type="button" onclick="deleteRow(this)"><span class="fa fa-trash"></span></button>
        </div>`;
        container.appendChild(newRow);
        optionIndex++;
    }

    function deleteRow(button) {
        const row = button.closest('.option-row');
        row.remove();
    }

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#image_preview').show();
                    $('#uploding_image').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        var id = "{{$subscriptionPlan->id}}";
        if (parseInt(id) == 1) {
            $('#free_type').prop('checked', true);
            $('.limited_booking_div').addClass('d-none');

            $('.limited_days_div').addClass('d-none');
            $('.status-div').addClass('d-none');
            $('.paid-plan-div').addClass('d-none');
            $('#plan_price,#plan_validity,#order').attr('readonly', true);
        }
        if ("{{$subscriptionPlan->type}}" == 'paid') {
            $('.plan_price_div').removeClass('d-none');
        } else {
            $('.plan_price_div').addClass('d-none');
        }
        if ("{{$subscriptionPlan->bookingLimit}}" != '-1') {
            $('.booking-limit-div').removeClass('d-none');
        }
        if ("{{$subscriptionPlan->expiryDay}}" != '-1') {
            $('.expiry-limit-div').removeClass('d-none');
        }
</script>
@endsection