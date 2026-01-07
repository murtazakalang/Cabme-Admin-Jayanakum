@extends('layouts.app')

@section('content')
<div class="page-wrapper">
	<div class="row page-titles">
		<div class="col-md-5 align-self-center">
			<h3 class="text-themecolor">{{trans('lang.coupon_plural')}}</h3>
		</div>

		<div class="col-md-7 align-self-center">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>			
            
          <li class="breadcrumb-item"><a href= "{!! route('coupons.index') !!}" >{{trans('lang.coupon_plural')}}</a></li>
      
				<li class="breadcrumb-item active">{{trans('lang.coupon_create')}}</li>
			</ol>
		</div>
</div>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card pb-4">

        <div class="card-body">
        
          <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{trans('lang.processing')}}</div>
          <div class="error_top" style="display:none"></div>
          @if($errors->any())
          <div class="alert alert-danger">
              <ul>
                  @foreach($errors->all() as $error)
                  <li class="error">{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
          @endif    
          <form method="post" action="{{ route('coupons.store') }}" enctype="multipart/form-data">
          @csrf

            <div class="row restaurant_payout_create">
      
              <div class="restaurant_payout_create-inner">         
              <fieldset>
                <legend>{{trans('lang.coupon_create')}}</legend>     

                <div class="form-group row width-50">
                    <label class="col-3 control-label">{{trans('lang.coupon_code')}}</label>
                    <div class="col-7">
                      <input type="text" type="text" class="form-control coupon_code" name="code" value="{{ Request::old('code')}}">
                      <div class="form-text text-muted">{{ trans("lang.coupon_code_help") }} </div>  
                    </div>
                </div>

                <div class="form-group row width-50">
                  <label class="col-3 control-label">{{trans('lang.coupon_discount_type')}}</label>
                  <div class="col-7">
                    <select id="coupon_discount_type" class="form-control" name="type">
                    @if (Request::old('type') == 'Percentage')
                      <option value="Percentage" selected>{{trans('lang.coupon_percent')}}</option>
                  @else
                  <option value="Percentage">{{trans('lang.coupon_percent')}}</option>
                  @endif
                  @if (Request::old('type') == 'Fixed')
                      <option value="Fixed" selected>{{trans('lang.coupon_fixed')}}</option>
                  @else
                  <option value="Fixed">{{trans('lang.coupon_fixed')}}</option>
                  @endif
                    </select>
                    <div class="form-text text-muted">{{ trans("lang.coupon_discount_type_help") }}</div>

                  </div>
                </div>

                <div class="form-group row width-50">
                  <label class="col-3 control-label">{{trans('lang.coupon_discount')}}</label>
                  <div class="col-7">
                    <input type="number" type="text" class="form-control coupon_discount" name="discount" value="{{ Request::old('discount')}}">
                    <div class="form-text text-muted">{{ trans("lang.coupon_discount_help") }}</div>  
                  </div>
                </div>

                <div class="form-group row width-50">
                    <label class="col-3 control-label">{{trans('lang.coupon_expires_at')}}</label>
                    <div class="col-7">
                      <!-- <div class="form-group"> -->
                        <div class='input-group date' id='datetimepicker1'>
                          <input type='date' class="form-control date_picker input-group-addon" name="expire_at"  value="{{ Request::old('expire_at')}}"  min="{{ now()->toDateString() }}"  />
                          <span class="">
                          <!-- <span class="glyphicon glyphicon-calendar fa fa-calendar"></span> -->
                          </span>
                        </div>
                      <div class="form-text text-muted">
                        {{ trans("lang.coupon_expires_at_help") }}
                      </div>   
                      <!-- </div> -->
                    </div>   
                </div>
                <div class="form-group row width-50">
                  <label class="col-3 control-label">{{trans('lang.coupon_type')}}</label>
                  <div class="col-7">
                    <select id="coupon_type" class="form-control" name="coupon_type">
                        <option value="Ride" {{ old('coupon_type') == 'Ride' ? 'selected="selected"' : '' }}>{{trans('lang.ride')}}</option>
                        <option value="Parcel" {{ old('coupon_type') == 'Parcel' ? 'selected="selected"' : '' }}>{{trans('lang.parcel')}}</option>
                        <option value="Rental" {{ old('coupon_type') == 'Rental' ? 'selected="selected"' : '' }}>{{trans('lang.rental')}}</option>
                    </select>
                    <div class="form-text text-muted">
                      {{ trans("lang.coupon_type_help") }}
                    </div> 
                  </div>
                </div>

                <div class="form-group row width-100">
                  <label class="col-3 control-label">{{trans('lang.coupon_description')}}</label>
                  <div class="col-7">
                    <textarea rows="12" class="form-control coupon_description" id="coupon_description" name="discription" value="{{ Request::old('description')}}">{{ Request::old('description')}}</textarea>
                    <div class="form-text text-muted">{{ trans("lang.coupon_description_help") }}</div>
                  </div>
                </div>


                <div class="form-group row width-100">
                  <div class="form-check">                    
                    <input type="checkbox" class="coupon_enabled" id="coupon_enabled" name="statut">
                    <label class="col-3 control-label" for="coupon_enabled">{{trans('lang.coupon_enabled')}}</label>

                  </div>
                </div>

         
              </fieldset>
            </div>

        </div>

      </div>
    
      <div class="form-group col-12 text-center btm-btn">
        <button type="submit" class="btn btn-primary save-form-btn" ><i class="fa fa-save"></i> {{ trans('lang.save')}}</button>       
        <a href="{!! route('coupons.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
      </div>
</form>
  </div>

</div>
</div>
</div>
</div>

@endsection

@section('scripts')
<script>
  $('.save-form-btn').on('click',function(){

    var code = $('.coupon_code').val();
    if(code != '')
    {
      $('.error').html("{{trans('lang.code_field_is_required')}}");
    }

  })
</script>
@endsection