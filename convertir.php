<?php

$dir = "/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/replay-map/1272/";

$array_merge = [];

/*if ($handle = opendir($dir)) {
  while (false !== ($entry = readdir($handle))) {    
      if ($entry != "." && $entry != ".." && substr($entry, 0, 9) === 'VALLESOCC') {

        $extension = pathinfo($entry, PATHINFO_EXTENSION);


        if(strtolower($extension) === 'json') {
          $contenido = file_get_contents($dir.$entry);
          $array1 = json_decode($contenido, true)['features'];

          $array_merge = array_merge($array_merge, $array1);
        }

      }        
  }    
  closedir($handle);
}*/


/*
$json = '{ "type": "FeatureCollection", "features": [] }';
$json_obj = json_decode($json);
$json_obj->features = $array_merge;


file_put_contents($dir.'VALLESOCCIDENTAL.js', "var VALLESOCCIDENTAL = ".json_encode($json_obj));*/


$contenido = file_get_contents($dir."BAGES_20230401.json");
$json_obj = json_decode($contenido);

$json_obj->name = "BAGES";

echo $json_obj->type;

for($i=0; $i<sizeof($json_obj->features); $i++) {
  $json_obj->features[$i]->properties->FULLNAME = "N Vasco Rd";
  $json_obj->features[$i]->properties->id = $i;
  $json_obj->features[$i]->properties->name = "bages";
  //$json_obj->features[$i]->tippecanoe->maxzoom = 9;
  //$json_obj->features[$i]->tippecanoe->minzoom = 4;

  for($j=0; $j<sizeof($json_obj->features[$i]->geometry->coordinates); $j++){
    $json_obj->features[$i]->geometry->coordinates[$j][0] = floatval($json_obj->features[$i]->geometry->coordinates[$j][0]);
    $json_obj->features[$i]->geometry->coordinates[$j][1] = floatval($json_obj->features[$i]->geometry->coordinates[$j][1]);
  }
}

echo json_encode($json_obj);

file_put_contents($dir.'BAGES2.json', json_encode($json_obj));

?>