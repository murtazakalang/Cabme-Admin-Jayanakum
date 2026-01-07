@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.on_boarding') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.on_boarding') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.on_boarding') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.on_boarding') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.on_boarding_table_text') }}</p>
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
                                            <div class="form-group mb-0">
                                                <form action="{{ route('on-boarding.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="title" {{ request('selected_search') == 'title' ? 'selected' : '' }}>
                                                            {{ trans('lang.title') }}
                                                        </option>
                                                        <option value="type" {{ request('selected_search') == 'type' ? 'selected' : '' }}>
                                                            {{ trans('lang.onboarding_type') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" 
                                                            class="search form-control" 
                                                            name="search" 
                                                            id="search" 
                                                            value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ url('on-boarding') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display  table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('lang.title') }}</th>
                                                <th>{{ trans('lang.description') }}</th>
                                                <th>{{ trans('lang.onboarding_type') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($onboarding) > 0)
                                                @foreach ($onboarding as $value)
                                                    <tr>
                                                        @if (file_exists(public_path('assets/images/onboarding' . '/' . $value->image)) && !empty($value->image))
                                                            <td><img class="rounded" style="width:50px" src="{{ asset('assets/images/onboarding') . '/' . $value->image }}" alt="image">
                                                            <a href="{{ route('on-boarding.edit', ['id' => $value->id]) }}">{{ $value->title }}</a>
                                                            </td>
                                                        @else
                                                            <td><img class="rounded" style="width:50px" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image">
                                                            <a href="{{ route('on-boarding.edit', ['id' => $value->id]) }}">{{ $value->title }}</a>
                                                            </td>
                                                        @endif
                                                        <td>
                                                            {{ $value->description }}
                                                        </td>
                                                        <td>
                                                            {{ $value->type }}
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('on-boarding.edit', ['id' => $value->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
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
                                            {{trans('lang.showing')}} {{ $onboarding->firstItem() }} {{trans('lang.to_small')}} {{ $onboarding->lastItem() }} {{trans('lang.of')}} {{ $onboarding->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $onboarding->links('pagination.pagination') }}
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
    </script>
@endsection
