@extends('layouts.app')
@section('content')
<div class="page-wrapper ridedetail-page">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center"> 
            <h3 class="text-themecolor">{{trans('lang.sos_detail')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{!! url('/dashboard') !!}">{{trans('lang.home')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{!! route('sos.index') !!}">{{trans('lang.sos')}}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{trans('lang.sos_detail')}}
                </li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="post" action="{{ route('sos.update',$sos->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method("PUT")
                    <div class="card">
                        <div class="card-body p-0 pb-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header bb-2 border-primary">
                                            <h3 class="box-title">{{trans('lang.map_view')}}</h3>
                                        </div>
                                        <div class="box-body">
                                            <div id="map" style="height:300px">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="user-top">
                                <div class="row align-items-center">
                                    <div class="user-title col-md-8">
                                        <h4 class="card-title"> {{trans('lang.details_of_sos')}} </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="user-detail taxi-detail" role="tabpanel">
                                <ul class="nav nav-tabs">
                                    <li role="presentation" class="">
                                        <a href="#user" aria-controls="information" role="tab" data-toggle="tab"
                                           class="{{ (Request::get('tab') == 'user' || Request::get('tab') == '') ? 'active show' : '' }}">{{trans('lang.user')}}</a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#driver" aria-controls="driver" role="tab" data-toggle="tab"
                                           class="{{ (Request::get('tab') == 'driver') ? 'active show' : '' }}}}">{{trans('lang.driver')}}</a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#rides" aria-controls="rides" role="tab" data-toggle="tab"
                                           class="{{ (Request::get('tab') == 'rides') ? 'active show' : '' }}">{{trans('lang.ride')}}</a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#sos" aria-controls="sos" role="tab" data-toggle="tab"
                                           class="{{ (Request::get('tab') == 'sos') ? 'active show' : '' }}">{{trans('lang.sos')}}</a>
                                    </li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel"
                                         class="tab-pane {{ (Request::get('tab') == 'user' || Request::get('tab') == '') ? 'active' : '' }}"
                                         id="user">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.user_name')}}:</label>
                                                           <a href="{{ route('users.show',['id'=>$sos->userID]) }}"><span>{{ $sos->userFirstNom}} {{ $sos->userNom}}  </span></a>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.user_phone')}}:</label>
                                                    <span>{{ $sos->user_phone}}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.Image')}}:</label>
                                                    <span>
												@if (file_exists(public_path('assets/images/users'.'/'.$sos->user_photo)) && !empty($sos->user_photo))
													<img class="rounded" style="width:50px"
                                                         src="{{asset('assets/images/users').'/'.$sos->user_photo}}"
                                                         alt="image">
												@else
													<img class="rounded" style="width:50px"
                                                         src="{{asset('assets/images/placeholder_image.jpg')}}"
                                                         alt="image">
												@endif
												</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel"
                                         class="tab-pane {{ Request::get('tab') == 'driver' ? 'active' : '' }}"
                                         id="driver">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.driver_name')}}:</label>
                                                    <a href="{{ route('driver.show',['id'=>$sos->driverID]) }}"><span>{{ $sos->driverPreNom}} {{ $sos->driverNom}}</span></a>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.driver_phone')}}:</label>
                                                    <span>{{ $sos->driver_phone}}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.Image')}}:</label>
                                                    <span>	@if (file_exists(public_path('assets/images/driver'.'/'.$sos->driver_photo)) && !empty($sos->driver_photo))
														<img class="rounded" style="width:50px"
                                                             src="{{asset('assets/images/driver').'/'.$sos->driver_photo}}"
                                                             alt="image">
													@else
													<img class="rounded" style="width:50px"
                                                         src="{{asset('assets/images/placeholder_image.jpg')}}"
                                                         alt="image">
													@endif</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel"
                                         class="tab-pane {{ Request::get('tab') == 'rides' ? 'active' : '' }}"
                                         id="rides">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.depart')}}:</label>
                                                    <span>{{ $sos->depart_name}}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label for=""
                                                           class="font-weight-bold">{{trans('lang.destination')}}:</label>
                                                    <span>{{ $sos->destination_name}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel"
                                         class="tab-pane {{ Request::get('tab') == 'sos' ? 'active' : '' }}"
                                         id="sos">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label class="font-weight-bold">{{trans('lang.status')}}:</label>
                                                    @php
                                                    $status = ['initiated' => 'Initiated', 'completed' => 'Completed', 'processing' => 'Processing']
                                                    @endphp
                                                    <select name="order_status" class="form-control mb-3">
                                                        @foreach ($status as $key => $value)
                                                            <option value="{{ $key }}" {{ ( $key== $sos->status) ? 'selected' : '' }}> {{ $value }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="text-right">
                                                        <button type="submit" class="btn btn-primary edit-form-btn">
                                                            <i class="fa fa-save"></i> {{trans('lang.update')}}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label class="font-weight-bold">{{trans('lang.created_at')}}:</label>
                                                    <span class="date">{{ date('d F Y',strtotime($sos->creer))}}</span>
                                                    <span class="time">{{ date('h:i A',strtotime($sos->creer))}}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-group">
                                                    <label class="font-weight-bold">{{trans('lang.sos_location')}}:</label>
                                                    <span id="sos_location"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-12 text-center btm-btn" >
                                        <a href="{!! route('sos.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/leaflet/leaflet.css') }}" />

@endsection
@section('scripts')

<script src="{{ asset('js/leaflet/leaflet.js')}}"></script>
<script src="{{ asset('js/leaflet/leaflet-routing-machine.js')}}"></script>

<script type="text/javascript">

    var latitude_depart = '{{ $sos->latitude_depart }}';
    var longitude_depart = '{{ $sos->longitude_depart}}';
    var latitude = '{{ $sos->latitude }}';
    var longitude = '{{ $sos->longitude }}';
    var latitude_arrivee = '{{ $sos->latitude_arrivee }}';
    var longitude_arrivee = '{{ $sos->longitude_arrivee }}';
    var destination_name =  '{{ $sos->destination_name }}'; 
    let mapType = "{{ $mapType }}"; 
    
    var lat = parseFloat(latitude);
    var lng = parseFloat(longitude);
    
    var locations = [
        ['origin', latitude_depart, longitude_depart, 1],
        ['sos', lat, lng, 2],
        ['des', latitude_arrivee, longitude_arrivee, 3],
    ];

    $(document).ready(function () {
    // document.addEventListener("mapsLoaded", function () {
        initialize();
    });

    function initialize() {

        if(mapType == "OSM"){
            var map = L.map('map').setView([lat, lng], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            var locationsOSM = [
                ['origin', latitude_depart, longitude_depart],
                ['sos', lat, lng],
                ['des', latitude_arrivee, longitude_arrivee],
            ];
            for (var i = 0; i < locationsOSM.length; i++) {
                var marker = L.marker([locationsOSM[i][1], locationsOSM[i][2]]).addTo(map);
                marker.bindPopup(locationsOSM[i][0]);
                if (i == 0) originMarker = marker;
                if (i == locationsOSM.length - 1) destinationMarker = marker;
            }
            L.Routing.control({
                waypoints: [
                    L.latLng(latitude_depart, longitude_depart),
                    L.latLng(lat, lng),
                    L.latLng(latitude_arrivee, longitude_arrivee)
                ],
                routeWhileDragging: true,
                draggableWaypoints: false,
                addWaypoints: false
            }).addTo(map);

            getLocationName(lat, lng, function(name) {
                if(name){
                    $('#sos_location').html(name);
                }else{
                    $('#sos_location').html(destination_name);
                }
            });

        } else {

            var directionsService = new google.maps.DirectionsService();
            var directionsDisplay = new google.maps.DirectionsRenderer();

            var latlng = new google.maps.LatLng(lat, lng);
            var geocoder = geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        $('#sos_location').html(results[1].formatted_address)
                    }
                }
            });
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 6,
                center: new google.maps.LatLng(21.7679, 78.8718),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            directionsDisplay.setMap(map);
            var infowindow = new google.maps.InfoWindow();
            var marker, i;
            var request = {
                travelMode: google.maps.TravelMode.DRIVING
            };
            for (i = 0; i < locations.length; i++) {
                marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                });
                google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infowindow.setContent(locations[i][0]);
                    infowindow.open(map, marker);
                }
                })(marker, i));
                if (i == 0) request.origin = marker.getPosition();
                else if (i == locations.length - 1) request.destination = marker.getPosition();
                else {
                if (!request.waypoints) request.waypoints = [];
                request.waypoints.push({
                    location: marker.getPosition(),
                    stopover: true
                });
                }
            }
            directionsService.route(request, function(result, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
                }
            });

        }
    }

    function getLocationName(lat, lng, callback) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    callback(data.display_name);
                } else {
                    callback("Unknown location");
                }
            })
            .catch(err => {
                console.error(err);
                callback("Error getting location");
            });
    }


</script>

@endsection
