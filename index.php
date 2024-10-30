<!DOCTYPE html>
<html>
<head>
    <title>Título 14</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.vectorgrid@1.3.0/dist/Leaflet.VectorGrid.bundled.js"></script>
</head>
<body style='margin:0'>
    <div id="map" style="width: 100vw; height: 100vh; background: #fdfdfd"></div>
    <script>
        const minZoom = 6;
        const maxZoom = 15;

        var map = L.map('map', {
            center: [41.5, 2],
            minZoom: minZoom,
            maxZoom: maxZoom,
            zoom: 12,
            tap: false,
        });

        var bounds = [[42.385927, 1.234657], [41.148408, 2.850021]];
        map.fitBounds(bounds);

        var mapBaseLayer = L.tileLayer(
          'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}'
        ).addTo(map);

        var mapUrl = "https://helmugacloud.github.io/1272/tiles/{z}/{x}/{y}.pbf";

        var vectorTileStyling = {
            OSONA_20240326: function (properties, zoom) {
                var weight = zoom > 12 ? 1.0 : 0.5; // Agrega grosor mínimo para niveles de zoom bajos
                var color = properties.color || "#ffff00"; // Asigna color desde la propiedad 'color' o un predeterminado

                return {
                    fill: true,
                    weight: 1,
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.9,
                    opacity: 1.0
                };
            },
            BARCELONA_20240322: function (properties, zoom) {
                var weight = zoom > 12 ? 1.0 : 0.5; // Agrega grosor mínimo para niveles de zoom bajos
                var color = "#ffff00"; // Asigna color desde la propiedad 'color' o un predeterminado

                return {
                    fill: true,
                    weight: 1,
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.9,
                    opacity: 1.0
                };
            }
        }


        var mapVectorTileOptions = {
            rendererFactory: L.canvas.tile,
            interactive: true,
            maxNativeZoom: maxZoom,
            minZoom: minZoom,
            vectorTileLayerStyles: vectorTileStyling,
        };

        var mapPbfLayer = new L.VectorGrid.Protobuf(
            mapUrl, mapVectorTileOptions
        ).on('click', function (e) {
            L.popup()
                .setContent("Feature clicked")
                .setLatLng(e.latlng)
                .openOn(map);
        });

        mapPbfLayer.addTo(map);
    </script>
</body>
</html>
