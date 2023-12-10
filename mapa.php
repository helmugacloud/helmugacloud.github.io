
<!DOCTYPE html>
<html>

<head>
    <title>Spatial Signatures in Great Britain</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- load leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
        integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
        crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
        integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
        crossorigin=""></script>

    <!-- load VectorGrid extension -->
    <script src="https://unpkg.com/leaflet.vectorgrid@1.3.0/dist/Leaflet.VectorGrid.bundled.js"></script>

    <!-- load locate plugin -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.76.1/dist/L.Control.Locate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol@0.76.1/dist/L.Control.Locate.min.js"
        charset="utf-8"></script>

    <!-- legend styles -->

    <script>
        function toggleTitle() {
            var x = document.getElementById("maptitle");
            if (x.style.display === "block") {
                x.style.display = "none";
            } else {
                x.style.display = "block";
            }
        };

        function toggleLegend() {
            var x = document.getElementById("maplegend signaturetype");
            if (x.style.display === "block") {
                x.style.display = "none";
            } else {
                x.style.display = "block";
            }
        }
    </script>

</head>

<body style='margin:0'>

    <!-- div containing map -->
    <div id="map" style="width: 100vw; height: 100vh; background: #fdfdfd"></div>

    <!-- specification of leaflet map -->
    <script>
        // defaults
        const minZoom = 6;
        const maxZoom = 15;

        // get parameters from URL to allow custom location and zoom start
        var params = {};
        window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
            params[key] = value;
        });

        // create map
        var map = L.map('map', {
            center: [params.lat || 53.4107, params.lng || -2.9704],
            minZoom: minZoom,
            maxZoom: maxZoom,
            zoomControl: true,
            zoom: params.zoom || 12,
            tap: false,
        });


        var bounds = [[42.385927, 1.234657], [41.148408, 2.850021]];
        map.fitBounds(bounds);

        // add background basemap
        var mapBaseLayer = L.tileLayer(
          'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}'
        ).addTo(map);

        // get vector tiles URL
        //var mapUrl = "https://urbangrammarai.xyz/great-britain/tiles/{z}/{x}/{y}.pbf";
        var mapUrl = "https://helmugacloud.github.io/tiles/{z}/{x}/{y}.pbf";

        // mapping fof colors to signature types
        const cmap = {
            "0_0": "#f2e6c7",
            "1_0": "#8fa37e",
            "3_0": "#d7a59f",
            "4_0": "#d7ded1",
            "5_0": "#c3abaf",
            "6_0": "#e4cbc8",
            "7_0": "#c2d0d9",
            "8_0": "#f0d17d",
            "2_0": "#678ea6",
            "2_1": "#94666e",
            "2_2": "#efc758",
            "9_0": "#3b6e8c",
            "9_1": "#333432",
            "9_2": "#ab888e",
            "9_3": "#c1c1c0",
            "9_4": "#bc5b4f",
            "9_5": "#a7b799",
            "9_6": "#c1c1c0",
            "9_7": "#c1c1c0",
            "9_8": "#c1c1c0"
        };

        // mapping of names to signature types to be shown in popup on click
        const popup_info = {
            "0_0": "<strong>Signature type</strong><br>Countryside agriculture",
            "1_0": "<strong>Signature type</strong><br>Accessible suburbia",
            "3_0": "<strong>Signature type</strong><br>Open sprawl",
            "4_0": "<strong>Signature type</strong><br>Wild countryside",
            "5_0": "<strong>Signature type</strong><br>Warehouse/Park land",
            "6_0": "<strong>Signature type</strong><br>Gridded residential quarters",
            "7_0": "<strong>Signature type</strong><br>Urban buffer",
            "8_0": "<strong>Signature type</strong><br>Disconnected suburbia",
            "2_0": "<strong>Signature type</strong><br>Dense residential neighbourhoods",
            "2_1": "<strong>Signature type</strong><br>Connected residential neighbourhoods",
            "2_2": "<strong>Signature type</strong><br>Dense urban neighbourhoods",
            "9_0": "<strong>Signature type</strong><br>Local urbanity",
            "9_1": "<strong>Signature type</strong><br>Concentrated urbanity",
            "9_2": "<strong>Signature type</strong><br>Regional urbanity",
            "9_4": "<strong>Signature type</strong><br>Metropolitan urbanity",
            "9_5": "<strong>Signature type</strong><br>Hyper concentrated urbanity",
        };

        // define styling of vector tiles
        var vectorTileStyling = {
            signatures_combined_levels_clipped_4326: function (properties, zoom) {
                var weight = 0;
                if (zoom > 12) {
                    weight = 1.0;
                }
                return ({
                    fill: true,
                    weight: weight,
                    color: "#ffffff",
                    fillColor: "#ff0000",
                    fillOpacity: 0.9,
                    opacity: 1.0,
                });
            }
        }

        // define options of vector tiles
        var mapVectorTileOptions = {
            rendererFactory: L.canvas.tile,
            interactive: true,
            attribution: '&copy; <a href="https://martinfleischmann.net">Martin Fleischmann</a>, <a href="https://darribas.org">Dani Arribas-Bel</a>, <a href="https://urbangrammarai.xyz">Urban Grammar AI research project</a>',
            maxNativeZoom: maxZoom,
            minZoom: minZoom,
            vectorTileLayerStyles: {
    // A plain set of L.Path options.
    BAGES2: {
        weight: 2,
        fillColor: 'red',
        fillOpacity: 1,
        color: "red"
    },
},
        };

        // create VectorGrid layer and add popup to it
        var mapPbfLayer = new L.VectorGrid.Protobuf(
            mapUrl, mapVectorTileOptions
        ).on('click', function (e) {
            L.popup()
                .setContent(popup_info[e.layer.properties.signature_type])
                .setLatLng(e.latlng)
                .openOn(map);
        });

        // add VectorGrid layer to map
        mapPbfLayer.addTo(map);

    </script>

</body>

</html>