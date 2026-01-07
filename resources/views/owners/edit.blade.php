@extends('layouts.app')

@section('content')

    <div class="page-wrapper">

        <div class="row page-titles">

            <div class="col-md-5 align-self-center">

                <h3 class="text-themecolor">{{ trans('lang.owner_edit') }}</h3>

            </div>

            <div class="col-md-7 align-self-center">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>

                    <li class="breadcrumb-item"><a href="{!! route('owners.index') !!}">{{ trans('lang.owner_plural') }}</a></li>

                    <li class="breadcrumb-item active">{{ trans('lang.owner_edit') }}</li>

                </ol>

            </div>

        </div>

        <div class="container-fluid">

            <div class="card pb-4">

                <div class="card-body">

                    <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">

                        {{ trans('lang.processing') }}
                    </div>

                    <div class="error_top"></div>

                    @if ($errors->any())
                        <div class="alert alert-danger">

                            <ul>

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>
                    @endif

                    <form method="post" action="{{ route('owners.update', $owner->id) }}" enctype="multipart/form-data">

                        @csrf

                        @method('PUT')

                        <div class="row restaurant_payout_create">

                            <div class="restaurant_payout_create-inner">

                                <fieldset>

                                    <legend>{{ trans('lang.owner_edit') }}</legend>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.first_name') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_first_name" name="nom" value="{{ $owner->nom }}">

                                            <div class="form-text text-muted">

                                                {{ trans('lang.user_first_name_help') }}

                                            </div>

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.last_name') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_last_name" name="prenom" value="{{ $owner->prenom }}">

                                            <div class="form-text text-muted">

                                                {{ trans('lang.user_last_name_help') }}

                                            </div>

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.email') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_email" name="email" value="{{ $owner->email }}" readonly>

                                            <div class="form-text text-muted">

                                                {{ trans('lang.user_email_help') }}

                                            </div>

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.user_phone') }}</label>

                                        <div class="col-7">

                                            <div class="phone-box position-relative">
                                                <span class="country_flag">
                                                    <img id="flag_icon" src="https://flagcdn.com/w40/{{ strtolower($countries->firstWhere('phone', old('country_code', ltrim($owner->country_code, '+')))->code ?? 'in') }}.png" class="flag-icon">
                                                </span>
                                                <select name="country_code" class="form-control" id="country_code" readonly>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->phone }}" data-code="{{ strtolower($country->code) }}"
                                                        {{ old('country_code', ltrim($owner->country_code, '+')) == $country->phone ? 'selected' : '' }}>
                                                        {{ $country->libelle }} (+{{ $country->phone }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="text" class="form-control user_phone" name="phone" value="{{$owner->phone}}" readonly>
                                            </div>

                                            <div class="form-text text-muted w-50">

                                                {{ trans('lang.user_phone_help') }}

                                            </div>

                                        </div>

                                    </div>


                                    <div class="form-group row width-100">

                                        <label class="col-2 control-label">{{ trans('lang.restaurant_image') }}</label>

                                        <input type="file" class="col-6" name="photo" onchange="readURL(this);">

                                        @if (file_exists(public_path('assets/images/driver' . '/' . $owner->photo_path)) && !empty($owner->photo_path))
                                            <td><img class="rounded" id="uploding_image" style="width:100px" src="{{ asset('assets/images/driver') . '/' . $owner->photo_path }}" alt="image"></td>
                                        @else
                                            <td><img class="rounded" id="uploding_image" style="width:100px" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image"></td>
                                        @endif

                                    </div>

                                    <div class="form-group row width-50">

                                        <div class="form-check">

                                            @if ($owner->statut === 'yes')
                                                <input type="checkbox" class="user_active" name="statut" id="user_active" checked="checked">
                                            @else
                                                <input type="checkbox" class="user_active" name="statut" id="user_active">
                                            @endif

                                            <label class="col-3 control-label" for="user_active">{{ trans('lang.status') }}</label>

                                        </div>

                                    </div>

                                </fieldset>
                                @if (!empty($owner->subscriptionPlanId))
                                    <fieldset>
                                        <legend>{{ trans('lang.subscription_model') }}</legend>

                                        <div class="form-group row width-100">
                                            <label class="col-4 control-label">{{ trans('lang.change_expiry_date') }}</label>
                                            <div class="col-7">
                                                <input type="date" name="change_expiry_date" class="form-control" id="change_expiry_date" value="{{ !empty($owner->subscriptionExpiryDate) ? \Carbon\Carbon::parse($owner->subscriptionExpiryDate)->format('Y-m-d') : '' }}">
                                            </div>
                                        </div>

                                    </fieldset>
                                @endif

                                <fieldset>

                                    <legend>{{ trans('lang.admin_commission_settings') }}</legend>
                                    <div class="form-group row width-50 admin_commision_detail ">

                                        <label class="col-3 control-label">{{ trans('lang.commission_type') }}</label>
                                        <select name="commission_type" class="form-control">
                                            <option value="Percentage" {{ !empty($owner->adminCommission) ? ($owner->adminCommission['type'] == 'Percentage' ? 'selected' : '') : '' }}>{{trans('lang.percentage')}}</option>
                                            <option value="Fixed" {{ !empty($owner->adminCommission) ? ($owner->adminCommission['type'] == 'Fixed' ? 'selected' : '') : '' }}>{{trans('lang.fix')}}</option>
                                        </select>
                                    </div>

                                    <div class="form-group row width-50 admin_commision_detail ">
                                        <label class="col-3 control-label">{{ trans('lang.dashboard_total_admin_commission') }}</label>
                                        <input type="text" class="form-control code" name="commission_value" value="{{ !empty($owner->adminCommission) ? $owner->adminCommission['value'] : '' }}">
                                        <div class="form-text text-muted w-50">

                                            {{ trans('lang.insert_commission_value') }}

                                        </div>
                                    </div>

                                </fieldset>


                                <fieldset>

                                    <legend>{{ trans('lang.driver_bank_details') }}</legend>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.bank_name') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control address_line1" name="bank_name" value="{{ $owner->bank_name }}">

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.branch_name') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_first_name" name="branch_name" value="{{ $owner->branch_name }}">

                                            {{-- <div class="form-text text-muted">

                      {{ trans("lang.user_first_name_help") }}

                  </div> --}}

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.holder_name') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_last_name" name="holder_name" value="{{ $owner->holder_name }}">

                                            {{-- <div class="form-text text-muted">

                      {{ trans("lang.user_last_name_help") }}

              </div> --}}

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.account_no') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_email" name="account_no" value="{{ $owner->account_no }}">

                                            {{-- <div class="form-text text-muted">

                      {{ trans("lang.user_email_help") }}

            </div> --}}

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.Other_info') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_phone" name="other_info" value="{{ $owner->other_info }}">

                                            {{-- <div class="form-text text-muted w-50">

                      {{ trans("lang.user_phone_help") }}

        </div> --}}

                                        </div>

                                    </div>

                                    <div class="form-group row width-50">

                                        <label class="col-3 control-label">{{ trans('lang.ifsc_code') }}</label>

                                        <div class="col-7">

                                            <input type="text" class="form-control user_phone" name="ifsc_code" value="{{ $owner->ifsc_code }}">

                                        </div>

                                    </div>

                                </fieldset>

                            </div>

                        </div>

                </div>

                <div class="form-group col-12 text-center btm-btn">

                    <button type="submit" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>

                    <a href="{!! route('owners.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>

                </div>

            </div>

            </form>

        </div>

    </div>

@endsection

@section('scripts')
    <script>
    

        function readURL(input) {

            console.log(input.files);

            if (input.files && input.files[0]) {

                var reader = new FileReader();



                reader.onload = function(e) {

                    //	$('#image_preview').show();

                    $('#uploding_image').attr('src', e.target.result);





                }



                reader.readAsDataURL(input.files[0]);

            }



        }
        $('#country_code').select2({
            templateSelection: function (data, container) {
                if (data.id) {
                    return '+'+data.id;
                }
                return data.text;
            }
        });

     
    </script>
@endsection
