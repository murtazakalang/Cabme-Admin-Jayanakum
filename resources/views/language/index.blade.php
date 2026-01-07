@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.language') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">languages</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/language.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.language') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.language') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.language_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('language.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.language_create') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{ trans('lang.processing') }}
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
                                                <form action="{{ route('language.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="language" {{ request('selected_search') == 'language' ? 'selected' : '' }}>
                                                            {{ trans('lang.language') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" class="search form-control" name="search" id="search"
                                                            value="{{ request('search') ?? '' }}" placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ route('language.index') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <div class="error_top"></div>
                                    @if ($errors->any())
                                        <div class="alert alert-danger" style="display:none;">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('lang.language') }}</th>
                                                <th>{{ trans('lang.code') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list12">
                                            @foreach ($language as $customer)
                                                <tr>
                                                    @if (file_exists(public_path('assets/images/flags' . '/' . $customer->flag)) && !empty($customer->flag))
                                                        <td><img class="rounded" style="width:50px" src="{{ asset('assets/images/flags') . '/' . $customer->flag }}" alt="image">{{ $customer->language }}</td>
                                                    @else
                                                        <td><img class="rounded" style="width:50px" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image">{{ $customer->language }}</td>
                                                    @endif
                                                    <td>{{ $customer->code }}</td>
                                                    <td>
                                                        @if ($customer->status == 'true')
                                                            <label class="switch"><input type="checkbox" checked id="{{ $customer->id }}" name="isSwitch"><span class="slider round"></span></label>
                                                        @else
                                                            <label class="switch"><input type="checkbox" id="{{ $customer->id }}" name="isSwitch"><span class="slider round"></span></label>
                                                        @endif
                                                    </td>
                                                    <td class="action-btn"><a href="{{ route('language.edit', ['id' => $customer->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                    @can('language.delete')
                                                    <a id="'+val.id+'" class="delete-btn" name="user-delete" href="{{ route('language.delete', ['id' => $customer->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            {{trans('lang.showing')}} {{ $language->firstItem() }} {{trans('lang.to_small')}} {{ $language->lastItem() }} {{trans('lang.of')}} {{ $language->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $language->links('pagination.pagination') }}
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
        var status = $("input[name='isSwitch']").val();
        $(document).on("click", "input[name='isSwitch']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            var url = "{{ route('language.switch') }}";
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                method: "POST",
                data: {
                    'ischeck': ischeck,
                    'id': id
                },
                success: function(data) {
                    console.log(data.error);
                    if (data.error) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").after("<div class='alert alert-danger'><ul><li>" + data.error + "</li></ul></div>");
                    }
                    window.location.reload();
                },
                error: function(response) {
                },
            });
        });
    </script>
@endsection
