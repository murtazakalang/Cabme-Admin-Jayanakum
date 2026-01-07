@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.create_zone') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href= "{!! route('zone.index') !!}">{{ trans('lang.zone') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.create_zone') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
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
                    <div class="card pb-4">
                        <form action="{{ route('zone.store') }}" method="post" enctype="multipart/form-data"
                            id="create_zone">
                            @csrf
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default"
                                    style="display: none;">
                                    {{ trans('lang.processing') }}
                                </div>
                                <div class="row restaurant_payout_create">
                                    <div class="restaurant_payout_create-inner">
                                        <fieldset>
                                            <legend>{{ trans('lang.create_zone') }}</legend>
                                            <div class="form-group row width-50">
                                                <label class="col-3 control-label">{{ trans('lang.name') }}</label>
                                                <div class="col-7">
                                                    <input type="text" class="form-control name" name="name">
                                                </div>
                                            </div>
                                            <div class="form-group row width-100">
                                                <div class="form-check">
                                                    <input type="checkbox" class="user_active" id="status"
                                                        name="status">
                                                    <label class="col-3 control-label"
                                                        for="status">{{ trans('lang.status') }}</label>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
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
                                <button type="submit" class="btn btn-primary  save-setting-btn"><i
                                        class="fa fa-save"></i>{{ trans('lang.save') }}</button>
                                <a href="{!! route('zone.index') !!}" class="btn btn-default"><i
                                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                            </div>
                            <input type="hidden" id="coordinates" name="coordinates" value="">
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
    let polygonPath; 
    let drawnItems;
    let deleteButton = null, dragMap = null;
    let selectedPolygon = null;
    let mapType = "{{ $mapType }}"; 
    let googleMapKey = "{{ $googleMapKey }}"; 
    
    var onclick='',polygon='',deletearea='';   
    $(document).ready(function () {
        initMap();
    });
    
    var allShapes = [];
    var sendable_coordinates = [];
    var shapeColor = "#007cff";
    var kernelColor = "#000";
    
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
        if (selectedShape) {
            selectedShape.setMap(null);
            var index = allShapes.indexOf(selectedShape);
            if (index > -1) {
                allShapes.splice(index, 1);
            }
        }
        if (selectedKernel) {
            selectedKernel.setMap(null);
        }
        let lat_lng = [];
        allShapes.forEach(function(data, index) {
            lat_lng[index] = getCoordinates(data);
        });
        if(lat_lng.length == 0){
            document.getElementById('coordinates').value = '';
        }else{
            document.getElementById('coordinates').value = JSON.stringify(lat_lng);
        }
    }

    function clearMap() {
        if (allShapes.length > 0) {
            for (var i = 0; i < allShapes.length; i++){
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

        } else {
            
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
                var bounds = new google.maps.LatLngBounds();
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
                if (place && place.address_components) {
                    var city = place.address_components.find(f => f.types.includes("locality"))?.long_name || "";
                    var state = place.address_components.find(f => f.types.includes("administrative_area_level_1"))?.long_name || "";
                    var country = place.address_components.find(f => f.types.includes("country"))?.long_name || "";
                    var address_lat = place.geometry.location.lat();
                    var address_lng = place.geometry.location.lng();
                    map.setCenter({ lat: address_lat, lng: address_lng });
                    map.setZoom(12);
                    $("#search-box")
                        .val(place.formatted_address || "")
                        .attr('data-latitude', place.geometry.location.lat())
                        .attr('data-longitude', place.geometry.location.lng())
                        .attr('data-city', city)
                        .attr('data-state', state)
                        .attr('data-country', country);
                }
            });
        }
    }

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
            if (layer instanceof L.Polygon) {
                // Add the drawn layer to the map
                drawnItems.addLayer(layer);
                makePolygonDraggable(layer);
                // Bind a popup and open it
                layer.bindPopup("Drag me!").openPopup();
                // Update selected polygon variable (optional, depending on use case)
                selectedPolygon = layer;
            } else {
                console.log("This is not a polygon.");
            }
        });
        // Optional: Restrict dragging to only one polygon at a time (click event)
        map.on('click', function(event) {
            map.dragging.disable();
            var latlng = event.latlng; 
            if (selectedPolygon) {
                // If there's already a selected polygon, deselect it
                selectedPolygon.setStyle({ color: '#3388ff' });
            }
            drawnItems.eachLayer(function(layer) {
                makePolygonDraggable(layer);
                if (layer instanceof L.Polygon && layer.getBounds().contains(latlng)) {
                    selectedPolygon = layer;
                    layer.setStyle({ color: 'red' });
                }
                // Optionally, log the coordinates of the drawn polygon to the console
                const  coordinates = layer.getLatLngs();  // Get the polygon's coordinates
                document.getElementById('coordinates').value = JSON.stringify(coordinates);
            });
        });
    }
    // Function to update coordinates (when polygon is resized or dragged)
    function updateCoordinates(layer) {
        let latLngs = layer.getLatLngs();
        let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);
        let convertedArray = flatLatLngs.map(function(latLng) {
            if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                return { lat: latLng.lat, lng: latLng.lng };

            }
        }).filter(item => item !== undefined); // Filter out undefined items
        // Update coordinates in the input field
        document.getElementById('coordinates').value = JSON.stringify(convertedArray);
    }
    function makePolygonDraggable(layer) {
        var latLngs = layer.getLatLngs()[0]; // Get the LatLngs of the polygon
        const  coordinates = layer.getLatLngs();  // Get the polygon's coordinates
        document.getElementById('coordinates').value = JSON.stringify(coordinates);
        // To track mouse position and delta
        var isDragging = false;
        var startLatLng = null;
        var startLatLngs = [];
        // Mouse down event to start dragging
        layer.on('mousedown', function(e) {
            isDragging = true;
            startLatLng = e.latlng; // Store the initial mouse position in LatLng
            startLatLngs = latLngs.map(function(latlng) {
                return latlng; // Clone the LatLngs of the polygon for reference
            });
            map.on('mousemove', onMouseMove); // Track mouse movement
            map.on('mouseup', onMouseUp); // End dragging when mouse is released
        });
        // Mouse move event to drag the polygon
        function onMouseMove(e) {
            const  coordinates = layer.getLatLngs();  // Get the polygon's coordinates
            layer.setLatLngs(coordinates);
            document.getElementById('coordinates').value = JSON.stringify(coordinates);
            if (isDragging) {
                var dx = e.latlng.lng - startLatLng.lng; // Calculate change in longitude
                var dy = e.latlng.lat - startLatLng.lat; // Calculate change in latitude
                // Create new LatLngs by applying the change to each point
                var newLatLngs = startLatLngs.map(function(latlng) {
                    return L.latLng(latlng.lat + dy, latlng.lng + dx); // Shift each point by dx, dy
                });
                // Update the polygon's LatLngs
                layer.setLatLngs([newLatLngs]);
                document.getElementById('coordinates').value = JSON.stringify(newLatLngs);
            }
        }
        // Mouse up event to stop dragging
        function onMouseUp() {
            const  coordinates = layer.getLatLngs();  // Get the polygon's coordinates
            layer.setLatLngs(coordinates);
            document.getElementById('coordinates').value = JSON.stringify(coordinates);       
            isDragging = false;
            map.off('mousemove', onMouseMove); // Stop mousemove tracking
            map.off('mouseup', onMouseUp); // Stop mouseup tracking
        }
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
    function DragMap() {
        map.dragging.enable();
    }
     // Allow deletion of selected polygon
     function deleteSelectedPolygon() {
        map.dragging.disable();
        if (!drawnItems) {
            return;
        }
        if (selectedPolygon) {
            drawnItems.removeLayer(selectedPolygon);
            selectedPolygon = null;  
            if(selectedPolygon == null){
                document.getElementById('coordinates').value = '';
            }          
        } 
        else {
            alert("Please select polygon to delete");   
        }
    }
    document.getElementById('search-box').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent the autocomplete behavior on Enter key press
        }
    });
    
    function initMap() {
        
        var default_lat = getCookie('default_latitude');
        var default_lng = getCookie('default_longitude');
        
        if (mapType == "OSM"){

            map = L.map('map').setView([default_lat, default_lng], 10);
            map.dragging.disable();
            $(".mapType").hide();   
            searchBox();

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);
             // Create a feature group to store drawn items (polygons, lines, etc.)
            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            // Set up the Leaflet Draw control
            var drawControl = new L.Control.Draw({
                edit: {
                    featureGroup: drawnItems,
                    remove: false
                },
                draw: {
                    polygon: {
                        allowIntersection: false, // Disable intersecting polygons
                        showArea: true // Show area of the polygon
                    },
                    rectangle: false, // Disable rectangle drawing
                    circle: false,    // Disable circle drawing
                    marker: false,    // Disable marker drawing
                    polyline: false,  // Disable polyline drawing
                    circlemarker: false,
                },
            });
            map.addControl(drawControl);
            map.on('draw:dragend', function (event) {
               // makePolygonDraggable(event.layer);
            });
            map.on('draw:edited', function(event) {
                event.layers.eachLayer(function(layer) {
                    if (layer instanceof L.Polygon || layer instanceof L.MultiPolygon) {
                        let latLngs = layer.getLatLngs();
                        let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);

                        // Prepare array only for saving
                        let convertedArray = flatLatLngs.map(function(latLng) {
                            if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                                return { lat: latLng.lat, lng: latLng.lng };
                            }
                            return null;
                        }).filter(item => item !== null);

                        // ✅ Do NOT call layer.setLatLngs(finalArray)
                        document.getElementById('coordinates').value = JSON.stringify(convertedArray);
                    }
                });
            });

            map.on('draw:resize', function(event) {
                var layer = event.layer;
                if (layer instanceof L.Polygon || layer instanceof L.MultiPolygon) {
                    let latLngs = layer.getLatLngs();
                    let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);

                    let convertedArray = flatLatLngs.map(function(latLng) {
                        if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                            return { lat: latLng.lat, lng: latLng.lng };
                        }
                        return null;
                    }).filter(item => item !== null);

                    document.getElementById('coordinates').value = JSON.stringify(convertedArray);
                }
            });

            enablePolygonDrawing(map);
        }
        else
        {
            $(".mapType").show();
            var infowindow = new google.maps.InfoWindow({
                size: new google.maps.Size(150, 50)
            })
            var defaultLatLong = JSON.parse('<?php echo json_encode($lat_long); ?>');
            if (defaultLatLong.length != 0) {
                default_lat = parseFloat(defaultLatLong['lat']);
                default_lng = parseFloat(defaultLatLong['lng']);
            }
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 8,
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

            searchBox();
            
            var shapeOptions = {
                strokeWeight: 1,
                fillOpacity: 0.4,
                editable: true,
                draggable: true
            };
            
            drawingManager = new google.maps.drawing.DrawingManager({               
                drawingMode: null,
                drawingControl: false,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.RIGHT_CENTER,
                    drawingModes: ['polygon']
                },
                polygonOptions: shapeOptions,
                map: map
            });
           
            google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
                var newShape = e.overlay;
                allShapes.push(newShape);

                updatePolygonCoordinates();

                newShape.setOptions({
                    fillColor: shapeColor
                });

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
