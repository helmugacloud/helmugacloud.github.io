<?php

  include '/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/php/functions.php';

  if(!isset($_GET["stage"]))
    $stage = "NA";
  else
    $stage = $_GET["stage"];

  $datoscarrera = datosCarreraItem($_GET["race"], $_GET["year"], $stage);
	$idcarrera = $datoscarrera['idcarrera'];

  function HTMLToRGB($htmlCode) {
    if($htmlCode[0] == '#')
    $htmlCode = substr($htmlCode, 1);

    if (strlen($htmlCode) == 3)
    {
    $htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
    }

    $r = hexdec($htmlCode[0] . $htmlCode[1]);
    $g = hexdec($htmlCode[2] . $htmlCode[3]);
    $b = hexdec($htmlCode[4] . $htmlCode[5]);

    return $b + ($g << 0x8) + ($r << 0x10);
  }

  function RGBToHSL($RGB) {
    $r = 0xFF & ($RGB >> 0x10);
    $g = 0xFF & ($RGB >> 0x8);
    $b = 0xFF & $RGB;

    $r = ((float)$r) / 255.0;
    $g = ((float)$g) / 255.0;
    $b = ((float)$b) / 255.0;

    $maxC = max($r, $g, $b);
    $minC = min($r, $g, $b);

    $l = ($maxC + $minC) / 2.0;

    if($maxC == $minC)
    {
    $s = 0;
    $h = 0;
    }
    else
    {
    if($l < .5)
    {
        $s = ($maxC - $minC) / ($maxC + $minC);
    }
    else
    {
        $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
    }
    if($r == $maxC)
        $h = ($g - $b) / ($maxC - $minC);
    if($g == $maxC)
        $h = 2.0 + ($b - $r) / ($maxC - $minC);
    if($b == $maxC)
        $h = 4.0 + ($r - $g) / ($maxC - $minC);

    $h = $h / 6.0; 
    }

    $h = (int)round(255.0 * $h);
    $s = (int)round(255.0 * $s);
    $l = (int)round(255.0 * $l);

    return (object) Array('hue' => $h, 'saturation' => $s, 'lightness' => $l);
  }

  function loadTeams($idcarrera) {

      $query = "SELECT *
                  FROM tracker.cct_virtual_races_teams
                  WHERE idcarrera = ".$idcarrera.";";

                  
      $result = mysql_query( $query );
      $num_rows = mysql_num_rows($result);	
      
      $html = "<table>";
      $html .= "<tr><td valign='middle'><img class='equipo' style='height:30px;padding:0;margin-right:10px;margin-bottom:0px;margin-left:5px;' src='https://tracker.helmuga.cloud/integration/atrapakm/logonew.png'></td>";
      $html .= "<td>";

      while ( $row = mysql_fetch_assoc( $result ) ) 
      {
          $equipo = $row['team_name'];
          $color = $row['color'];
          
          $rgb = HTMLToRGB($color);
          $hsl = RGBToHSL($rgb);
          if($hsl->lightness > 120)
              $color_texto = "#000000";
          else
              $color_texto = "#FFFFFF";

          $html .= "<span class='equipo' style='background-color:".$color.";color:".$color_texto."'>".$equipo."</span>";
          
      }
      $html .= "</td></tr>";

      $html .= "</table>";

      return $html;

  }

  function loadJson($idcarrera) {

    $html = "";

    if ($handle = opendir("/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/replay-map/".$idcarrera."/")) {
      while (false !== ($entry = readdir($handle))) {    
          if ($entry != "." && $entry != "..") {

            $name_layer = str_replace(".json", "", $entry);

            $html .= $name_layer.": function (properties, zoom) {
                var color = properties.color; 

                return {
                    fill: true,
                    weight: 2,
                    color: color,
                    fillColor: color,
                    fillOpacity: 1,
                    opacity: 1.0
                };
            },";
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
    <title>helmuga.cloud - Virtual race</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.vectorgrid@1.3.0/dist/Leaflet.VectorGrid.bundled.js"></script>
    <link rel='stylesheet' href='https://tracker.helmuga.cloud/css/bootstrap.css'>

    <style>
        #leyenda
        {
            position:absolute;
            z-index: 999;
            top:5px;
            left:5px;
        }

        .equipo
        {
            float:left;
            padding: 5px 10px;
            margin-bottom:3px;
            margin-right:3px;
            border-radius:5px;
            color:white;
            font-size:12px;
        }
    </style>

</head>
<body style='margin:0'>
    <div id="map" style="width: 100vw; height: 100vh; background: #fdfdfd"></div>
    <div id="leyenda">
    
    <?php

      echo loadTeams($idcarrera);

    ?>

    </div> 
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

        var mapUrl = "https://helmugacloud.github.io/<?php echo $idcarrera; ?>/tiles/{z}/{x}/{y}.pbf?nocache=" + new Date().getTime();

        map.zoomControl.setPosition('bottomleft');

        var vectorTileStyling = {
            <?php echo loadJson($idcarrera); ?>
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
