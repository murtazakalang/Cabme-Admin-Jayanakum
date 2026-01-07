@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.drivers_disbursements') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.drivers_disbursements') }}</li>
            </ol>
        </div>
        <div></div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/payment.png') }}"></span>
                            <h3 class="mb-0">{{ trans('lang.drivers_disbursements') }}</h3>
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
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.drivers_disbursements') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.drivers_payout_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('driversPayouts.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.drivers_payout_create') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (session('message'))
                                <div class="alert alert-success">
                                    {{ session('message') }}
                                </div>
                            @endif
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                </div>
                            @endif
                            <div class="userlist-topsearch-flex mb-3">
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
                                <div class="userlist-topsearch d-flex mb-0">
                                    <div id="users-table_filter" class="ml-auto">
                                        <label>{{ trans('lang.search_by') }}
                                            <div class="form-group mb-0">
                                                <form action="{{ route('driversPayouts.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="note" @if (request('selected_search') == 'note') selected @endif>
                                                            {{ trans('lang.drivers_payout_note') }}
                                                        </option>
                                                        <option value="driver" @if (request('selected_search') == 'driver') selected @endif>
                                                            {{ trans('lang.driver_name') }}
                                                        </option>
                                                        <option value="status" @if (request('selected_search') == 'status') selected @endif>
                                                            {{ trans('lang.status') }}
                                                        </option>
                                                        <option value="payout_request_id" @if (request('selected_search') == 'payout_request_id') selected @endif>
                                                            {{ trans('lang.payout_request_id') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative mt-2">
                                                        <input type="text" class="search form-control" name="search" id="search"
                                                            value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute"><i class="fa fa-search"></i></button>
                                                        <a class="btn btn-warning btn-flat" href="{{ url('driversPayouts') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="dropdown text-right">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-download"></i> {{ trans('lang.export_as') }}
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                        <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'Withdrawal']) }}">{{ trans('lang.export_excel') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'Withdrawal']) }}">{{ trans('lang.export_pdf') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'Withdrawal']) }}">{{ trans('lang.export_csv') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="table-responsive m-t-10">
                                <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                            <th>{{ trans('lang.payout_request_id') }}</th>
                                            <th>{{ trans('lang.driver') }}</th>
                                            <th>{{ trans('lang.paid_amount') }}</th>
                                            <th>{{ trans('lang.drivers_payout_note') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.drivers_payout_paid_date') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                        @if (count($withdrawal) > 0)
                                            @foreach ($withdrawal as $value)
                                                <tr>
                                                    <td class="delete-all"><input type="checkbox" id="is_open_{{ $value->id }}" class="is_open" dataid="{{ $value->id }}"><label class="col-3 control-label" for="is_open_{{ $value->id }}"></label></td>
                                                    <td>{{ $value->request_id }}</td>
                                                    <td>{{ $value->prenom }} {{ $value->nom }}</td>
                                                    <td>
                                                        @if ($currency->symbol_at_right == 'true')
                                                            <span style="color:red">({{ number_format($value->amount, $currency->decimal_digit) . '' . $currency->symbole }})</span>
                                                        @else
                                                            <span style="color:red">({{ $currency->symbole . '' . number_format($value->amount, $currency->decimal_digit) }})</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $value->note }} </td>
                                                    <td>
                                                        @if ($value->statut == 'success')
                                                            <span class="badge badge-success">{{ $value->statut }}<span>
                                                        @elseif ($value->statut == 'pending')
                                                            <span class="badge badge-warning">{{ $value->statut }}<span>
                                                        @elseif ($value->statut == 'reject')
                                                            <span class="badge badge-danger">{{ $value->statut }}<span>
                                                        @endif
                                                    </td>
                                                    <td>{{ date('d F Y h:i A', strtotime($value->creer)) }} </td>
                                                    <td class="action-btn">
                                                        
                                                        @if ($value->statut == 'pending')
                                                           
                                                            <a href="javascript:void(0)" 
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ trans('lang.accept') }}" 
                                                                onclick="openBankDetailsModal({{ $value->id_conducteur }}, {{ $value->id }}, {{$value->amount}})">
                                                                <i class="mdi mdi-check-circle"></i>
                                                            </a>


                                                            <a href="javascript:void(0)" 
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ trans('lang.reject') }}" 
                                                                onclick="openCancelRequestModal({{ $value->id }})">
                                                                <i class="mdi mdi-close-circle-outline"></i>
                                                            </a>

                                                        @endif

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
</div>

<div class="modal fade" id="bankdetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered location_modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title locationModalTitle">{{ trans('lang.bankdetails') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeBankDetailsModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('withdraw.accept') }}">
                    @csrf
                    <div class="form-row">
                       
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{ trans('lang.bank_name') }}</label>
                                <div class="col-12">
                                    <input type="text" name="bank_name" class="form-control" id="bankName" disabled>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{ trans('lang.branch_name') }}</label>
                                <div class="col-12">
                                    <input type="text" name="branch_name" class="form-control" id="branchName" disabled>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{ trans('lang.holer_name') }}</label>
                                <div class="col-12">
                                    <input type="text" name="holer_name" class="form-control" id="holderName" disabled>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{ trans('lang.account_number') }}</label>
                                <div class="col-12">
                                    <input type="text" name="account_number" class="form-control" id="accountNumber" disabled>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{ trans('lang.other_information') }}</label>
                                <div class="col-12">
                                    <input type="text" name="other_information" class="form-control" id="otherDetails" disabled>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{ trans('lang.ifsc_code') }}</label>
                                <div class="col-12">
                                    <input type="text" name="ifsc_code" class="form-control" id="ifsc_code" disabled>
                                </div>
                            </div>
                            <input type="hidden" name="driverId" id="driverId">
                            <input type="hidden" name="withdrawalId" id="withdrawalId">
                            <input type="hidden" name="walletBalance" id="walletBalance">
                            <input type="hidden" name="requestedAmount" id="requestedAmount"> 


                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-form-btn" id="submit_accept">
                            {{ trans('lang.accept') }}</a>
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close" onclick="closeBankDetailsModal()">
                            {{ trans('lang.close') }}</a>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cancelRequestModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title locationModalTitle">{{ trans('lang.cancel_payout_request') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeCancelRequestModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="data-table_processing_modal" class="dataTables_processing panel panel-default" style="display: none;">{{ trans('lang.processing') }}
                </div>
                <form id="cancelRequestForm" method="POST">
                @csrf
                    <div class="form-row">
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{ trans('lang.notes') }}</label>
                                <div class="col-12">
                                    <textarea name="admin_note" class="form-control @error('admin_note') is-invalid @enderror" id="admin_note" cols="5" rows="5"></textarea>
                                    @error('admin_note')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-form-btn" id="submit_cancel">
                            {{ trans('lang.submit') }}</a>
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close" onclick="closeCancelRequestModal()">
                            {{ trans('lang.close') }}</a>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    let currentWithdrawalId = null;

    $("#deleteAll").click(function() {
        if ($('#example24 .is_open:checked').length) {
            if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                var arrayUsers = [];
                $('#example24 .is_open:checked').each(function() {
                    arrayUsers.push($(this).attr('dataId'));
                });
                var url = "{{ route('driversPayouts.delete', ':id') }}";
                url = url.replace(':id', 0);                
                url += "?ids=" + arrayUsers.join(",");
                $(this).attr('href', url);                
            }
        } else {
            alert('{{trans("lang.select_delete_alert")}}');
        }
    });
    function openBankDetailsModal(driverId, withdrawalId, requestedAmount) {
        $('#bankdetailsModal').modal('show');

        $('#driverId').val(driverId);
        $('#withdrawalId').val(withdrawalId);
        $('#requestedAmount').val(requestedAmount);

        // reset fields
        $('#bankName').val('');
        $('#branchName').val('');
        $('#holderName').val('');
        $('#accountNumber').val('');
        $('#otherDetails').val('');
        $('#ifsc_code').val('');
        $('#walletBalance').val(''); 

        $.ajax({
            url: "/drivers/" + driverId + "/bank-details",
            type: "GET",
            success: function (data) {
                $('#bankName').val(data.bank_name);
                $('#branchName').val(data.branch_name);
                $('#holderName').val(data.holder_name);
                $('#accountNumber').val(data.account_number);
                $('#otherDetails').val(data.other_information);
                $('#ifsc_code').val(data.ifsc_code);
                $('#walletBalance').val(data.amount); 
            },
            error: function () {
                alert("Could not load bank details.");
            }
        });
    }



    function closeBankDetailsModal() {
        $('#bankdetailsModal').modal('hide');
    }
   
    window.openCancelRequestModal = function(id) {
        currentWithdrawalId = id;
        $('#cancelRequestForm').attr('action', '/withdrawals/reject/' + id);
        $('#cancelRequestModal').modal('show');
    };


    window.closeCancelRequestModal = function() {
        $('#cancelRequestModal').modal('hide');
        $('#admin_note').val('');
        currentWithdrawalId = null;
    };
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });



</script>
@endsection
