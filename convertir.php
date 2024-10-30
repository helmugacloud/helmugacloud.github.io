<?php

include '/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/php/functions.php';

$dir = "/var/www/vhosts/helmuga.cloud/tracker.cyclingcloud.com/githubpages/helmugacloud.github.io/json/";

$array_merge = [];

$idcarrera = 1272;

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

if ($handle = opendir($dir)) {
  while (false !== ($entry = readdir($handle))) {    
      if ($entry != "." && $entry != ".." && $entry != "") {

        $contenido = file_get_contents($dir.$entry);
        $json_obj = json_decode($contenido);

        $json_obj->name = $entry;

        $equipo = obtenerNombreEquipo($entry);
        $color = colorTeam($equipo);

        for($i=0; $i<sizeof($json_obj->features); $i++) {
          $json_obj->features[$i]->id = (string)floor(rand(0, 999));
          $json_obj->features[$i]->properties->FULLNAME = "N Vasco Rd";
          $json_obj->features[$i]->properties->id = (string)floor(rand(0, 999));
          $json_obj->features[$i]->properties->name = $entry;
          $json_obj->features[$i]->properties->color = $color;
          //$json_obj->features[$i]->tippecanoe->maxzoom = 9;
          //$json_obj->features[$i]->tippecanoe->minzoom = 4;

          for($j=0; $j<sizeof($json_obj->features[$i]->geometry->coordinates); $j++){
            $json_obj->features[$i]->geometry->coordinates[$j][0] = floatval($json_obj->features[$i]->geometry->coordinates[$j][0]);
            $json_obj->features[$i]->geometry->coordinates[$j][1] = floatval($json_obj->features[$i]->geometry->coordinates[$j][1]);
          }
        }

        //echo json_encode($json_obj);

        file_put_contents($dir.$entry, json_encode($json_obj));

      }
    }
  }

?>