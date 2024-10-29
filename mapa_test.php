
<?php

  $idcarrera = 1272;
  include '../../php/functions.php';

  function colorTeam($team) {

    global $idcarrera;

    $query = "SELECT color
                  FROM tracker.cct_virtual_races_teams
                  WHERE team = '".$team."'
                  AND idcarrera = $idcarrera;";
                  
    $result = mysql_query( $query );
    $row = mysql_fetch_assoc( $result );

    return $row['color'];

  }

  function obtenerNombreEquipo($fichero) {

    $campos = explode(".", $fichero);
    $nombre = $campos[0];

    $campos = explode("_", $nombre);

    if(sizeof($campos) > 1)
      return $campos[0];
    else
      return $nombre;

  }

  function loadJson() {

    global $idcarrera;

    $html = "";

    if ($handle = opendir("/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/replay-map/".$idcarrera."/")) {
      while (false !== ($entry = readdir($handle))) {    
          if ($entry != "." && $entry != ".." && $entry != "") {

            $equipo = obtenerNombreEquipo($entry);
            $color = colorTeam($equipo);

            if($equipo != "") {
                $html .= pathinfo($entry)['filename'].": {
                        weight: 2,
                        fillOpacity: 1,
                        color: '".$color."',
                        fillColor: '".$color."',
                    },";
            }
            
            
              
          }        
      }    


      closedir($handle);
    }


    return $html;

  }


?>


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
        //var mapUrl = "https://tracker.helmuga.cloud/replay-map/1272/tiles/{z}/{x}/{y}.pbf";



        // define styling of vector tiles
        var vectorTileStyling = {
          <?php

            echo loadJson();

          ?>
        }

        // define options of vector tiles
        var mapVectorTileOptions = {
            rendererFactory: L.canvas.tile,
            interactive: true,
            attribution: '&copy; <a href="https://martinfleischmann.net">Martin Fleischmann</a>, <a href="https://darribas.org">Dani Arribas-Bel</a>, <a href="https://urbangrammarai.xyz">Urban Grammar AI research project</a>',
            maxNativeZoom: maxZoom,
            minZoom: minZoom,
            vectorTileLayerStyles: vectorTileStyling,
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