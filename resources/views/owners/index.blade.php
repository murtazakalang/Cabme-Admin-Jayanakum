@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">
                    @if(request()->is('owners/approved'))
                        {{ trans('lang.approved_owners') }}
                    @elseif(request()->is('owners/pending'))
                        {{ trans('lang.pending_owners') }}
                    @else
                        {{ trans('lang.all_owners') }}
                    @endif
                </h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ trans('lang.owner_plural') }}
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
                                <span class="icon mr-3"><img src="{{ asset('images/users.png') }}"></span>
                                <h3 class="mb-0">
                                    @if(request()->is('owners/approved'))
                                        {{ trans('lang.approved_owners') }}
                                    @elseif(request()->is('owners/pending'))
                                        {{ trans('lang.pending_owners') }}
                                    @else
                                        {{ trans('lang.all_owners') }}
                                    @endif
                                </h3>
                                <span class="counter ml-3 zone_count">{{ $totalLength }}</span>
                            </div>
                            <form action="{{ route('owners.index') }}" method="get" id="filterForm">
                                <div class="d-flex top-title-right align-self-center">
                                    <div class="select-box pl-3">
                                        <select class="form-control status_selector filteredRecords" name="status_selector">
                                            <option value="">{{ trans('lang.status') }}</option>
                                            <option value="active" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'active' ? 'selected ' : '' }}>{{ trans('lang.active') }}</option>
                                            <option value="inactive" {{ isset($_GET['status_selector']) && $_GET['status_selector'] == 'inactive' ? 'selected' : '' }}>{{ trans('lang.in_active') }}</option>
                                        </select>
                                    </div>
                                    <div class="select-box pl-3">
                                        <input type="text" placeholder="dd-mm-yyyy" class="form-control filteredRecords" id="daterange" name="daterange" value="{{ request('daterange') }}" readonly />
                                    </div>
                                </div>
                            </form>
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
                                        @if(request()->is('owners/approved'))
                                            {{ trans('lang.approved_owners') }}
                                        @elseif(request()->is('owners/pending'))
                                            {{ trans('lang.pending_owners') }}
                                        @else
                                            {{ trans('lang.all_owners') }}
                                        @endif
                                    </h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.owner_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('owners.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.owners_create') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                                    {{ trans('lang.processing') }}
                                </div>
                                <div class="userlist-topsearch-flex mb-3">
                                    <div class="userlist-topsearch d-flex mb-0">
                                        <div id="users-table_filter" class="ml-auto">
                                            <div class="form-group mb-0">
                                                <form method="GET" action="{{ url()->current() }}" id="perPageForm">
                                                    <label for="per_page">{{ trans('lang.show') }}</label>
                                                    <select name="per_page" id="per_page" class="form-control input-sm" onchange="document.getElementById('perPageForm').submit()">
                                                        <option value="10" {{  $perPage == 10 ? 'selected' : '' }}>10</option>
                                                        <option value="20" {{  $perPage == 20 ? 'selected' : '' }}>20</option>
                                                        <option value="30" {{  $perPage == 30 ? 'selected' : '' }}>30</option>
                                                        <option value="50" {{  $perPage == 50 ? 'selected' : '' }}>50</option>
                                                        <option value="100" {{  $perPage == 100 ? 'selected' : '' }}>100</option>
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
                                                    <form action="{{ route('owners.index') }}" method="get">
                                                        <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                            <option value="prenom" {{ request('selected_search') == 'prenom' ? 'selected' : '' }}>
                                                                {{ trans('lang.user_name') }}
                                                            </option>
                                                            <option value="phone" {{ request('selected_search') == 'phone' ? 'selected' : '' }}>
                                                                {{ trans('lang.user_phone') }}
                                                            </option>
                                                            <option value="email" {{ request('selected_search') == 'email' ? 'selected' : '' }}>
                                                                {{ trans('lang.email') }}
                                                            </option>
                                                        </select>
                                                        <div class="search-box position-relative">
                                                            <input type="text" class="search form-control" name="search" id="search"
                                                                value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                            <button type="submit" class="btn-flat position-absolute">
                                                                <i class="fa fa-search"></i>
                                                            </button>
                                                            <a class="btn btn-warning btn-flat"
                                                                href="
                                                                        @if (Request::is('owners/approved'))
                                                                            {{ route('owners.approved') }}
                                                                        @elseif (Request::is('owners/pending'))
                                                                            {{ route('owners.pending') }}
                                                                        @else
                                                                            {{ route('owners.index') }}
                                                                        @endif
                                                                    ">
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
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'Owner']) }}">{{ trans('lang.export_excel') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'Owner']) }}">{{ trans('lang.export_pdf') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'Owner']) }}">{{ trans('lang.export_csv') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('owners.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.owner_name') }}</th>
                                                <th>{{ trans('lang.email') }}</th>
                                                <th>{{ trans('lang.active_plan') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.dispatcher_access') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list12">
                                            @if (count($owners) > 0)
                                                @foreach ($owners as $owner)
                                                    <tr>
                                                        @can('owners.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $owner->id }}" class="is_open" dataid="{{ $owner->id }}"><label class="col-3 control-label" for="is_open_{{ $owner->id }}"></label></td>
                                                        @endcan
                                                        <td> 
                                                            @if (file_exists(public_path('assets/images/driver' . '/' . $owner->photo_path)) && !empty($owner->photo_path))
                                                                <img class="rounded" style="width:50px" src="{{ asset('assets/images/driver') . '/' . $owner->photo_path }}" alt="image">
                                                            @else
                                                                <img class="rounded" style="width:50px" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image">
                                                            @endif
                                                            <a href="{{ route('owners.show', ['id' => $owner->id]) }}"> 
                                                                {{ $owner->prenom }} {{ $owner->nom }}
                                                            </a>
                                                            @if($owner->is_verified)
                                                                <i class="mdi mdi-verified verified-icon" title="Verified"></i>
                                                            @endif
                                                        </td>
                                                        <td>{{ $owner->email }}</td>
                                                        <td>
                                                            @if (!empty($owner->subscription_plan))
                                                                <span>{{ trans('lang.plan_name') }} : {{ $owner->subscription_plan['name'] }}</span><br>
                                                                <span>{{ trans('lang.expiry_date') }} : {{ $owner->subscriptionExpiryDate == null ? trans('lang.unlimited') : date('d F Y', strtotime($owner->subscriptionExpiryDate)) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($owner->statut == 'yes')
                                                                <label class="switch"><input type="checkbox" checked id="{{ $owner->id }}" name="isActive"><span class="slider round"></span></label>
                                                            @else
                                                                <label class="switch"><input type="checkbox" id="{{ $owner->id }}" name="isActive"><span class="slider round"></span></label><span>
                                                            @endif
                                                        </td>
                                                        <?php $count = 0; ?>
                                                        @if ($owner->subscriptionPlanId && !empty($owner->subscription_plan))
                                                            <td>
                                                                @if ($owner->subscription_plan['dispatcher_access'] == "yes")
                                                                    <span class="badge badge-success">{{ trans('lang.yes') }}</span>
                                                                @else
                                                                    <span class="badge badge-danger">{{ trans('lang.no') }}</span>
                                                                @endif
                                                            </td>
                                                        @else
                                                            <td></td>
                                                        @endif
                                                        <td class="action-btn">
                                                            <a href="{{ route('walletstransaction.driver', ['id' => $owner->id]) }}"><i class="mdi mdi-wallet" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.wallet_transaction') }}"></i></a>
                                                            <a href="{{ route('driver.documentView', ['id' => $owner->id]) }}"><i class="mdi mdi-file" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.document_details') }}"></i></a>
                                                            <a href="{{ route('owners.show', ['id' => $owner->id]) }}"><i class="mdi mdi-eye" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view_details') }}"></i></a>
                                                            <a href="{{ route('owners.edit', ['id' => $owner->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                            @can('owners.delete')
                                                                <a class="delete-btn" name="user-delete" href="{{ route('owners.delete', ['id' => $owner->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
                                                            @endcan
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
                                            {{trans('lang.showing')}} {{ $owners->firstItem() }} {{trans('lang.to_small')}} {{ $owners->lastItem() }} {{trans('lang.of')}} {{ $owners->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $owners->links('pagination.pagination') }}
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
    <script type="text/javascript">
        $(document).ready(function() {
            $(".shadow-sm").hide();
        })
        $("#is_active").click(function() {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));
        });
        $('.status_selector').select2({
            placeholder: '{{ trans('lang.status') }}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('select').on("select2:unselecting", function(e) {
            var self = $(this);
            setTimeout(function() {
                self.select2('close');
            }, 0);
        });
        function setDate() {
            let initialDateRange = $('#daterange').val(); // Get the initial value from input
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY',
                    cancelLabel: "{{trans('lang.clear')}}"
                }
            });
            if (initialDateRange) {
                let dates = initialDateRange.split(' - ');
                $('#daterange').data('daterangepicker').setStartDate(dates[0]);
                $('#daterange').data('daterangepicker').setEndDate(dates[1]);
                $('#daterange').val(initialDateRange);
            }
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
                $('.filteredRecords').trigger('change');
            });
            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('.filteredRecords').trigger('change');
            });
        }
        setDate();
        $('.filteredRecords').change(async function() {
            $('#filterForm').submit();
        })
        $("#deleteAll").click(function() {
            if ($('#example24 .is_open:checked').length) {
                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('owners/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert('{{ trans('lang.select_delete_alert') }}');
            }
        });
        /* toggal publish action code start*/
        $(document).on("click", "input[name='isActive']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'owner/switch',
                method: "POST",
                data: {
                    'ischeck': ischeck,
                    'id': id
                },
                success: function(data) {
                },
            });
        });
        /*toggal publish action code end*/
    </script>
@endsection
