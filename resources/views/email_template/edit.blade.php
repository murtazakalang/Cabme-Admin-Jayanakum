@extends('layouts.app')



@section('content')

<div class="page-wrapper">

	<div class="row page-titles">

		<div class="col-md-5 align-self-center">

			<h3 class="text-themecolor">{{trans('lang.email_template_plural')}}</h3>

		</div>

		<div class="col-md-7 align-self-center">

			<ol class="breadcrumb">

				<li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

				<li class="breadcrumb-item active">{{trans('lang.email_template_plural')}}</li>

				<li class="breadcrumb-item active">{{trans('lang.edit_email_template')}}</li>

			</ol>

		</div>



	</div>

	<div class="container-fluid">

		<div class="row">

			<div class="col-12">

				<div class="card pb-4">

					<div class="card-body">



						<div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{trans('lang.processing')}}</div>

						<div class="error_top"></div>



						<form action="{{ route('email-template.update',$template->id) }}" method="post" enctype="multipart/form-data" id="edit_template">

							@csrf

							@method("PUT")



							<div class="row restaurant_payout_create term-cond">

								<div class="restaurant_payout_create-inner">

									<fieldset>

										<legend>{{trans('lang.email_template')}}</legend>

										<div class="form-group row">

											<div class="col-12 p-0">

												<label for="send_admin">{{trans('lang.template_type')}}</label>

												@if($template->type=="payment_receipt")

													@php $type=trans("lang.payment_receipt") @endphp

												@elseif($template->type=="wallet_topup")

													@php $type=trans("lang.wallet_topup") @endphp

												@elseif($template->type=="payout_approve_disapprove")

													@php $type=trans("lang.payout_approve_disapprove") @endphp

												@elseif($template->type=="payout_request")

													@php $type=trans("lang.payout_request") @endphp

												@elseif($template->type=="new_registration")

													@php $type=trans("lang.new_registration") @endphp

												@elseif($template->type=="reset_password")

													@php $type=trans("lang.reset_password") @endphp

												@elseif($template->type=="parcel_payment_receipt")

													@php $type=trans("lang.parcel_payment_receipt") @endphp

												@elseif($template->type=="rental_payment_receipt")

													@php $type=trans("lang.rental_payment_receipt") @endphp

												@endif

												<input type="text" class="form-control col-7" name="type" id="type"

													value="{{$type}}" readonly>

											</div>

										</div>

										<div class="form-group row">

											<div class="col-12 p-0">

												<label for="send_admin">{{trans('lang.subject')}}</label>

												<input type="text" class="form-control col-7" name="subject" id="subject" value="{{$template->subject}}">

											</div>

										</div>





										<div class="form-group row">

											<div class="col-12 p-0">

												<label>{{trans('lang.message')}}</label>

												<textarea class="form-control col-7" name="message" id="message">{{$template->message}}</textarea>

											</div>

										</div>
										<div class="form-group row width-100 email-tags">
											<label class="col-3 control-label">{{ trans('lang.available_tags') }}</label>
											<div class="col-7">
												<div class="p-2 border rounded">
														<div class="tag-user-name"><code>{username}</code>: <span>{{ trans('lang.tag_user_name') }}</span></div>
													@if($template->type && $template->type == 'payment_receipt')
														<div class="tag-distance"><code>{Distance}</code>: <span>{{ trans('lang.tag_distance') }}</span></div>
														<div class="tag-duration"><code>{Duree}</code>: <span>{{ trans('lang.tag_duration') }}</span></div>
														<div class="tag-subtotal"><code>{Subtotal}</code>: <span>{{ trans('lang.tag_subtotal') }}</span></div>
														<div class="tag-discount"><code>{Discount}</code>: <span>{{ trans('lang.tag_discount') }}</span></div>
														<div class="tag-tax"><code>{Tax}</code>: <span>{{ trans('lang.tag_tax') }}</span></div>
														<div class="tag-tip"><code>{Tip}</code>: <span>{{ trans('lang.tag_tip') }}</span></div>
														<div class="tag-total"><code>{Total}</code>: <span>{{ trans('lang.tag_total') }}</span></div>
														<div class="tag-date"><code>{Date}</code>: <span>{{ trans('lang.tag_date') }}</span></div>
														<div class="tag-app-name"><code>{AppName}</code>: <span>{{ trans('lang.tag_app_name') }}</span></div>
													@elseif($template->type && $template->type == 'wallet_topup')
														<div class="tag-amount"><code>{Amount}</code>: <span>{{ trans('lang.tag_amount') }}</span></div>
														<div class="tag-date"><code>{Date}</code>: <span>{{ trans('lang.tag_date') }}</span></div>
														<div class="tag-payment-method"><code>{PaymentMethod}</code>: <span>{{ trans('lang.tag_payment_method') }}</span></div>
														<div class="tag-transaction-id"><code>{TransactionId}</code>: <span>{{ trans('lang.tag_transaction_id') }}</span></div>
														<div class="tag-balance"><code>{Balance}</code>: <span>{{ trans('lang.tag_balance') }}</span></div>
														<div class="tag-app-name"><code>{AppName}</code>: <span>{{ trans('lang.tag_app_name') }}</span></div>
													@elseif($template->type && $template->type == 'payout_approve_disapprove')
														<div class="tag-request-id"><code>{RequestId}</code>: <span>{{ trans('lang.tag_request_id') }}</span></div>
														<div class="tag-status"><code>{Status}</code>: <span>{{ trans('lang.tag_status') }}</span></div>
														<div class="tag-amount"><code>{Amount}</code>: <span>{{ trans('lang.tag_amount') }}</span></div>
														<div class="tag-date"><code>{Date}</code>: <span>{{ trans('lang.tag_date') }}</span></div>
														<div class="tag-app-name"><code>{AppName}</code>: <span>{{ trans('lang.tag_app_name') }}</span></div>
													@elseif($template->type && $template->type == 'payout_request')
														<div class="tag-request-id"><code>{PayoutRequestId}</code>: <span>{{ trans('lang.tag_request_id') }}</span></div>
														<div class="tag-app-name"><code>{AppName}</code>: <span>{{ trans('lang.tag_app_name') }}</span></div>
														<div class="tag-user-id"><code>{UserId}</code>: <span>{{ trans('lang.tag_userid') }}</span></div>
														<div class="tag-amount"><code>{Amount}</code>: <span>{{ trans('lang.tag_amount') }}</span></div>
														<div class="tag-date"><code>{Date}</code>: <span>{{ trans('lang.tag_date') }}</span></div>
														<div class="tag-user-contact"><code>{UserContactInfo}</code>: <span>{{ trans('lang.tag_contact_info') }}</span></div>
													@elseif($template->type && $template->type == 'new_registration')
														<div class="tag-app-name"><code>{AppName}</code>: <span>{{ trans('lang.tag_app_name') }}</span></div>
														<div class="tag-user-id"><code>{UserId}</code>: <span>{{ trans('lang.tag_userid') }}</span></div>
														<div class="tag-user-email"><code>{UserEmail}</code>: <span>{{ trans('lang.tag_user_email') }}</span></div>
														<div class="tag-user-phone"><code>{UserPhone}</code>: <span>{{ trans('lang.tag_user_phone') }}</span></div>
														<div class="tag-date"><code>{Date}</code>: <span>{{ trans('lang.tag_date') }}</span></div>
													@elseif($template->type && $template->type == 'reset_password')
														<div class="tag-otp"><code>{OTP}</code>: <span>{{ trans('lang.tag_otp') }}</span></div>		
													@elseif($template->type && $template->type == 'parcel_payment_receipt')
														<div class="tag-app-name"><code>{AppName}</code>: <span>{{ trans('lang.tag_app_name') }}</span></div>		
														<div class="tag-subtotal"><code>{Subtotal}</code>: <span>{{ trans('lang.tag_subtotal') }}</span></div>
														<div class="tag-discount"><code>{Discount}</code>: <span>{{ trans('lang.tag_discount') }}</span></div>	
														<div class="tag-tax"><code>{Tax}</code>: <span>{{ trans('lang.tag_tax') }}</span></div>			
														<div class="tag-total"><code>{Total}</code>: <span>{{ trans('lang.tag_total') }}</span></div>
														<div class="tag-date"><code>{Date}</code>: <span>{{ trans('lang.tag_date') }}</span></div>	
													@elseif($template->type && $template->type == 'rental_payment_receipt')
														<div class="tag-app-name"><code>{AppName}</code>: <span>{{ trans('lang.tag_app_name') }}</span></div>		
														<div class="tag-subtotal"><code>{Subtotal}</code>: <span>{{ trans('lang.tag_subtotal') }}</span></div>
														<div class="tag-discount"><code>{Discount}</code>: <span>{{ trans('lang.tag_discount') }}</span></div>	
														<div class="tag-tax"><code>{Tax}</code>: <span>{{ trans('lang.tag_tax') }}</span></div>			
														<div class="tag-total"><code>{Total}</code>: <span>{{ trans('lang.tag_total') }}</span></div>
														<div class="tag-date"><code>{Date}</code>: <span>{{ trans('lang.tag_date') }}</span></div>						
													@endif
												</div>
											</div>
										</div>

										<div class="form-group row width-50">

											<div class="form-check">

												<input type="checkbox" class="send_admin" id="send_admin" name="send_admin" {{$template->send_to_admin ? "checked":""}}>

												<label class="col-3 control-label" for="send_admin">{{trans('lang.is_send_to_admin')}}</label>



											</div>



										</div>





									</fieldset>

								</div>

							</div>

							<div class="form-group col-12 text-center btm-btn text-center">

								<button type="submit" class="btn btn-primary  edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>
								<a href="{!! route('email-template.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>

						</form>

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

<script type="text/javascript">
	$('#message').summernote({

		height: 400,



		toolbar: [

			['style', ['bold', 'italic', 'underline', 'clear']],

			['font', ['strikethrough', 'superscript', 'subscript']],

			['fontsize', ['fontsize']],

			['color', ['color']],

			['forecolor', ['forecolor']],

			['backcolor', ['backcolor']],

			['para', ['ul', 'ol', 'paragraph']],

			['height', ['height']],

			['view', ['codeview', 'help']],



		]

	});
	$('#edit_template').on('submit', function() {
		var editor = $('#message');
		if (editor.summernote('codeview.isActivated')) {
			editor.summernote('code', editor.summernote('code'));
		}

	});
</script>





@endsection