@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.cms_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ trans('lang.cms_plural') }}
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
                                <span class="icon mr-3"><img src="{{ asset('images/cms.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.cms_plural') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.cms_plural') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.cms_plural_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('cms.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_page') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                                    {{ trans('lang.processing') }}
                                </div>
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
                                            <div class="form-group mb-0">
                                                <form action="{{ route('cms.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="cms_name" {{ request('selected_search') == 'cms_name' ? 'selected' : '' }}>
                                                            {{ trans('Name') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ url('cms') }}">
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
                                                @can('cms.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                                @endcan
                                                <th>{{ trans('lang.page_name') }}</th>
                                                <th>{{ trans('lang.page_slug') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list12">
                                            @if (count($cmss) > 0)
                                                @foreach ($cmss as $cmss12)
                                                    <tr>
                                                        @can('cms.delete')
                                                        <td class="delete-all"><input type="checkbox" id="is_open_{{ $cmss12->cms_id }}" class="is_open" dataid="{{ $cmss12->cms_id }}"><label class="col-3 control-label" for="is_open_{{ $cmss12->cms_id }}"></label></td>
                                                        @endcan
                                                        <td>{{ $cmss12->cms_name }}</td>
                                                        <td>{{ $cmss12->cms_slug }}</td>
                                                        <td>
                                                            @if ($cmss12->cms_status == '1')
                                                                <label class="switch"><input type="checkbox" checked id="{{ $cmss12->cms_id }}" name="isActive"><span class="slider round"></span></label>
                                                            @elseif ($cmss12->cms_status == 'on')
                                                                <label class="switch"><input type="checkbox" checked id="{{ $cmss12->cms_id }}" name="isActive"><span class="slider round"></span></label>
                                                            @elseif ($cmss12->cms_status == 'yes')
                                                                <label class="switch"><input type="checkbox" checked id="{{ $cmss12->cms_id }}" name="isActive"><span class="slider round"></span></label>
                                                            @elseif ($cmss12->cms_status == 'Publish')
                                                                <label class="switch"><input type="checkbox" checked id="{{ $cmss12->cms_id }}" name="isActive"><span class="slider round"></span></label>
                                                            @elseif ($cmss12->cms_status == '0')
                                                                <label class="switch"><input type="checkbox" id="{{ $cmss12->cms_id }}" name="isActive"><span class="slider round"></span></label>
                                                            @else
                                                                <label class="switch"><input type="checkbox" id="{{ $cmss12->cms_id }}" name="isActive"><span class="slider round"></span></label>
                                                            @endif
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('cms.edit', ['id' => $cmss12->cms_id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                            @can('cms.delete')
                                                            <a id="'+val.id+'" class="delete-btn" name="user-delete" href="{{ route('cms.delete', ['id' => $cmss12->cms_id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
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
                                        {{trans('lang.showing')}} {{ $cmss->firstItem() }} {{trans('lang.to_small')}} {{ $cmss->lastItem() }} {{trans('lang.of')}} {{ $cmss->total() }} {{trans('lang.entries')}}
                                    </div>
                                    <div>
                                        {{ $cmss->links('pagination.pagination') }}
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
        $("#deleteAll").click(function() {
            if ($('#example24 .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    var arrayUsers = [];
                    $('#example24 .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('cms/destroycms', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
        $(document).on("click", "input[name='isActive']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'cms/switch',
                method: "POST",
                data: {
                    'ischeck': ischeck,
                    'id': id
                },
                success: function(data) {
                },
            });
        });
    </script>
@endsection
