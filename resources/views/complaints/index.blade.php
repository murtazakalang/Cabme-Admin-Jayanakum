@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.complaints') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ trans('lang.complaints') }}
                    </li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.complaints') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.complaints') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.complaints_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="userlist-topsearch d-flex mb-3">
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
                                    <div id="users-table_filter" class="ml-auto">
                                        <label>{{ trans('lang.search_by') }}
                                            <div class="form-group">
                                                <form action="{{ route('complaints.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="title" {{ request('selected_search') == 'title' ? 'selected' : '' }}>
                                                            {{ trans('lang.title') }}
                                                        </option>
                                                        <option value="status" {{ request('selected_search') == 'status' ? 'selected' : '' }}>
                                                            {{ trans('lang.status') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }}" 
                                                            style="{{ request('selected_search') == 'status' ? 'display:none' : '' }}"
                                                            placeholder="{{ trans('lang.search') }}..."
                                                        >
                                                        <select id="status" class="search form-control" name="status" style="{{ request('selected_search') == 'status' ? '' : 'display:none' }}">
                                                            <option value="initiated" {{ request('status') == 'initiated' ? 'selected' : '' }}>
                                                                {{ trans('lang.initiated') }}
                                                            </option>
                                                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                                                {{ trans('lang.processing') }}
                                                            </option>
                                                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                                                {{ trans('lang.completed') }}
                                                            </option>
                                                        </select>
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ url('complaints') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('complaints.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan 
                                                <th>{{ trans('lang.order_id') }}</th>
                                                <th>{{ trans('lang.order_type') }}</th>
                                                <th>{{ trans('lang.driver_name') }}</th>
                                                <th>{{ trans('lang.userName') }}</th>
                                                <th>{{ trans('lang.title') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.created') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($complaints) > 0)
                                                @foreach ($complaints as $complaint)
                                                    <tr>
                                                        @can('complaints.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $complaint->id }}" class="is_open" dataid="{{ $complaint->id }}"><label class="col-3 control-label" for="is_open_{{ $complaint->id }}"></label>
                                                        </td>
                                                        @endcan 
                                                        <td>
                                                            @if($complaint->booking_type == "ride")
                                                                <a href="{{ route('ride.show', ['id' => $complaint->booking_id]) }}">{{ $complaint->booking_id }}</a>
                                                            @elseif($complaint->booking_type == "parcel")
                                                                <a href="{{ route('parcel.show', ['id' => $complaint->booking_id]) }}">{{ $complaint->booking_id }}</a>
                                                            @elseif($complaint->booking_type == "rental")
                                                                <a href="{{ route('rental-orders.show', ['id' => $complaint->booking_id]) }}">{{ $complaint->booking_id }}</a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ ucfirst($complaint->booking_type) }}
                                                        </td>
                                                        <td>
                                                            @if($complaint->driver)
                                                                <a href="{{ route('driver.show', ['id' => $complaint->driver->id]) }}">
                                                                    {{ $complaint->driver->prenom }} {{ $complaint->driver->nom }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($complaint->user)
                                                                <a href="{{ route('users.show', ['id' => $complaint->user->id]) }}">
                                                                    {{ $complaint->user->prenom }} {{ $complaint->user->nom }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $complaint->title }}</td>
                                                        <td>
                                                            @if ($complaint->status == 'completed')
                                                                <span class="badge badge-success">{{ $complaint->status }}</span>
                                                            @elseif($complaint->status == 'processing')
                                                                <span class="badge badge-warning"> {{ $complaint->status }}</span>
                                                            @elseif($complaint->status == 'initiated')
                                                                <span class="badge badge-primary"> {{ $complaint->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                           <span class="date">{{ date('d F Y', strtotime($complaint->created_at)) }}</span>
                                                            <span class="time">{{ date('h:i A', strtotime($complaint->created_at)) }}</span>
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="javascript:void(0)" id="{{ $complaint->id }}" class="complaint-show" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view_details') }}"><i class="mdi mdi-eye"></i></a>
                                                            @can('complaints.delete')
                                                            <a class="delete-btn" name="user-delete" href="{{ route('complaints.delete', ['id' => $complaint->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                            {{trans('lang.showing')}} {{ $complaints->firstItem() }} {{trans('lang.to_small')}} {{ $complaints->lastItem() }} {{trans('lang.of')}} {{ $complaints->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $complaints->links('pagination.pagination') }}
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
    <div class="modal fade" id="showComplaintModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.complaint_detail') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('complaints.update') }}" method="post" class="">
                        @csrf
                        <div class="form-row">
                            <div class="form-group row">
                                <input type="text" name="complaint_id" id="complaint_id" hidden>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.title') }}</label>
                                    <div class="col-12 title">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.message') }}</label>
                                    <div class="col-12 message">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.status') }}</label>
                                    <div class="col-12">
                                        @php
                                            $status = ['initiated' => 'Initiated', 'processing' => 'Processing', 'completed' => 'Completed'];
                                        @endphp
                                        <select name="complaint_status" class="form-control" class="status" id="complaint_status">
                                            @foreach ($status as $key => $value)
                                                <option value="{{ $key }}"> {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary edit-form-btn" id="add-wallet-btn">{{ trans('submit') }}</a>
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close">
                                {{ trans('close') }}</a>
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
        $(document).ready(function() {
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
                    var url = "{{ url('complaints/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert('{{ trans('lang.select_delete_alert') }}');
            }
        });
        $('.complaint-show').on('click', function() {
            var id = this.id;
            var url = "{{ url('complaints/show', 'id') }}";
            url = url.replace('id', id);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('.title').text(data.title);
                    $('.message').text(data.description);
                    $('#complaint_status [value="' + data.status + '"]').attr('selected', 'true');
                    $('#complaint_id').val(id);
                    $('#showComplaintModal').modal('show');
                },
            })
        })
        $(document).ready(function() {
            if ($('#selected_search').val() == "status") {
                jQuery('#search').val('');
            } else {
                jQuery('#status').val('');
            }
        })
        $(document.body).on('change', '#selected_search', function() {
            if (jQuery(this).val() == 'status') {
                jQuery('#status').show();
                jQuery('#status').val('initiated');
                jQuery('#search').val('');
                jQuery('#search').hide();
            } else {
                jQuery('#status').hide();
                jQuery('#status').val('');
                jQuery('#search').show();
            }
        });
    </script>
@endsection
