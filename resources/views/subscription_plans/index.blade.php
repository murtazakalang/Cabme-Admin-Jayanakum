@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">{{ trans('lang.subscription_plans') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.subscription_plans') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.subscription_plans') }}</h3>
                                <span class="counter ml-3 zone_count">{{ $totalLength }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if (count($overviewPlans) > 0)
                <div class="overview-sec">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header d-flex justify-content-between align-items-center border-0">
                                    <div class="card-header-title">
                                        <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.overview') }}</h3>
                                        <p class="mb-0 text-dark-2">{{ trans('lang.see_overview_of_package_earning') }}</p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row subscription-list">
                                        @foreach ($overviewPlans as $data)
                                            <div class="col-md-4">
                                                <div class="card card-box-with-icon">
                                                    <div class="card-body">
                                                        <span class="box-icon"><img src="{{ asset('assets/images/subscription') . '/' . $data->image }}"></span>
                                                        <div class="card-box-with-content mt-3">
                                                            <h4 class="text-dark-2 mb-1 h4 ">
                                                                @if (!empty($data->total_earning))
                                                                    @php $totalEarning=$data->total_earning;@endphp
                                                                @else
                                                                    @php $totalEarning=0;@endphp
                                                                @endif
                                                                @if ($currency->symbol_at_right == 'true')
                                                                    {{ number_format($totalEarning, $currency->decimal_digit) . '' . $currency->symbole }}
                                                                @else
                                                                    {{ $currency->symbole . '' . number_format($totalEarning, $currency->decimal_digit) }}
                                                                @endif
                                                            </h4>
                                                            <p class="mb-0 text-dark-2">{{ $data->name }} ({{ ucfirst($data->plan_for) }})</p>
                                                        </div>
                                                        <span class="background-img"><img src="{{ asset('assets/images/subscription') . '/' . $data->image }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.subscription_plans') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.subscription_plans_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('subscription-plans.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_subscription_plan') }}</a>
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
                                                <form action="{{ route('subscription-plans.index') }}" method="get">
                                                    <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                        <option value="name" {{ request('selected_search') == 'name' ? 'selected' : '' }}>
                                                            {{ trans('lang.plan_name') }}
                                                        </option>
                                                        <option value="price" {{ request('selected_search') == 'price' ? 'selected' : '' }}>
                                                            {{ trans('lang.price') }}
                                                        </option>
                                                    </select>
                                                    <div class="search-box position-relative">
                                                        <input type="text" 
                                                            class="search form-control" 
                                                            name="search" 
                                                            id="search" 
                                                            value="{{ request('search', '') }}"
                                                            placeholder="{{ trans('lang.search') }}...">
                                                        <button type="submit" class="btn-flat position-absolute">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <a class="btn btn-warning btn-flat" href="{{ route('subscription-plans.index') }}">
                                                            {{ trans('lang.clear') }}
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="subscriptionPlansTable" class="display nowrap table table-hover table-striped table-bordered table table-striped dataTable no-footer dtr-inline collapsed" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @can('subscription-plans.delete')
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i>
                                                            {{ trans('lang.all') }}</a></label>
                                                </th>
                                                @endcan
                                                <th>{{ trans('lang.plan_name') }}</th>
                                                <th>{{ trans('lang.plan_for') }}</th>
                                                <th>{{ trans('lang.plan_price') }}</th>
                                                <th>{{ trans('lang.duration') }}</th>
                                                <th>{{ trans('lang.current_subscriber') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                            @if (count($subscriptionPlans) > 0)
                                                @foreach ($subscriptionPlans as $value)
                                                    <tr>
                                                        @can('subscription-plans.delete')
                                                        <td class="delete-all">
                                                            @if (intval($value->id) != 1)
                                                                <input type="checkbox" id="is_open_{{ $value->id }}" class="is_open" dataid="{{ $value->id }}"><label class="col-3 control-label" for="is_open_{{ $value->id }}"></label>
                                                            @endif
                                                        </td>
                                                        @endcan
                                                        @if (file_exists(public_path('assets/images/subscription' . '/' . $value->image)) && !empty($value->image))
                                                            <td><img class="rounded" width="50px" src="{{ asset('assets/images/subscription') . '/' . $value->image }}" alt="image">
                                                            <a href="{{ route('subscription-plans.edit', ['id' => $value->id]) }}">{{ $value->name }}</a>
                                                            </td>
                                                        @else
                                                            <td><img class="rounded" width="50px" src="{{ asset('assets/images/placeholder_image.jpg') }}" alt="image">
                                                            <a href="{{ route('subscription-plans.edit', ['id' => $value->id]) }}">{{ $value->name }}</a>
                                                            </td>
                                                        @endif
                                                        <td>
                                                            @if($value->id == 1)
                                                                {{ trans('lang.driver_or_owner') }}
                                                            @else
                                                                {{ ucfirst($value->plan_for) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (intVal($value->price != 0))
                                                                @if ($currency->symbol_at_right == 'true')
                                                                    {{ number_format($value->price, $currency->decimal_digit) . '' . $currency->symbole }}
                                                                @else
                                                                    {{ $currency->symbole . '' . number_format($value->price, $currency->decimal_digit) }}
                                                                @endif
                                                            @else
                                                                {{ trans('lang.free') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($value->expiryDay == '-1')
                                                                {{ trans('lang.unlimited') }}
                                                            @else
                                                                {{ $value->expiryDay }} {{ trans('lang.days') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($value->subscribers_count > 0)
                                                                <a href="{{ route('current-subscriber.list', ['id' => $value->id]) }}">{{ $value->subscribers_count }}</a>
                                                            @else
                                                               {{ $value->subscribers_count }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (intval($value->id) != 1)
                                                                @if ($value->isEnable == 'true')
                                                                    <label class="switch"><input type="checkbox" id="{{ $value->id }}" name="isActive" checked><span class="slider round"></span></label>
                                                                @else
                                                                    <label class="switch"><input type="checkbox" id="{{ $value->id }}" name="isActive"><span class="slider round"></span></label>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="action-btn">
                                                            <a href="{{ route('subscription-plans.edit', ['id' => $value->id]) }}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                            @can('subscription-plans.delete')
                                                            @if (intval($value->id) != 1)
                                                                <a href="{{ route('subscription-plans.delete', ['id' => $value->id]) }}" class="delete-btn" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>
                                                            @endif
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
                                            {{trans('lang.showing')}} {{ $subscriptionPlans->firstItem() }} {{trans('lang.to_small')}} {{ $subscriptionPlans->lastItem() }} {{trans('lang.of')}} {{ $subscriptionPlans->total() }} {{trans('lang.entries')}}
                                        </div>
                                        <div>
                                            {{ $subscriptionPlans->links('pagination.pagination') }}
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
    <script>
        $("#is_active").click(function() {
            $("#subscriptionPlansTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#subscriptionPlansTable .is_open:checked').length) {
                if (confirm('{{ trans('lang.selected_delete_alert') }}')) {
                    var arrayUsers = [];
                    $('#subscriptionPlansTable .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        arrayUsers.push(dataId);
                    });
                    arrayUsers = JSON.stringify(arrayUsers);
                    var url = "{{ url('subscription-plans/delete', 'id') }}";
                    url = url.replace('id', arrayUsers);
                    $(this).attr('href', url);
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
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
                url: 'subscription-plans-switch',
                method: "POST",
                data: {
                    'ischeck': ischeck,
                    'id': id
                },
                success: function(response) {
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message);
                    location.reload();
                }
            });
        });
    </script>
@endsection
