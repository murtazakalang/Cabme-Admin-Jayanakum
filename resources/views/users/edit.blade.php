@extends('layouts.app')


@section('content')
<div class="page-wrapper">
	<div class="row page-titles">
		<div class="col-md-5 align-self-center">
			<h3 class="text-themecolor">{{trans('lang.user_edit')}}</h3>
		</div>
		<div class="col-md-7 align-self-center">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
				<li class="breadcrumb-item"><a href= "{!! route('users.index') !!}" >{{trans('lang.user_plural')}}</a></li>
				<li class="breadcrumb-item active">{{trans('lang.user_edit')}}</li>
			</ol>
		</div>
	</div>
	<div class="container-fluid">

		<div class="card pb-4">
			<div class="card-body">
				<div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{trans('lang.processing')}}</div>
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
				<form method="post" action="{{ route('users.update',$user->id) }}" enctype="multipart/form-data">
					@csrf
					@method("PUT")
					<div class="row restaurant_payout_create">
						<div class="restaurant_payout_create-inner">
							<fieldset>
								<legend>{{trans('lang.user_edit')}}</legend>
								<div class="form-group row width-50">
									<label class="col-3 control-label">{{trans('lang.first_name')}}</label>
									<div class="col-7">
										<input type="text" class="form-control user_first_name" name="prenom" value="{{$user->prenom}}">
										<div class="form-text text-muted">
											{{ trans("lang.user_first_name_help") }}
										</div>
									</div>
								</div>

								<div class="form-group row width-50">
									<label class="col-3 control-label">{{trans('lang.last_name')}}</label>
									<div class="col-7">
										<input type="text" class="form-control user_last_name" name="nom" value="{{$user->nom}}">
										<div class="form-text text-muted">
											{{ trans("lang.user_last_name_help") }}
										</div>
									</div>
								</div>
								<div class="form-group row width-50">
									<label class="col-3 control-label">{{trans('lang.email')}}</label>
									<div class="col-7">
										<input type="text" class="form-control user_email"  name="email" value="{{$user->email}}" readonly>
										<div class="form-text text-muted">
											{{ trans("lang.user_email_help") }}
										</div>
									</div>
								</div>

								<div class="form-group row width-50">
									<label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
									<div class="col-7">
										
										<div class="phone-box position-relative">
											<span class="country_flag">
												<img id="flag_icon" src="https://flagcdn.com/w40/{{ strtolower($countries->firstWhere('phone', old('country_code', ltrim($user->country_code, '+')))->code ?? 'in') }}.png" class="flag-icon">
											</span>
											<select name="country_code" class="form-control" id="country_code" readonly>
												@foreach($countries as $country)
													<option value="{{ $country->phone }}" data-code="{{ strtolower($country->code) }}"
													{{ old('country_code', ltrim($user->country_code, '+')) == $country->phone ? 'selected' : '' }}>
													{{ $country->libelle }} (+{{ $country->phone }})
													</option>
												@endforeach
											</select>
											<input type="text" class="form-control user_phone" name="phone" value="{{$user->phone}}" readonly>
										</div>
										<div class="form-text text-muted w-50">
											{{ trans("lang.user_phone_help") }}
										</div>
									</div>

								</div>						

								<div class="form-group row width-100">
									<label class="col-2 control-label">{{trans('lang.profile_image')}}</label>
									<input type="file" class="col-6 photo" name="photo" onchange="readURL(this);">

									{{--@if (file_exists('assets/images/users'.'/'.$user->photo_path) && !empty($user->photo_path))--}}
									@if (file_exists(public_path('assets/images/users'.'/'.$user->photo_path)) && !empty($user->photo_path))
										<img class="rounded" id="uploding_image" style="width:50px" src="{{asset('assets/images/users').'/'.$user->photo_path}}" alt="image">
									@else
									<img class="rounded" id="uploding_image" style="width:50px" src="{{asset('assets/images/placeholder_image.jpg')}}" alt="image">

									@endif

								</div>						
								<div class="form-group row width-50">
									<div class="form-check">
										@if ($user->statut === "yes")
											<input type="checkbox" class="user_active" name="statut" id="user_active" checked="checked"  value="yes"/>
										@else
											<input type="checkbox" class="user_active" name="statut" id="user_active" value="no"/>
										@endif
										<label class="col-3 control-label" for="user_active">{{trans('lang.active')}}</label>
									</div>
								</div>						
								</div>
							</fieldset>
						</div>
						<div class="form-group col-12 text-center btm-btn" >
							<button type="submit" class="btn btn-primary  edit-form-btn" ><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>
							<a href="{!! route('users.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
						</div>
					</div>
					
				</form>
			</div>
        </div>
	</div>
</div>

@endsection

@section('scripts')

<script type="text/javascript">

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

    function readURL(input) {
		console.log(input.files);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#uploding_image').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

	function readURLNic(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
				$('#user_nic_image').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
