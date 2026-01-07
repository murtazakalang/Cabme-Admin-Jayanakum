@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.edit_zone') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href= "{!! route('zone.index') !!}">{{ trans('lang.zone') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.edit_zone') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card pb-4">
                        <form action="{{ route('zone.update', $zone->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default"
                                    style="display: none;">
                                    {{ trans('lang.processing') }}
                                </div>
                                <div class="error_top"></div>
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="row restaurant_payout_create">
                                    <div class="restaurant_payout_create-inner">
                                    <fieldset>
                                            <legend>{{ trans('lang.edit_zone') }}</legend>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.name') }}</label>
                                                <div class="col-7">
                                                    <input type="text" class="form-control libelle" name="name"
                                                        value="{{ $zone->name }}">
                                                </div>
                                            </div>
                                            <div class="form-group row width-100">
                                                <div class="form-check">
                                                    @if ($zone->status === 'yes')
                                                        <input type="checkbox" class="user_active" id="status"
                                                            name="status" checked="checked">
                                                    @else
                                                        <input type="checkbox" class="user_active" id="status"
                                                            name="status">
                                                    @endif
                                                    <label class="col-3 control-label"
                                                        for="status">{{ trans('lang.status') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="row mt-5">
                                    <div class="col-sm-5">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h4>{{trans('lang.instructions')}}</h4>
                                                <p>{{trans('lang.instructions_help')}}</p>
                                                <p><i class="fa fa-hand-pointer-o map_icons"></i>{{trans('lang.instructions_hand_tool')}}</p>
                                                <p><i class="fa fa-plus-circle map_icons"></i>{{trans('lang.instructions_shape_tool')}}</p>
                                                <p><i class="fa fa-trash map_icons"></i>{{trans('lang.instructions_trash_tool')}}</p>
                                            </div>
                                            <div class="col-sm-12">
                                                <img src="{{asset('images/zone_info.gif')}}" alt="GIF" width="100%">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <input type="text" placeholder="{{ trans('lang.search_location') }}" id="search-box"
                                            class="form-control controls" />
                                            <div id="autocomplete-list"></div>
                                        <div id="map"></div>
                                        <p class="mt-2"><strong>{{trans('lang.note')}}:</strong>{{trans('lang.zone_draw_note_detail')}}</p>
                                    </div>
                                    <div class="col-sm-2 mapType">
                                        <ul style="list-style: none;padding:0">
                                            <li>
                                                <a id="select-button" href="javascript:void(0)"
                                                    class="btn-floating zone-add-btn btn-large waves-effect waves-light tooltipped"
                                                    title="Use this tool to drag the map and select your desired location"
                                                    onclick="
                                                        @if ($mapType == 'OSM')
                                                            console.log('Offline mode, no drawing available.');
                                                        @else
                                                            drawingManager.setDrawingMode(null);
                                                        @endif
                                                    "
                                                >
                                                    <i class="fa fa-hand-pointer-o map_icons"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <a id="add-button" href="javascript:void(0)"
                                                    class="btn-floating zone-add-btn btn-large waves-effect waves-light tooltipped"
                                                    title="Use this tool to highlight areas and connect the dots"
                                                    onclick="
                                                        @if ($mapType == 'OSM')
                                                            enablePolygonDrawing(map);
                                                        @else
                                                            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
                                                        @endif
                                                    "
                                                >
                                                    <i class="fa fa-plus-circle map_icons"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <a id="delete-all-button" href="javascript:void(0)"
                                                    class="btn-floating zone-delete-all-btn btn-large waves-effect waves-light tooltipped"
                                                    title="Use this tool to delete all selected areas"
                                                    onclick="
                                                        @if ($mapType == 'OSM')
                                                            clearMap();
                                                        @else
                                                            deleteSelectedShape();
                                                        @endif
                                                    "
                                                >
                                                    <i class="mdi mdi-delete map_icons"></i>
                                                </a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 text-center btm-btn">
                                <button type="submit" class="btn btn-primary  edit-setting-btn"><i class="fa fa-save"></i>
                                    {{ trans('lang.save') }}</button>
                                <a href="{!! route('zone.index') !!}" class="btn btn-default"><i
                                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                            </div>
                            <input type="hidden" id="coordinates" name="coordinates" value="">
                            <input type="hidden" id="area" name="area" value="">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    #map {
        height: 500px;
        width: 100%;
        position: relative;
        z-index: 0; /* Make sure the map is rendered correctly */
    }
    #panel {
        width: 200px;
        font-family: Arial, sans-serif;
        font-size: 13px;
        float: right;
        margin: 10px;
        margin-top: 100px;
    }
    #delete-button,
    #add-button,
    #delete-all-button,
    #save-button {
        margin-top: 5px;
    }
    #search-box {
        background-color: #f7f7f7;
        font-size: 15px;
        font-weight: 300;
        margin-top: 10px;
        margin-bottom: 10px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        height: 25px;
        border: 1px solid #c7c7c7;
    }
    .map_icons {
        font-size: 24px;
        color: white;
        padding: 10px;
        background-color: {{ isset($_COOKIE['admin_panel_color']) ? $_COOKIE['admin_panel_color'] : '#072750' }};
        margin: 5px;
    }
    #autocomplete-list {
        border: 1px solid #d4d4d4;
        z-index: 9999;
        position: absolute;
        background-color: white;
        cursor: pointer;
    }
    .autocomplete-item {
        padding: 10px;
        border-bottom: 1px solid #d4d4d4;
    }
    .autocomplete-item:hover {
        background-color: #e9e9e9;
    }
    .leaflet-control-custom {
        background-color: #f44336;
        border: none;
        color: white;
        padding: 10px;
        cursor: pointer;
        font-size: 16px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }
    /* Hover effect for the button */
    .leaflet-control-custom:hover {
        background-color: #d32f2f;
    }
    .leaflet-control-custom i {
        font-size: 18px;
    }
</style>
@section('scripts')
    <script type="text/javascript">
        var map;
        var drawingManager;
        var selectedShape;
        var selectedKernel;
        var gmarkers = [];
        var coordinates = [];
        var allShapes = [];
        var sendable_coordinates = [];
        var shapeColor = "#007cff";
        var kernelColor = "#000";
        var default_lat = '{{$default_lat}}';
        var default_lng = '{{$default_lng}}';
        var geopoints = '';
        var data = '{{$coordinates}}';
        let mapType = "{{ $mapType }}"; 
        let googleMapKey = "{{ $googleMapKey }}"; 
        let drawnItems = new L.FeatureGroup();
        let deleteButton ,dragMap;
        let selectedPolygon = null;
        let zones = JSON.parse(data.replace(/&quot;/g,'"'));
        var onclick='',polygon='',deletearea='';
      
        document.addEventListener("mapsLoaded", function () {
            initMap();
        });          
       
        $(document).ready(function () {          
            if (Array.isArray(zones) && zones.length > 0) {
                if (Array.isArray(zones[0])) {
                    const latLonArray = [];
                    zones[0].forEach(function (zone) {
                        if (zone.lat !== undefined && zone.lng !== undefined) {
                            latLonArray.push([zone.lat, zone.lng]); // Add [lat, lon] pair to the array
                        } else {
                            console.error("Invalid zone data: Missing lat/lng in zone object", zone);
                        }
                    });
                    document.getElementById('area').value = latLonArray.map(coord => `${coord[1]},${coord[0]}`).join(',');
                    if(mapType == "OSM"){                        
                        var coordinatesUpdated = latLonArray.map(function(coord) {
                            return {
                                lat: coord[0],  // latitude from the first element of the array
                                lon: coord[1]   // longitude from the second element of the array
                            };
                        });
                        document.getElementById('coordinates').value =JSON.stringify(coordinatesUpdated);
                    }
                    else
                    {
                        latLonArray.push(latLonArray[0]);
                        document.getElementById('coordinates').value =latLonArray;
                    }
                    default_lat = zones[0][0].lat || 0; // Fallback to 0 if not valid
                    default_lng = zones[0][0].lng || 0; // Fallback to 0 if not valid
                    geopoints = zones[0];
                    setTimeout(function(){
                        initMap();
                    },2500);
                } else {
                    console.error("Invalid zone data: Missing lat/lng in zone objects");
                }
            } else {
                console.error("Invalid zones object, missing latitude, longitude, or area");
            }
        });
        function addNewPolys(newPoly) {
            google.maps.event.addListener(newPoly, 'click', function() {
                setSelection(newPoly , 0);
            });
        }
        function setMapOnAll(map) {
            for (var i = 0; i < gmarkers.length; i++) {
                gmarkers[i].setMap(map);
            }
        }
        function clearMarkers() {
            setMapOnAll(null);
        }
        function deleteMarkers() {
            clearMarkers();
            gmarkers = [];
        }        
        function deleteSelectedShape() {
            console.log('clicked');
            if (selectedShape) {
                selectedShape.setMap(null); // removes from map
                let index = allShapes.indexOf(selectedShape);
                if (index > -1) {
                    allShapes.splice(index, 1);
                }
                selectedShape = null;
            }
            if (selectedKernel) {
                selectedKernel.setMap(null);
                selectedKernel = null;
            }

            // update hidden field
            let lat_lng = [];
            allShapes.forEach(function (poly, i) {
                lat_lng[i] = getCoordinates(poly);
            });
            document.getElementById('coordinates').value = lat_lng.length > 0 ? JSON.stringify(lat_lng) : '';
        }

        function clearMap() {
            if (allShapes.length > 0) {
                for (var i = 0; i < allShapes.length; i++) {
                    allShapes[i].setMap(null);
                }
                allShapes = [];
                deleteMarkers();
                document.getElementById('coordinates').value = null;
            }
        }
        function clearSelection() {
            if (selectedShape) {
                if (selectedShape.type !== 'marker') {
                    selectedShape.setEditable(false);
                }
                selectedShape = null;
            }
            if (selectedKernel) {
                if (selectedKernel.type !== 'marker') {
                    selectedKernel.setEditable(false);
                }
                selectedKernel = null;
            }
        }
        function setSelection(shape, check) {
            clearSelection();
            shape.setEditable(true);
            shape.setDraggable(true);
            if (check) {
                selectedKernel = shape;
            } else {
                selectedShape = shape;
            }
        }
        function getCoordinates(polygon) {
            var path = polygon.getPath();
            coordinates = [];
            for (var i = 0; i < path.length; i++) {
                coordinates.push({
                    lat: path.getAt(i).lat(),
                    lng: path.getAt(i).lng()
                });
            }
            return coordinates;
        }
        function createMarker(coord, nr, map) {
            var mesaj = "<h6>Vârf " + nr + "</h6><br>" + "Lat: " + coord.lat + "<br>" + "Lng: " + coord.lng;
            var marker = new google.maps.Marker({
                position: coord,
                map: map,
            });
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent(mesaj);
                infowindow.open(map, marker);
            });
            google.maps.event.addListener(marker, 'dblclick', function() {
                marker.setMap(null);
            });
            return marker;
        }
        function makePolygonDraggable(layer) {
            updateCoordinatesInput(layer);
            // To track mouse position and delta
            var isDragging = false;
            var startLatLng = null;
            var startLatLngs = [];
            // Mouse down event to start dragging
            layer.on('mousedown', function(e) {
                isDragging = true;
                startLatLng = e.latlng; // Store the initial mouse position in LatLng
                startLatLngs = layer.getLatLngs()[0].map(latlng => L.latLng(latlng.lat, latlng.lng));
                map.on('mousemove', onMouseMove); // Track mouse movement
                map.on('mouseup', onMouseUp); // End dragging when mouse is released
            });
            // Mouse move event to drag the polygon
            function onMouseMove(e) {
                if (isDragging) {
                    var dx = e.latlng.lng - startLatLng.lng; // Calculate change in longitude
                    var dy = e.latlng.lat - startLatLng.lat; // Calculate change in latitude
                    // Create new LatLngs by applying the change to each point
                    let newLatLngs = startLatLngs.map(latlng => L.latLng(latlng.lat + dy, latlng.lng + dx));
                    // Update the polygon's LatLngs
                    layer.setLatLngs([newLatLngs]);
                    updateCoordinatesInput(layer);
                }
            }
            // Mouse up event to stop dragging
            function onMouseUp() {
                isDragging = false;
                map.off('mousemove', onMouseMove); // Stop mousemove tracking
                map.off('mouseup', onMouseUp); // Stop mouseup tracking
            }
        }
        function updateCoordinatesInput(layer) {
            const coordinates = layer.getLatLngs()[0].map(coord => ({
                lat: parseFloat(coord.lat.toFixed(6)), 
                lon: parseFloat(coord.lng.toFixed(6))
            }));
            document.getElementById('coordinates').value = JSON.stringify(coordinates);
        }
        function createDragMapButton() {
            if(!dragMap){
                var dragMap = L.control({ position: 'topright' });
                dragMap.onAdd = function(map) {
                    var button = L.DomUtil.create('button', 'leaflet-control-custom');
                    button.innerHTML = '<i class="fa fa-hand-pointer-o"></i>'; // Using Font Awesome icon
                    // Disable map dragging when clicking the button
                    L.DomEvent.disableClickPropagation(button);
                    // Button click functionality
                    button.addEventListener('click', function(event) {
                        event.preventDefault(); // Prevent page reload
                        event.stopPropagation(); // Stop propagation of the event
                        DragMap();
                    });
                    return button; // Return the button to the control
                };
                // Add the custom button to the map
                dragMap.addTo(map);
            }
        }
        // Create the delete button once and hide it initially
        function createDeleteButton() {
            if (!deleteButton) {
                var deleteButton = L.control({ position: 'topright' });
                deleteButton.onAdd = function(map) {
                    var button = L.DomUtil.create('button', 'leaflet-control-custom');
                    button.innerHTML = '<i class="mdi mdi-delete"></i>'; // Using Font Awesome icon
                    // Disable map dragging when clicking the button
                    L.DomEvent.disableClickPropagation(button);
                    // Button click functionality
                    button.addEventListener('click', function(event) {
                        event.preventDefault(); // Prevent page reload
                        event.stopPropagation(); // Stop propagation of the event
                        deleteSelectedPolygon();
                    });
                    return button; // Return the button to the control
                };
                // Add the custom button to the map
                deleteButton.addTo(map);
            } 
        }
        function enablePolygonDrawing(map) { 
            map.dragging.disable();
            if (!drawnItems) {
                drawnItems = new L.FeatureGroup();
                map.addLayer(drawnItems);
            } 
            // Create the delete button before enabling drawing
            createDeleteButton();
            createDragMapButton();
            map.on('draw:created', function(event) {
                var layer = event.layer;  // The drawn polygon or shape
                // Add the drawn layer to the map (it is already added to the 'drawnItems' feature group)
                drawnItems.addLayer(layer);
                makePolygonDraggable(layer);
                layer.bindPopup("Drag me!").openPopup();
                // Optionally, log the coordinates of the drawn polygon to the console
                const  coordinates = layer.getLatLngs();  // Get the polygon's coordinates
                if(drawnItems.getLayers().length==1){
                    document.getElementById('coordinates').value = JSON.stringify(coordinates);
                }
            });
            map.on('click', function(event) {
                map.dragging.disable();
                var latlng = event.latlng; 
                if (selectedPolygon) {
                    // If there's already a selected polygon, deselect it
                    selectedPolygon.setStyle({ color: '#3388ff' });
                }
                drawnItems.eachLayer(function(layer) {
                    makePolygonDraggable(layer);
                    if (layer instanceof L.Polygon && layer.getBounds().contains(event.latlng)) {
                        selectedPolygon = layer;
                        layer.setStyle({ color: 'red' });
                    }
                    // Optionally, log the coordinates of the drawn polygon to the console
                    const  coordinates = layer.getLatLngs();  // Get the polygon's coordinates
                    document.getElementById('coordinates').value = JSON.stringify(coordinates);
                });
            });
        }
        function DragMap() {
            map.dragging.enable();
        }
        // Allow deletion of selected polygon
        function deleteSelectedPolygon() {
            map.dragging.disable();
            if (!selectedPolygon) {
            alert("{{trans('lang.no_polygon_selected_to_delete')}}");
            return;
            }
                drawnItems.removeLayer(selectedPolygon);
                selectedPolygon = null; 
                if(selectedPolygon == null){
                    document.getElementById('coordinates').value = '';
                }
        }
        document.getElementById('search-box').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent the autocomplete behavior on Enter key press
            }
        });
        function searchBox() {
            if (mapType == "OSM"){
                var input = document.getElementById('search-box');
                let marker , newLat , newLon;
                var autocompleteList = document.getElementById('autocomplete-list');               
                input.addEventListener('input', function() {
                    var query = this.value.trim();
                    if (query.length < 3) return; // only search after 3+ characters
                    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5`)
                        .then(response => response.json())
                        .then(data => {
                            autocompleteList.innerHTML = '';
                            data.forEach(place => {
                                var item = document.createElement('div');
                                item.classList.add('autocomplete-item');
                                item.innerText = place.display_name;
                                item.onclick = function() {
                                    input.value = place.display_name;
                                    map.setView([place.lat, place.lon], 13);
                                    // place marker
                                    if (marker) map.removeLayer(marker);
                                    marker = L.marker([place.lat, place.lon], { draggable: true }).addTo(map);
                                    // save coordinates
                                    document.getElementById('coordinates').value = JSON.stringify([{lat: place.lat, lng: place.lon}]);
                                    autocompleteList.innerHTML = '';
                                };
                                autocompleteList.appendChild(item);
                            });
                        })
                        .catch(console.error);
                });
                document.addEventListener('click', function(e) {
                    let latitude = input.dataset.latitude;
                    let longitude = input.dataset.longitude;
                    if (e.target !== input) {
                        autocompleteList.innerHTML = '';
                    }
                });
                function updateCoordinatesDisplay(lat, lon) {
                    var url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&addressdetails=1`;
                    // Fetch data from Nominatim API
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            // Display location details
                            if (data && data.address) {
                                var address = data.display_name; 
                                document.getElementById('search-box').value = address;
                            }
                        })
                        .catch(error => {
                            document.getElementById('search-box').innerHTML = "Error fetching data.";
                            console.error('Error:', error);
                        });
                }
            }
            else
            {
                var input = document.getElementById('search-box');
                var searchBox = new google.maps.places.SearchBox(input);
                map.addListener('bounds_changed', function() {
                    searchBox.setBounds(map.getBounds());
                });
                searchBox.addListener('places_changed', function() {
                    var places = searchBox.getPlaces();
                    if (places.length == 0) {
                        return;
                    }
                    // var bounds = new google.maps.LatLngBounds();
                    var bounds = new google.maps.LatLngBounds();
                    for (let i = 0; i < zones_area.length; i++) {
                        bounds.extend(zones_area[i]);
                    }
                    map.fitBounds(bounds);
                    places.forEach(function(place) {
                        if (!place.geometry) {
                            return;
                        }
                        var icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25)
                        };
                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
                var autocomplete = new google.maps.places.Autocomplete(input);             
                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();
                    if (!place.geometry) return;
                    // center map
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(15);
                    }
                    // optional marker
                    new google.maps.Marker({
                        map: map,
                        position: place.geometry.location
                    });
                    // store coordinates
                    document.getElementById('coordinates').value = JSON.stringify([{
                        lat: place.geometry.location.lat(),
                        lng: place.geometry.location.lng()
                    }]);
                });
            }
        }
        function initMap() {
            if (mapType == "OSM"){
                $(".mapType").hide();
                searchBox();
                map = L.map('map').setView([default_lat, default_lng], 10);
                map.dragging.disable();
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,  // Set maximum zoom level
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                // Create a feature group to store drawn items (polygons, lines, etc.)
                drawnItems = new L.FeatureGroup();
                map.addLayer(drawnItems);
                const AREA = document.getElementById('area').value;
                const values = AREA.split(',');
                const latLonArray = [];
                for (let i = 0; i < values.length; i += 2) {
                    const lat = parseFloat(values[i + 1]); // Latitude is the second value in the pair
                    const lon = parseFloat(values[i]);    // Longitude is the first value in the pair
                     // Validate coordinates before adding them
                    if (!isNaN(lat) && !isNaN(lon) && lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                        latLonArray.push([lat, lon]);
                    } else {
                        console.error("Invalid coordinate pair:", lat, lon); // Log the invalid pair
                    }
                }
                const cooo = latLonArray.map(coord => ({
                        lat: coord[0],
                        lon: coord[1]
                    }));
                    if(mapType == "OSM"){
                        var coordinatesString = JSON.stringify(cooo);
                        // Check if the string starts with '[[' and remove the first '[' if true
                        if (coordinatesString.startsWith('[[')) {
                            coordinatesString = coordinatesString.slice(1);  // Remove the first '['
                        }
                        if (coordinatesString.endsWith(']]')) {
                            coordinatesString = coordinatesString.slice(0, -1);  // Remove the last ']'
                        }
                        // Set the cleaned string as the value of the input field
                        document.getElementById('coordinates').value = coordinatesString; 
                    }
                    else
                    {
                        document.getElementById('coordinates').value = JSON.stringify(cooo);
                    }
                    // Create a polygon and add it to the map
                    if (latLonArray.length > 0) {
                        var polygon = L.polygon(latLonArray, { color: 'blue' }).addTo(drawnItems);
                        polygon.on('click', function () {
                            if (selectedPolygon) {
                                selectedPolygon.setStyle({ color: 'blue', weight: 3 });
                            }
                            polygon.setStyle({ color: 'red', weight: 3 });
                            selectedPolygon = polygon;
                        });
                        map.fitBounds(polygon.getBounds());
                    }
                    map.addControl(new L.Control.Draw({
                        draw: {  // Disable drawing functionality
                            polygon: true,  // Enable drawing of polygons
                            rectangle: false, // Disable rectangle drawing
                            circle: false,    // Disable circle drawing
                            marker: false,    // Disable marker drawing
                            polyline: false,  // Disable polyline drawing
                            circlemarker: false,
                        },
                        edit: {
                            featureGroup: drawnItems,  // Allow editing of drawn items
                            remove: false  // Allow removal of items
                        }
                    }));
                    map.on('draw:edited', function(event) {
                        event.layers.eachLayer(function(layer) {
                            if (layer instanceof L.Polygon  || layer instanceof L.MultiPolygon) {
                                makePolygonDraggable(layer);
                                // Get the coordinates of the polygon (all vertices)
                                let latLngs = layer.getLatLngs();
                                // Flatten the array of coordinates in case of multi-polygon
                                let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);
                                // Convert to desired format (lat, lon)
                                let convertedArray = flatLatLngs.map(function(latLng) {
                                    if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                                        if (latLng.lat >= -90 && latLng.lat <= 90 && latLng.lng >= -180 && latLng.lng <= 180) {
                                            return { lat: latLng.lat, lon: latLng.lng };
                                        }
                                        else
                                        {
                                            console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                            return null; // Avoid undefined latLngs
                                        }
                                    } else {
                                        console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                        return null; // Avoid undefined latLngs
                                    }
                                }).filter(item => item !== null); 
                                // Final array to be saved as JSON
                                let finalArray = convertedArray;
                                layer.setLatLngs(finalArray); 
                                document.getElementById('coordinates').value = JSON.stringify(finalArray);
                            }
                        });
                    }); 
                    map.on('draw:resize', function (event) {
                        var layer = event.layer;
                        if (layer instanceof L.Polygon || layer instanceof L.MultiPolygon) {
                            let latLngs = layer.getLatLngs();
                            let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);
                            let convertedArray = flatLatLngs.map(function(latLng) {
                                if (latLng instanceof L.LatLng) {
                                    latLng = latLng.wrap(); // Ensure it's a wrapped LatLng object
                                }
                                if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                                        if (latLng.lat >= -90 && latLng.lat <= 90 && latLng.lng >= -180 && latLng.lng <= 180) {
                                            return { lat: latLng.lat, lon: latLng.lng };
                                        }
                                        else
                                        {
                                            console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                            return null; // Avoid undefined latLngs
                                        }
                                } else {
                                    console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                    return null; // Avoid undefined latLngs
                                }
                                }).filter(item => item !== null); 
                                // Final array to be saved as JSON
                                let finalArray = convertedArray;
                                layer.setLatLngs(finalArray); 
                                document.getElementById('coordinates').value = JSON.stringify(finalArray);            
                        }
                    });
                    enablePolygonDrawing(map);  
            }
            else
            {
                var infowindow = new google.maps.InfoWindow({
                    size: new google.maps.Size(150, 50)
                })
                $(".mapType").show();
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 12,
                    center: new google.maps.LatLng(default_lat, default_lng),
                    mapTypeControl: false,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                        position: google.maps.ControlPosition.LEFT_CENTER
                    },
                    zoomControl: true,
                    zoomControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER
                    },
                    scaleControl: false,
                    scaleControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER
                    },
                    streetViewControl: false,
                    fullscreenControl: false
                });
                var zones = [];
                var zones_area = [];
                for (i = 0; i < geopoints.length; i++) {
                    zones_area.push({ lat: parseFloat(geopoints[i].lat), lng: parseFloat(geopoints[i].lng) });
                }
                zones.push(zones_area);
                var i;
                var polygon;
                for (i = 0; i < zones.length; i++) {                 
                    polygon = new google.maps.Polygon({
                        paths: zones[i],
                        strokeWeight: 1,
                        strokeColor:'#007cf',
                        fillColor: '#007cff',
                        fillOpacity: 0.4,
                        editable: true,   
                        draggable: true  
                    });
                    polygon.type = google.maps.drawing.OverlayType.POLYGON;
                    polygon.setMap(map);
                    addNewPolys(polygon);
                    allShapes.push(polygon);
                        google.maps.event.addListener(polygon, 'click', function(e) { getCoordinates(polygon); });
                        google.maps.event.addListener(polygon, "dragend", function(e) {
                        for (i=0; i < allShapes.length; i++) {
                            if (polygon.getPath() == allShapes[i].getPath()) {
                                allShapes.splice(i, 1);
                            }
                        }
                        allShapes.push(polygon);
                        let lat_lng = [];
                        allShapes.forEach(function(data, index) {
                            lat_lng[index] = getCoordinates(data);
                        });
                        document.getElementById('coordinates').value = JSON.stringify(lat_lng);
                    });
                    google.maps.event.addListener(polygon.getPath(), "insert_at", function(e) {
                        for (i=0; i < allShapes.length; i++) {   // Clear out the old allShapes entry
                            if (polygon.getPath() == allShapes[i].getPath()) {
                                allShapes.splice(i, 1);
                            }
                        }
                        allShapes.push(polygon);
                        let lat_lng = [];
                        allShapes.forEach(function(data, index) {
                            lat_lng[index] = getCoordinates(data);
                        });
                        document.getElementById('coordinates').value = JSON.stringify(lat_lng);
                    });
                    google.maps.event.addListener(polygon.getPath(), "remove_at", function(e) { getCoordinates(polygon); });
                    google.maps.event.addListener(polygon.getPath(), "set_at", function(e) { getCoordinates(polygon); });
                }
                let lat_lng = [];
                allShapes.forEach(function(data, index) {
                    lat_lng[index] = getCoordinates(data);
                });
                document.getElementById('coordinates').value = JSON.stringify(lat_lng);
                searchBox();
                var shapeOptions = {
                    strokeWeight: 1,
                    fillOpacity: 0.4,
                    editable: true,
                    draggable: true
                };
               
                var shapeOptions = {
                    strokeWeight: 1,
                    fillOpacity: 0.4,
                    editable: true,
                    draggable: true
                };

                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: null, // no active tool until user clicks button
                    drawingControl: false, // we use custom buttons
                    polygonOptions: shapeOptions
                });
                drawingManager.setMap(map);
                google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
                    if (e.type !== google.maps.drawing.OverlayType.POLYGON) return;
                  
                    allShapes.forEach(p => p.setMap(null));
                    allShapes = [];

                    var newShape = e.overlay;
                    newShape.type = e.type;
                    allShapes.push(newShape);

                    updatePolygonCoordinates();
                    newShape.setOptions({ fillColor: shapeColor });

                    drawingManager.setDrawingMode(null);
                    setSelection(newShape, 0);                   
                    google.maps.event.addListener(newShape, "dragend", function () {
                        updatePolygonCoordinates();
                    });

                    google.maps.event.addListener(newShape.getPath(), "insert_at", function () {
                        updatePolygonCoordinates();
                    });

                    google.maps.event.addListener(newShape.getPath(), "remove_at", function () {
                        updatePolygonCoordinates();
                    });

                    google.maps.event.addListener(newShape.getPath(), "set_at", function () {
                        updatePolygonCoordinates();
                    });
                });               


                google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
                google.maps.event.addListener(map, 'click', clearSelection); 
            }  
        }
        function updatePolygonCoordinates() {
            let lat_lng = [];
            allShapes.forEach(function (polygon, index) {
                lat_lng[index] = getCoordinates(polygon);
            });
            document.getElementById('coordinates').value = JSON.stringify(lat_lng);
        }        
    </script>
@endsection

