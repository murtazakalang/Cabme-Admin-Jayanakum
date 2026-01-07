@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.car_model')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.car_model')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
     <div class="admin-top-section">
        <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/car.png') }}"></span>
                            <h3 class="mb-0">{{ trans('lang.car_model') }}</h3>
                            <span class="counter ml-3">{{ $totalLength }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.car_model') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.car_model_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('car-model.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.car_model_create') }}</a>
                                    </div>
                                </div>
                            </div>
                    <div class="card-body">
                        <div id="data-table_processing" class="dataTables_processing panel panel-default"
                             style="display: none;">{{trans('lang.processing')}}
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
                                <label>{{ trans('lang.search_by')}}
                                    <div class="form-group mb-0">
                                        <form action="{{ route('car-model.index') }}" method="get">
                                            <select name="selected_search" id="selected_search" class="form-control input-sm">
                                                <option value="type" {{ request('selected_search') == 'name' ? 'selected' : '' }}>{{ trans('lang.model_name') }}</option>
                                                <option value="brand" {{ request('selected_search') == 'brand' ? 'selected' : '' }}>{{ trans('lang.brand') }}</option>
                                                <option value="vehicle_type" {{ request('selected_search') == 'vehicle_type' ? 'selected' : '' }}>{{ trans('lang.vehicle_type') }}</option>
                                            </select>
                                            <div class="search-box position-relative">
                                                <input type="text" class="search form-control" name="search" id="search" value="{{ request('search') }}" placeholder="{{ trans('lang.search') }}...">
                                                <button type="submit" class="btn-flat position-absolute"><i class="fa fa-search"></i></button>
                                                <a class="btn btn-warning btn-flat" href="{{route('car-model.index')}}">{{trans('lang.clear')}}</a>
                                            </div>
                                        </form>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive m-t-10">
                            <table id="example24"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    @can('car-model.delete')
                                    <th class="delete-all"><input type="checkbox" id="is_active">
                                        <label class="col-3 control-label" for="is_active"><a id="deleteAll" href="javascript:void(0)">
                                            <i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                        </label>
                                    </th>
                                    @endcan
                                    <th>{{trans('lang.model_name')}}</th>
                                    <th>{{trans('lang.brand')}}</th>
                                    <th>{{trans('lang.vehicle_type')}}</th>
                                    <th>{{trans('lang.status')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>
                                <tbody id="append_list12">
                                @if(count($carModel) > 0)
                                    @foreach($carModel as $value)
                                    <tr>
                                        @can('car-model.delete')
                                        <td class="delete-all">
                                            <input type="checkbox" id="is_open_{{$value->id}}" class="is_open" dataid="{{$value->id}}">
                                            <label class="col-3 control-label" for="is_open_{{$value->id}}"></label>
                                        </td>
                                        @endcan
                                        <td>
                                            <a href="{{route('car-model.edit', ['id' => $value->id])}}">
                                                {{ $value->name}}
                                            </a>
                                        </td>
                                        <td>
                                            @foreach ($brand as $brandVal)
                                                @if($value->brand_id==$brandVal->id)
                                                <a href="{{route('brand.edit', ['id' => $brandVal->id])}}">{{$brandVal->name}}</a>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>@foreach ($vehicleType as $brandVal)
                                                @if($value->vehicle_type_id==$brandVal->id)
                                                    {{$brandVal->libelle}}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> @if ($value->status=="yes")
                                            <label class="switch"><input type="checkbox" id="{{$value->id}}" name="isActive"
                                                                        checked><span class="slider round"></span></label>
                                            @else
                                            <label class="switch"><input type="checkbox" id="{{$value->id}}" name="isActive"><span
                                                        class="slider round"></span></label>
                                            @endif
                                        </td>
                                        <td class="action-btn"><a href="{{route('car-model.edit', ['id' => $value->id])}}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i
                                                        class="mdi mdi-lead-pencil"></i></a>
                                        @can('car-model.delete')
                                            <a href="{{ url('car-model/delete') }}?ids={{ $value->id }}" 
                                            class="delete-btn" 
                                            data-toggle="tooltip" 
                                            data-bs-original-title="{{ trans('lang.delete') }}">
                                            <i class="mdi mdi-delete"></i>
                                            </a>
                                        @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="11" align="center">{{trans("lang.no_result")}}</td>
                                </tr>
                                @endif
                                </tbody>
                            </table>                          
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    {{trans('lang.showing')}} {{ $carModel->firstItem() }} {{trans('lang.to_small')}} {{ $carModel->lastItem() }} {{trans('lang.of')}} {{ $carModel->total() }} {{trans('lang.entries')}}
                                </div>
                                <div>
                                    {{ $carModel->links('pagination.pagination') }}
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
    $(document).ready(function () {
        $(".shadow-sm").hide();
    })
    $("#is_active").click(function () {
        $("#example24 .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function () {
        if ($('#example24 .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
              
                var arrayUsers = [];
                $('#example24 .is_open:checked').each(function () {
                    arrayUsers.push($(this).attr('dataId'));
                });
                var ids = arrayUsers.join(',');
                var url = "{{ url('car-model/delete') }}" + "?ids=" + ids;
                $(this).attr('href', url);

            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: 'carModel/switch',
            method: "POST",
            data: {'ischeck': ischeck, 'id': id},
            success: function (data) {
            },
        });
    });

</script>

@endsection
