@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.user_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.user_plural')}}</li>
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
                                <div class="d-flex top-title-left align-self-center">
                                    <span class="icon mr-3"><img src="{{ asset('images/users.png') }}"></span>
                                    <h3 class="mb-0">{{ trans('lang.user_plural') }}</h3>
                                    <span class="counter ml-3 total_count">{{$totalLength}}</span>
                                </div>
                            </div>
                            <form action="{{ route('users.index') }}" method="get" id="filterForm">
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.user_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.users_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('users.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.user_create') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{ trans('lang.processing') }}
                                </div>
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
                                                    <form action="{{ route('users.index') }}" method="get">
                                                        <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                            <option value="prenom" {{ request('selected_search') == 'prenom' ? 'selected' : '' }}>
                                                                {{ trans('lang.user_name') }}
                                                            </option>
                                                            <option value="email" {{ request('selected_search') == 'email' ? 'selected' : '' }}>
                                                                {{ trans('lang.email') }}
                                                            </option>
                                                            <option value="phone" {{ request('selected_search') == 'phone' ? 'selected' : '' }}>
                                                                {{ trans('lang.user_phone') }}
                                                            </option>
                                                        </select>
                                                        <div class="search-box position-relative">
                                                            <input type="text" class="search form-control" name="search" id="search"
                                                                value="{{ request('search') ?? '' }}" placeholder="{{ trans('lang.search') }}...">
                                                            <button type="submit" class="btn-flat position-absolute">
                                                                <i class="fa fa-search"></i>
                                                            </button>
                                                            <a class="btn btn-warning btn-flat" href="{{ url('users') }}">
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
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'excel', 'model' => 'UserApp']) }}">{{ trans('lang.export_excel') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'pdf', 'model' => 'UserApp']) }}">{{ trans('lang.export_pdf') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('export.data', ['type' => 'csv', 'model' => 'UserApp']) }}">{{ trans('lang.export_csv') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('users.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.user_name') }}</th>
                                                <th>{{ trans('lang.email') }}</th>
                                                <th>{{ trans('lang.user_phone') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list12">
                                            @if (count($users) > 0)
                                                @foreach ($users as $customer)
                                                    <tr>
                                                        @can('users.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $customer->id }}" class="is_open" dataid="{{ $customer->id }}"><label class="col-3 control-label" for="is_open_{{ $customer->id }}"></label></td>
                                                        @endcan
                                                        @if (file_exists(public_path('assets/images/users' . '/' . $customer->photo_path)) && !empty($customer->photo_path))
                                                            <td><img class="rounded" style="width:50px" src="{{ asset('assets/images/users') . '/' . $customer->photo_path }}" alt="image"><a href="{{ route('users.show', ['id' => $customer->id]) }}">{{ $customer->nom }} {{ $customer->prenom }}</a></td>
                                                        @else
                                                            <td><img class="rounded" style="width:50px" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image"><a href="{{ route('users.show', ['id' => $customer->id]) }}">{{ $customer->prenom }} {{ $customer->nom }}</a></td>
                                                        @endif
                                                        <td>{{ $customer->email }}</td>
                                                        <td>{{ $customer->country_code }}{{ $customer->phone }}</td>
                                                        @if ($customer->statut == 'yes')
                                                            <td> <label class="switch"><input type="checkbox" checked id="{{ $customer->id }}" name="isActive"><span class="slider round"></span></label></td>
                                                        @else
                                                            <td><label class="switch"><input type="checkbox" id="{{ $customer->id }}" name="isActive"><span class="slider round"></span></label></td>
                                                        @endif
                                                        <td class="action-btn">
                                                            <a href="{{ route('walletstransaction.user', ['id' => $customer->id]) }}"><i class="mdi mdi-wallet" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.wallet_history') }}"></i></a>
                                                            <a href="{{ route('users.show', ['id' => $customer->id]) }}" class="" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view_details') }}"><i class="mdi mdi-eye"></i></a>
                                                            <a href="{{ route('users.edit', ['id' => $customer->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                            @can('users.delete')
                                                            <a href="{{ route('users.delete', ['id' => $customer->id]) }}" class="delete-btn" name="user-delete"  data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                            {{trans('lang.showing')}} {{ $users->firstItem() }} {{trans('lang.to_small')}} {{ $users->lastItem() }} {{trans('lang.of')}} {{ $users->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $users->links('pagination.pagination') }}
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
        $("#is_active").click(function() {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#example24 .is_open:checked').length) {
                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('user/delete', 'id') }}";
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
            console.log(id);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ url('switch') }}',
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
