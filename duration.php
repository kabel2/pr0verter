<?php
// duration.php soll die verbleibende zeit ausgeben.

// sollte checken ob es die id überhaupt gibt
// funktioniert leider nicht ganz so
// deshalb geb ich einfach 0 sekunden aus
$rm = false;
if(isset($_GET["id"])){
	$id = $_GET["id"];
	// code g0re
	if(file_exists("/var/www/logs/".$id."outputfile")){
		
	} else {
		if(file_exists("/var/www/logs/".$id.".sourceoutputfile")){
			$rm = true;
		} else {
			$arr = array("duration" => 0, "time" => 0, "progress" => 0);
			echo json_encode($arr);
			return;
		}
	}
} else {
	$arr = array("duration" => 0, "time" => 0, "progress" => 0);
		echo json_encode($arr);
	return;
}
$content;
if($rm){
	$content = @file_get_contents("/var/www/logs/".$id.".sourceoutputfile");
} else {
	$content = @file_get_contents("/var/www/logs/".$id."outputfile");
}

//kopiert
if($content){
    //get duration of source
    preg_match("/Duration: (.*?), start:/", $content, $matches);

    $rawDuration = $matches[1];

    //rawDuration is in 00:00:00.00 format. This converts it to seconds.
    $ar = array_reverse(explode(":", $rawDuration));
    $duration = floatval($ar[0]);
    if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
    if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;

    //get the time in the file that is already encoded
    preg_match_all("/time=(.*?) bitrate/", $content, $matches);

	
    $rawTime = array_pop($matches);

    //this is needed if there is more than one match
    if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

    //rawTime is in 00:00:00.00 format. This converts it to seconds.
    $ar = array_reverse(explode(":", $rawTime));
    $time = floatval($ar[0]);
    if (!empty($ar[1])) $time += intval($ar[1]) * 60;
    if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;

    //calculate the progress
    $progress = round(($time/$duration) * 100);

    //echo "Duration: " . $duration . "<br>";
   //echo "Current Time: " . $time . "<br>";
    //echo "Progress: " . $progress . "%";
	
	// Hartz4 methode um herrauszufinden ob datei fertig konvertiert ist
	if($rm){
		if( strpos(file_get_contents("/var/www/logs/".$id.".sourceoutputfile"),"muxing overhead") !== false) {
			$progress = 100; 
			// ich weiss nicht ob die anderen werte dann noch $time und $progress stimmen
		};
	} else {
		if( strpos(file_get_contents("/var/www/logs/".$id."outputfile"),"muxing overhead") !== false) {
		   $progress = 100; 
		};
	}
	
	$arr = array("duration" => $duration, "time" => $time, "progress" => $progress);
	echo json_encode($arr);

}
?>