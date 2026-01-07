@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.dispatcher_user_edit')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('dispatcher-users.index') !!}">{{trans('lang.dispatcher_user')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.dispatcher_user_edit')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card pb-4">
            <div class="card-body">
                <div id="data-table_processing" class="dataTables_processing panel panel-default"
                     style="display: none;">{{trans('lang.processing')}}
                </div>
                <div class="error_top"></div>
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form method="post" action="{{ route('dispatcher-users.update',$user->id) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method("PUT")
                    <div class="row restaurant_payout_create">
                        <div class="restaurant_payout_create-inner">
                            <fieldset>
                                <legend>{{trans('lang.dispatcher_user_edit')}}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_first_name" name="first_name"
                                               value="{{$user->first_name}}">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_first_name_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_last_name" name="last_name"
                                               value="{{$user->last_name}}">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_last_name_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_email" name="email"
                                               value="{{$user->email}}" disabled>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_email_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                    <div class="col-7">
                                        <div class="phone-box position-relative">
                                            <span class="country_flag"><img id="flag_icon" src="{{ asset('images/af.png') }}" class="flag-icon"></span>
											<select name="country_code" class="form-control" id="country_code" disabled>
												@foreach($countries as $country)
													<option value="{{ $country->phone }}" data-code="{{ strtolower($country->code) }}"
													{{ old('country_code', ltrim($user->country_code, '+')) == $country->phone ? 'selected' : '' }}>
													{{ $country->libelle }} (+{{ $country->phone }})
													</option>
												@endforeach
											</select>
											<input type="text" class="form-control user_phone" name="phone" value="{{$user->phone}}" disabled>
										</div>
                                        <div class="form-text text-muted w-50">
                                            {{ trans("lang.user_phone_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-2 control-label">{{trans('lang.profile_image')}}</label>
                                    <input type="file" class="col-6 photo" name="profile_picture" onchange="readURL(this);">
                                    @php
                                    $relativePath = str_replace(url('/') . '/', '', $user->profile_picture_path);
                                    $imagePath = !empty($user->profile_picture_path) && file_exists(public_path($relativePath)) 
                                                ? $user->profile_picture_path 
                                                : asset('assets/images/placeholder_image.jpg');
                                    @endphp
                                    <img class="rounded" id="uploding_image" style="width:50px" src="{{$imagePath}}" alt="image">
                                </div>
                                <div class="form-group row width-50">
                                    <div class="form-check">
                                        @if ($user->status === "yes")
                                        <input type="checkbox" class="user_active" name="status" id="user_active"
                                               checked="checked" value="yes"/>
                                        @else
                                        <input type="checkbox" class="user_active" name="status" id="user_active"
                                               value="no"/>
                                        @endif
                                        <label class="col-3 control-label"
                                               for="user_active">{{trans('lang.active')}}</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{
                            trans('lang.save')}}
                        </button>
                        <a href="{!! route('dispatcher-users.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                            trans('lang.cancel')}}</a>
                    </div>
            </div>
        </div>
    </div>
    </form>
</div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#uploding_image').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    function readURLNic(input) {
        console.log(input.files);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
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
</script>
@endsection
