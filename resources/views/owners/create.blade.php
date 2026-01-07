@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.owners_create') }}</h3>
            </div>

            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('owners.index') !!}">{{ trans('lang.owner_plural') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.owners_create') }}</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card pb-4">
                        <div class="card-body">
                            <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{ trans('lang.processing') }}</div>
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
                            <form action="{{ route('owners.store') }}" method="post" enctype="multipart/form-data" id="create_driver">
                                @csrf

                                <div class="row restaurant_payout_create">
                                    <div class="restaurant_payout_create-inner">
                                        <fieldset>
                                            <legend>{{ trans('lang.driver_details') }}</legend>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.first_name') }}</label>
                                                <div class="col-7">
                                                    <input type="hidden" class="form-control user_first_name" name="id">
                                                    <input type="text" class="form-control user_first_name" name="nom" value="{{ Request::old('nom') }}">
                                                    <div class="form-text text-muted">{{ trans('lang.first_name_help') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.last_name') }}</label>
                                                <div class="col-7">
                                                    <input type="text" class="form-control user_last_name" name="prenom" value="{{ Request::old('prenom') }}">
                                                    <div class="form-text text-muted">{{ trans('lang.last_name_help') }}</div>
                                                </div>
                                            </div>

                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.email') }}</label>
                                                <div class="col-7">
                                                    <input type="email" class="form-control user_email" name="email" value="{{ Request::old('email') }}">
                                                    <div class="form-text text-muted">{{ trans('lang.user_email_help') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.password') }}</label>
                                                <div class="col-7">
                                                    <input type="password" class="form-control user_password" name="password" value="{{ Request::old('password') }}">
                                                    <div class="form-text text-muted">{{ trans('lang.user_password_help') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.user_phone') }}</label>
                                                <div class="col-7">
                                                    <div class="phone-box position-relative">
                                                        <span class="country_flag"><img id="flag_icon" src="{{ asset('images/af.png') }}" class="flag-icon"></span>
                                                        <select name="country_code" class="form-control" id="country_code">
                                                            @foreach($countries as $country)
                                                                <option value="{{ $country->phone }}" data-code="{{ strtolower($country->code) }}"
                                                                    {{ old('country_code') == $country->phone ? 'selected' : '' }}>
                                                                    {{ $country->libelle }} (+{{ $country->phone }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="text" class="form-control user_phone" name="phone" value="{{ Request::old('phone')}}">
                                                    </div>
                                                    <div class="form-text text-muted">
                                                        {{ trans('lang.user_phone_help') }}</div>
                                                </div>
                                            </div>
                                          

                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.profile_image') }}</label>
                                                <div class="col-7">
                                                    <input type="file" class="" name="photo" value="{{ Request::old('photo') }}" onchange="readURL(this);">
                                                    <div class="form-text text-muted">{{ trans('lang.profile_image_help') }}
                                                    </div>
                                                </div>
                                                <div id="image_preview" style="display: none; padding-left: 15px;">
                                                    <img class="rounded" style="width:50px" id="uploding_image" src="#" alt="image">
                                                </div>
                                            </div>

                                            <div class="form-group row width-50">
                                                <div class="multi-select">	
                                                    <label class="col-3 control-label">{{ trans('lang.select_service_type') }}</label>
                                                    <div class="col-7">
                                                        <select class="form-control" name="service_type[]" id="service_type" multiple="multiple">
                                                            <option value="ride" {{ old('service_type') == 'ride' ? 'selected' : '' }}>{{trans('lang.ride')}}</option>
                                                            <option value="parcel" {{ old('service_type') == 'parcel' ? 'selected' : '' }}>{{trans('lang.parcel')}}</option>
                                                            <option value="rental" {{ old('service_type') == 'rental' ? 'selected' : '' }}>{{trans('lang.rental')}}</option>
                                                        </select>
                                                        <div class="form-text text-muted w-50">
                                                            {{ trans('lang.select_service_type_help') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check  width-50">
                                                <input type="checkbox" class="col-7 form-check-inline user_active" id="user_active" name="statut" value="yes">
                                                <label class="col-3 control-label" for="user_active">{{ trans('lang.active') }}</label>
                                            </div>
                                            
                                        </fieldset>

                                        <fieldset>
                                            <legend>{{ trans('lang.bank_details') }}</legend>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.bank_name') }}</label>
                                                <div class="col-7">
                                                    <input type="text" name="bank_name" class="form-control" id="bankName">

                                                </div>
                                            </div>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.branch_name') }}</label>
                                                <div class="col-7">
                                                    <input type="text" name="branch_name" class="form-control" id="branchName">

                                                </div>

                                            </div>

                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.holder_name') }}</label>
                                                <div class="col-7">
                                                    <input type="text" name="holder_name" class="form-control" id="holderName">

                                                </div>
                                            </div>

                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.account_number') }}</label>
                                                <div class="col-7">
                                                    <input type="text" name="account_number" class="form-control" id="accountNumber">

                                                </div>
                                            </div>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.ifsc_code') }}</label>
                                                <div class="col-7">
                                                    <input type="text" class="form-control user_phone" name="ifsc_code">

                                                </div>

                                            </div>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.other_information') }}</label>
                                                <div class="col-7">
                                                    <input type="text" name="other_information" class="form-control" id="otherDetails">

                                                </div>
                                            </div>

                                        </fieldset>

                                        <div class="form-group col-12 text-center btm-btn">
                                            <button type="submit" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
                                            <a href="{!! route('owners.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                                        </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
    
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

        function readURLNic(input) {
            console.log(input.files);
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#placeholder_img_thumb').show();
                    $('#user_nic_image').attr('src', e.target.result);
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

        $(document).on("change", "#country_code", function() {
            let code = $("#country_code option:selected").data('code');
            $("#flag_icon").attr("src", `https://flagcdn.com/w40/${code}.png`)
        });

        $('#service_type').select2({
            placeholder: "{{trans('lang.select_service_type')}}",
            allowClear: true
        });
    </script>
@endsection
