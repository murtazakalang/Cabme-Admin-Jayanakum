@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.create_role') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('roles') }}">{{ trans('lang.role_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.create_role') }}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('roles.store') }}" method="post" id="roleForm">
                @csrf
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.role_details') }}</legend>
                            <div class="form-group row width-100 d-flex">
                                <label class="col-3 control-label">{{ trans('lang.name') }}</label>
                                <div class="col-6">
                                    <input type="text" class="form-control" id="name" name="name">
                                </div>
                                <div class="col-6 text-right">
                                    <label for="permissions" class="form-label">{{ trans('lang.assign_permissions') }}</label>
                                    <div class="text-right">
                                        <input type="checkbox" name="all_permission" id="all_permission">
                                        <label class="control-label" for="all_permission">{{ trans('lang.all_permissions') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <div class="role-table width-100">
                                    <div class="col-12">
                                        <table class="table table-striped">
                                            <thead>
                                                <th>Menu</th>
                                                <th>Permission</th>
                                            </thead>
                                            <tbody>
                                                @foreach($menus as $menu => $actions)
                                                <tr>
                                                    <td><strong>{{ Str::title(str_replace(['-', '_', '.'], ' ', $menu)) }}</strong></td>
                                                    <td>
                                                        @foreach(['list','create','edit','delete','view'] as $act)
                                                            @if(isset($actions[$act]))
                                                                @php
                                                                    $permName = $actions[$act];
                                                                    $checked = isset($rolePermissions) ? in_array($permName, $rolePermissions) : false;
                                                                @endphp

                                                                <input type="checkbox" name="permissions[]" value="{{ $permName }}" id="{{ $menu.'_'.$act }}" {{ $checked ? 'checked' : '' }} class="permission">
                                                                <label for="{{ $menu.'_'.$act }}">{{ $labelMap[$act] }}</label>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="submit" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i>
                    {{ trans('lang.save') }}
                </button>
                <a href="{{ route('roles.index') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
            </div>
            <form>
        </div>
    @endsection

    @section('scripts')
        <script>
            $('#all_permission').on('click', function() {
                if ($(this).is(':checked')) {
                    $.each($('.permission'), function() {
                        $(this).prop('checked', true);
                    });
                } else {
                    $.each($('.permission'), function() {
                        $(this).prop('checked', false);
                    });
                }
            });
        </script>

    @endsection
