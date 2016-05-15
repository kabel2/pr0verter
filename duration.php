<?php

function create_Json($duration, $time, $progress){
    $jsonArray = array(
        "duration" => $duration,
        "time" => $time,
        "progress" => $progress
    );
    return json_encode($jsonArray);
}

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    
    if (!file_exists("/var/www/logs/" . $id . "outputfile")) {
        return create_Json(0,0,0);
    }
} else {
    return create_Json(0,0,0);
}

$content = @file_get_contents("/var/www/logs/" . $id . "outputfile");

if ($content) {

    preg_match("/Duration: (.*?), start:/", $content, $matches);
    
    $rawDuration = $matches[1];
    
    $ar       = array_reverse(explode(":", $rawDuration));
    $duration = floatval($ar[0]);
    if (!empty($ar[1]))
        $duration += intval($ar[1]) * 60;
    if (!empty($ar[2]))
        $duration += intval($ar[2]) * 60 * 60;
    
    preg_match_all("/time=(.*?) bitrate/", $content, $matches);
   
    $rawTime = array_pop($matches);
    
    if (is_array($rawTime)) {
        $rawTime = array_pop($rawTime);
    }
    
    $ar   = array_reverse(explode(":", $rawTime));
    $time = floatval($ar[0]);
    if (!empty($ar[1]))
        $time += intval($ar[1]) * 60;
    if (!empty($ar[2]))
        $time += intval($ar[2]) * 60 * 60;
    
    $progress = round(($time / $duration) * 100);
    
    if (strpos(file_get_contents("/var/www/logs/" . $id . "outputfile"), "muxing overhead") !== false) {
        $progress = 100;
    };
    
    echo create_Json($duration, $time, $progress);
}

?>
