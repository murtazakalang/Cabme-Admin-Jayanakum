<!doctype html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?> dir="rtl" <?php } ?>>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CabMe') }}</title>
    @if (file_exists(public_path('assets/images/' . $app_setting->app_logo_favicon)) && !empty($app_setting->app_logo_favicon))
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/') . '/' . $app_setting->app_logo_favicon }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/regular.css') }}" rel="stylesheet">
    <link href="{{ asset('css/icons/font-awesome/css/solid.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/toast-master/css/jquery.toast.css')}}" rel="stylesheet">
    <link href="{{ asset('css/colors/blue.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/summernote/summernote.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/leaflet/leaflet.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/leaflet/leaflet.draw.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
 
    @yield('style')

    <style>
        :root {
            --admin-panel-color: {{ $admin_panel_color }};
            --admin-panel-sec-color: {{ $admin_panel_sec_color }};
        }
    </style>
    
    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{asset('assets/plugins/bootstrap/css/bootstrap-rtl.min.css')}}" rel="stylesheet">
    <?php } ?>

    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{asset('css/style_rtl.css')}}" rel="stylesheet">
    <?php } ?>

</head>

<body>

<div id="app" class="fix-header fix-sidebar card-no-border">
    <div id="main-wrapper">
        <header class="topbar non-printable">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                @include('layouts.header')
            </nav>
        </header>

        <aside class="left-sidebar non-printable">
            <div class="scroll-sidebar">
                @include('layouts.menu')
            </div>
        </aside>
    </div>
    <main class="py-4">
        @yield('content')
        @include('layouts.footer')
    </main>
</div>
    
<script src="{{ asset('js/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('js/leaflet/leaflet.draw.js') }}"></script>
<script src="{{ asset('js/leaflet/leaflet.editable.min.js') }}"></script>
<script src="{{ asset('js/leaflet/leaflet.draw-src.js') }}"></script>
<script src="https://unpkg.com/leaflet-geojson-layer/src/leaflet.geojson.js"></script>
<script src="{{ asset('js/leaflet/leaflet-routing-machine.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('js/waves.js') }}"></script>
<script src="{{ asset('js/sidebarmenu.js') }}"></script>
<script src="{{ asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
<script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js')}}"></script>
<script src="{{ asset('js/custom.min.js') }}"></script>
<script src="{{ asset('assets/plugins/summernote/summernote.js')}}"></script>
<script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script type="text/javascript">

    jQuery(window).scroll(function() {
        var scroll = jQuery(window).scrollTop();
        if (scroll <= 60) {
            jQuery("body").removeClass("sticky");
        } else {
            jQuery("body").addClass("sticky");
        }
    });
    
    function loadGoogleMapsScript() { 
        
        const script = document.createElement('script');
        var mapType = "{!! $app_setting->map_for_application !!}";
        if (mapType == "OSM" ){
            script.src = "{{ asset('js/leaflet/leaflet.js')}}"; 
            script.src = "{{ asset('js/leaflet/leaflet.draw.js')}}"; 
            script.src = "{{ asset('js/leaflet/leaflet.editable.min.js')}}";
            script.src = "{{ asset('js/leaflet/leaflet.draw-src.js')}}";
            script.src = "{{ asset('js/leaflet/leaflet.ajax.min.js')}}";
            script.src = "https://unpkg.com/leaflet-geojson-layer/src/leaflet.geojson.js";
            script.src = "{{ asset('js/leaflet/leaflet-routing-machine.js')}}"; 
        }else{
            script.src="https://maps.googleapis.com/maps/api/js?key={{$app_setting->google_map_api_key}}&libraries=drawing,geometry,places";
        }
        script.onload = function () {
            navigator.geolocation.getCurrentPosition(GeolocationSuccessCallback,GeolocationErrorCallback);
            if(typeof window['initMap'] === 'function') { 
                initMap();
            }
            document.dispatchEvent(new Event("mapsLoaded"));
        };
        document.head.appendChild(script);
    }

    const GeolocationSuccessCallback = (position) => {
        if(position.coords != undefined){
            default_latitude = position.coords.latitude
            default_longitude = position.coords.longitude
            setCookie('default_latitude', default_latitude, 365);
            setCookie('default_longitude', default_longitude, 365);
        }
    };

    const GeolocationErrorCallback = (error) => {
        console.log('Error: You denied for your default Geolocation',error.message);
        setCookie('default_latitude', '23.022505', 365);
        setCookie('default_longitude','72.571365', 365);
    };

    loadGoogleMapsScript();

    function setCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        let name = cname + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    $(document).ready(function() {
        var url = "{{ route('language.header') }}";
        $.ajax({
            url: url,
            type: "GET",
            data: {
                _token: '{{csrf_token()}}',
            },

            dataType: 'json',
            success: function(data) {
                $.each(data, function(key, value) {
                    $('#language_dropdown').append($("<option></option>").attr("value", value.code).text(value.language));
                });
                <?php if (session()->get('locale')) { ?>
                    $("#language_dropdown").val("<?php echo session()->get('locale'); ?>");
                <?php } ?>
            }
        });
    });

    $(document).on('change', '.changeLang', function () {        
        var slug = $(this).val();       
        var url = "{{ route('lang.code',':slugid') }}";
        url = url.replace(':slugid', slug);
        if (slug) {
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: '{{csrf_token()}}',
                },
                dataType: 'json',
                success: function(data) {
                    $.each(data, function(key, value) {
                        if (value.code == slug) {
                            if (value.is_rtl == false) {
                                setCookie('is_rtl', 'false', 365);
                            } else {
                                setCookie('is_rtl', value.is_rtl.toString(), 365);
                            }
                            window.location.href = "{{ route('changeLang') }}" + "?lang=" + value.code;
                        }
                    });
                }
            });
        }
    });

</script>

<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
    var pusherSettings = @json($app_setting->pusher_settings);
    var pusher = new Pusher(pusherSettings.pusher_key, {
        cluster: pusherSettings.pusher_cluster
    });
    var channel = pusher.subscribe('sos');
    channel.bind('updated', function(response) {
        var data = response.data;
        Swal.fire({
            icon: 'warning',
            title: 'SOS Alert!',
            html: `<b>User:</b> ${data.user.prenom} ${data.user.nom}<br><b>Location:</b> ${data.destination_name}`,
            showCancelButton: false,
            confirmButtonText: 'View Details',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/sos/show/'+data.sos_id;
            }
        });
    });
    
</script>

@yield('scripts')

</body>

</html>