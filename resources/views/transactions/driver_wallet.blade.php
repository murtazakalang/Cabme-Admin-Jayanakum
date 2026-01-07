@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">
                    @if($transaction->count() > 0)
                        @if($transaction[0]->role == 'owner')
                            {{ trans('lang.owner_wallet_transaction_plural') }}
                        @else
                            {{ trans('lang.driver_wallet_transaction_plural') }}
                        @endif
                    
                    @else
                        @if($userRole == 'owner')
                            {{ trans('lang.owner_wallet_transaction_plural') }}
                        @else
                            {{ trans('lang.driver_wallet_transaction_plural') }}
                        @endif
                    @endif
                </h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">  
                        @if($transaction->count() > 0)
                            @if($transaction[0]->role == 'owner')
                                {{ trans('lang.owner_wallet_transaction_plural') }}
                            @else
                                {{ trans('lang.driver_wallet_transaction_plural') }}
                            @endif
                        @else
                            @if($userRole == 'owner')
                            {{ trans('lang.owner_wallet_transaction_plural') }}
                            @else
                                {{ trans('lang.driver_wallet_transaction_plural') }}
                            @endif
                        @endif
                    </li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/wallet.png') }}"></span>
                                <h3 class="mb-0">
                                    @if($transaction->count() > 0)
                                        @if($transaction[0]->role == 'owner')
                                            {{ trans('lang.owner_wallet_transaction_plural') }}
                                        @else
                                            {{ trans('lang.driver_wallet_transaction_plural') }}
                                        @endif
                                    @else
                                        @if($userRole == 'owner')
                                            {{ trans('lang.owner_wallet_transaction_plural') }}
                                        @else
                                            {{ trans('lang.driver_wallet_transaction_plural') }}
                                        @endif
                                    @endif
                                </h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">                                       
                                        @if($transaction->count() > 0)
                                            @if($transaction[0]->role == 'owner')
                                                {{ trans('lang.owner_wallet_transaction_table') }}
                                            @else
                                                {{ trans('lang.driver_wallet_transaction_table') }}
                                            @endif
                                        @else
                                            @if($userRole == 'owner')
                                                {{ trans('lang.owner_wallet_transaction_table') }}
                                            @else
                                                {{ trans('lang.driver_wallet_transaction_table') }}
                                            @endif
                                        @endif
                                    </h3>
                                    <p class="mb-0 text-dark-2">                                       
                                        @if($transaction->count() > 0)
                                            @if($transaction[0]->role == 'owner')
                                                {{ trans('lang.owner_wallet_transaction_table_text') }}
                                            @else
                                                {{ trans('lang.driver_wallet_transaction_table_text') }}
                                            @endif
                                        @else
                                            @if($userRole == 'owner')
                                                {{ trans('lang.owner_wallet_transaction_table_text') }}
                                            @else
                                                {{ trans('lang.driver_wallet_transaction_table_text') }}
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{ trans('lang.processing') }}</div>
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
                                            @if ($id != '')
                                                <form action="{{ route('walletstransaction.driver', ['id' => $id]) }}" method="get">
                                            @else
                                                <form action="{{ route('walletstransaction.driver') }}" method="get">
                                            @endif
                                            <label>{{ trans('lang.search_by') }}</label>
                                            <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                <option value="transaction_id" {{ request('selected_search') == 'transaction_id' ? 'selected' : '' }}>
                                                    {{ trans('lang.transaction_id') }}
                                                </option>
                                            </select>
                                            <div class="search-box position-relative">
                                                <input type="text" class="search form-control" name="search" id="search"  value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                <button type="submit" class="btn-flat position-absolute"><i class="fa fa-search"></i></button>
                                            </div>
                                            <button type="submit" onclick="searchtext();" class="btn btn-warning btn-flat">{{ trans('lang.search') }}</button>
                                            @if ($id != '')
                                                <a href="{{ route('walletstransaction.driver', ['id' => $id]) }}" class="btn btn-warning btn-flat">{{ trans('lang.clear') }}</a>
                                            @else
                                                <a href="{{ route('walletstransaction.driver') }}" class="btn btn-warning btn-flat">{{ trans('lang.clear') }}</a>
                                            @endif
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive m-t-10">
                                <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.transaction_id') }}</th>
                                            @if ($id == '')
                                                <th>{{ trans('lang.driver_name') }}</th>
                                            @endif
                                            <th>{{ trans('lang.amount') }}</th>
                                            <th>{{ trans('lang.date') }}</th>
                                            <th>{{ trans('lang.payment_method') }}</th>
                                            <th>{{ trans('lang.note') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                        @if (count($transaction) > 0)
                                            @foreach ($transaction as $data)
                                                <tr>
                                                    <td>{{ $data->id }}</td>
                                                    @if ($id == '')
                                                        <td>
                                                            <a href="{{ route('driver.show', ['id' => $data->userId]) }}">{{ $data->firstname }} {{ $data->lastname }}</a>
                                                        </td>
                                                    @endif
                                                    <td>
                                                        @if ($currency->symbol_at_right == 'true')
                                                            @if ($data->is_credited == '0')
                                                                <span style="color:red">(-{{ number_format($data->amount, $currency->decimal_digit) . '' . $currency->symbole }})</span>
                                                            @else
                                                                <span style="color:green">{{ number_format($data->amount, $currency->decimal_digit) . '' . $currency->symbole }}</span>
                                                            @endif
                                                        @else
                                                            @if ($data->is_credited == '0')
                                                                <span style="color:red">(-{{ $currency->symbole . '' . number_format($data->amount, $currency->decimal_digit) }})</span>
                                                            @else
                                                                <span style="color:green">{{ $currency->symbole . '' . number_format($data->amount, $currency->decimal_digit) }}</span>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>                                                      
                                                        <span class="date">{{ date('d F Y', strtotime($data->created_at)) }}</span>
                                                        <span class="time">{{ date('h:i A', strtotime($data->created_at)) }}</span>
                                                    </td>
                                                    @if ($data->image)
                                                        <td><img class="rounded" style="width:50px" src="{{ asset('/assets/images/payment_method/' . $data->image) }}" alt="image"></td>
                                                    @else
                                                        <td>{{ $data->payment_method }}</td>
                                                    @endif
                                                    <td>{{ $data->note }}</td>
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
                                        {{trans('lang.showing')}} {{ $transaction->firstItem() }} {{trans('lang.to_small')}} {{ $transaction->lastItem() }} {{trans('lang.of')}} {{ $transaction->total() }} {{trans('lang.entries')}}
                                    </div>
                                    <div>
                                        {{ $transaction->links('pagination.pagination') }}
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
@endsection
@section('scripts')
@endsection
