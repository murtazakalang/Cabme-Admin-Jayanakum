@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.payout_request') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.payout_request') }}</li>
                </ol>
            </div>
            <div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/payment.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.payout_request') }}</h3>
                                <span class="counter ml-3">{{ $totalLength }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                         <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.payout_request') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.payout_request_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{ trans('lang.processing') }}</div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('lang.driver') }}</th>
                                                <th>{{ trans('lang.paid_amount') }}</th>
                                                <th>{{ trans('lang.drivers_payout_note') }}</th>
                                                <th>{{ trans('lang.drivers_payout_paid_date') }}</th>
                                                <th>{{ trans('lang.drivers_payout_status') }}</th>
                                                <th>{{ trans('lang.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($withdrawal) > 0)
                                                @foreach ($withdrawal as $value)
                                                    <tr>
                                                        <td>{{ $value->nom }} {{ $value->prenom }}</td>
                                                        <td>
                                                            @if ($currency->symbol_at_right == 'true')
                                                                {{ number_format($value->amount, $currency->decimal_digit) . '' . $currency->symbole }}
                                                            @else
                                                                {{ $currency->symbole . '' . number_format($value->amount, $currency->decimal_digit) }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $value->note }}</td>
                                                        <td>
                                                            <span class="date">{{ date('d F Y', strtotime($value->creer)) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($value->creer)) }}</span>
                                                        </td>
                                                        <td>{{ $value->statut }}</td>
                                                        <td class="action-btn"><a name="driver_view" id={{ $value->id_conducteur }} href="javascript:void(0)" data-toggle="modal" data-target="#bankdetailsModal"><i class="mdi mdi-eye" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view_details') }}"></i></a>
                                                            <a name="driver_check" id="{{ $value->id }}" href="javascript:void(0)"><i class="mdi mdi-check edit-form-btn" style="color:green" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.accept') }}"></i></a>
                                                            <a name="driver_reject" id="{{ $value->id }}" href="javascript:void(0)"><i class="mdi mdi-close edit-form-btn" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.reject') }}"></i></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="11" align="center">{{ trans('lang.no_result') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>                                  
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $withdrawal->firstItem() }} {{trans('lang.to_small')}} {{ $withdrawal->lastItem() }} {{trans('lang.of')}} {{ $withdrawal->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $withdrawal->links('pagination.pagination') }}
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
    </div>
    <div class="modal fade" id="bankdetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.bank_details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <input type="hidden" name="driverId" id="driverId">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.bank_name') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="bank_name" class="form-control" id="bankName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.branch_name') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="branch_name" class="form-control" id="branchName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{ trans('lang.holder_name') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="holer_name" class="form-control" id="holderName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.account_number') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="account_number" class="form-control" id="accountNumber">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.other_information') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="other_information" class="form-control" id="otherDetails">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('close') }}</a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).on("click", "a[name='driver_view']", function(e) {
            $('#bankName').val("");
            $('#branchName').val("");
            $('#holderName').val("");
            $('#accountNumber').val("");
            $('#otherDetails').val("");
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "driver/getbankdetails",
                data: {
                    'id': id
                },
                method: "post",
                success: function(data) {
                    var obj = JSON.parse(data);
                    $('#bankName').val(obj['bankName']);
                    $('#branchName').val(obj['branchName']);
                    $('#holderName').val(obj['holderName']);
                    $('#accountNumber').val(obj['accNo']);
                    $('#otherDetails').val(obj['other_info']);
                },
            })
        });
        $(document).on("click", "a[name='driver_check']", function(e) {
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "withdrawal/accept",
                data: {
                    'id': id
                },
                method: "post",
                success: function(data) {
                    window.location.reload();
                },
            })
        });
        $(document).on("click", "a[name='driver_reject']", function(e) {
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "withdrawal/reject",
                data: {
                    'id': id
                },
                method: "post",
                success: function(data) {
                    window.location.reload();
                },
            })
        });
    </script>
@endsection
