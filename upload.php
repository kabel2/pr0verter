<!DOCTYPE html>
<html>
<head>
        <meta charset="UTF-8">
        <title>pr0verter</title>
        <link rel="icon" href="favicon.png">
        <link rel="stylesheet" href="style.css">
</head>
<h1 style="text-align: center;">
    <span style="color: #ee4d2e;">pr0verter</span>
</h1>
<br>
<br>
<div id="ft">
	<div id="ft2">
		<h2> Fortschritt </h2>
	</div>
</div>
<br>
<br>
<div id="myProgress">
  <div id="myBar">
    <div id="label">0%</div>
  </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

<?php
require '/home/marius/cloudconvert-php/vendor/autoload.php';
use \CloudConvert\Api;
function isSupported($arg){
	$supportedTypes = array("webm", "mp4", "mkv", "mov", "avi", "wmv", "flv", "3gp", "gif");
	$supported = 0;
	$webm = false;
	$supportgif = false;
	for($i=0; $i < count($supportedTypes); $i++)
	{
		if(strcasecmp($supportedTypes[$i], $arg) == 0)
		{
			if(strcasecmp($arg, "gif")==0){
				$supportgif = true;
			}
			if(strcasecmp($arg, "webm")==0){
				$webm = true;
			}
			$supported = 1;
			break;
		}
	}
	
	if($supported == 0){
		return 0;
	}
	if($webm){
		return 2;
	}
	if($supportgif){
		return 3;
	}
	return 1;
}

function randomName(){
	$milliseconds = round(microtime(true) * 1000);
	$hash = md5($milliseconds.uniqid());
	return $hash;
}

function extractFileName($url){
	$namearray = explode("/",$url);
	$size = count($namearray);
	$size = $size-1;
	return $namearray[$size];
}

function extractFormat($url){
	$namearray = explode("/",$url);
	$size = count($namearray);
	$size = $size-1;
	$format = explode(".", $namearray[$size])[1];
	return $format;
}

$verbindung = mysql_connect ("xxx","xxx", "xxx") or die ("keine Verbindung möglich. Benutzername oder Passwort sind falsch");
mysql_select_db("xxx") or die ("Die Datenbank existiert nicht.");
$ip = getenv ("REMOTE_ADDR");
$hashedIp = md5($ip);
$query = "SELECT tstamp FROM pr0verter WHERE ip = '$hashedIp'";
$currentTimestamp = time();
$timestamp = $currentTimestamp;
$result = mysql_query($query);
$isIpExisting = false;

	while($row = mysql_fetch_object($result))
	   {
		   $isIpExisting = true;
		   $timestamp = $row->tstamp;
		}
if($isIpExisting == false){
	$query = "SELECT COUNT(ip) FROM pr0verter";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	$row = $row[0];
	$sql = "INSERT INTO pr0verter (num, ip, tstamp) VALUES ('$row', '$hashedIp', '$currentTimestamp')";
	$out = mysql_query($sql);
	$isIpExisting = true;
}

$toobig = false;

if($isIpExisting){
	if(($currentTimestamp-$timestamp) > 60 OR ($currentTimestamp-$timestamp) == 0){
		$gformat = "";
		$filehash = "";
		$videoPath = "/var/www/videos/";
		if(isset($_GET["remote_upload"])){
			$url = $_GET["remote_upload"];
			
			$format = extractFormat($url);
			$fileName = extractFileName($url);
			
			$randomTitle = randomName(); 
			$newFileName = $randomTitle.".".$format; 
			$webmName = $randomTitle.".webm";
			$supported = isSupported($format); 
			$url = escapeshellarg($url);
			// muss mit fopen ersetzt werden
			$filesize = exec("wget --spider $url 2>&1 | awk '/Length/ {print $2}'");
			if($supported==1){
				if($filesize < 50000000){
					exec("wget -P $videoPath -A webm,mp4,mkv,mov,avi,wmv,flv,3gp,gif $url");
					exec("mv $videoPath.$fileName $videoPath.$newFileName");
					$filehash = $randomTitle;
				} else {
					echo "hmmm die Datei ist über 50MB groß :/";
					return;
				}
			} else {
				if($supported==0){
					echo "nur webm, mp4, mkv, mov, avi, wmv, flv, 3gp, gif1";
					echo "nur direkte links. yt, fb und co funktionieren NICHT, nutzt dafür http://keepvid.com/";				
					return;
				}
				if($supported==3){
					if($filesize < 50000000){
						exec("wget -P $videoPath -A webm,mp4,mkv,mov,avi,wmv,flv,3gp,gif $name");
						exec("mv $videoPath.$fileName $videoPath.$newFileName");
						
						//ffmpeg kommt mit gif nicht klar, ich hab schon alles versucht
						//deshalb jetzt cloudconvert.com
						$api = new Api("xxx");

						$api->convert([
							'inputformat' => 'gif',
							'outputformat' => 'webm',
							'input' => 'upload',
							'file' => fopen($videoPath.$newFileName, 'r'),
						])
						->wait()
						->download($videoPath.$webmName);
						if(filesize($videoPath.$webmName) > 4194000){
							$toobig = true;
						}
					} else {
						echo "hmmm die Datei ist über 50MB gross :/";
						return;
					}
				}
				if($supported==2){
					if($filesize < 50000000){
						exec("wget -P $videoPath -A webm,mp4,mkv,mov,avi,wmv,flv,3gp,gif $url");
						exec("mv ".$videoPath.$fileName." ".$videoPath.$randomTitle.".source.webm");
						$filehash = $randomTitle.".source";
					} else {
						echo "hmmm die Datei ist über 50MB gross :/";
						return;
					}
				} else {
					if($supported != 3){
						echo "nur webm, mp4, mkv, mov, avi, wmv, flv, 3gp, gif2";
						return;
					}
				}
			}
			
			$gformat = $format;
			
			
		} else {
			$fileName = $_FILES['datei']['name'];
			$format = pathinfo($videoPath.$fileName, PATHINFO_EXTENSION);
			$sizeLimit = 4; //llimit
			$randomTitle = randomName();
			$newFileName = $randomTitle.".".$format;
			$webmName = $randomTitle.".webm";
			$supported = isSupported($format);
			if($supported==1){
				$filehash = $randomTitle;
			} else {
				if($supported==3){
						move_uploaded_file($_FILES['datei']['tmp_name'], $videoPath.$newFileName);
						
						$api = new Api("xxx");

						$api->convert([
							'inputformat' => 'gif',
							'outputformat' => 'webm',
							'input' => 'upload',
							'file' => fopen($videoPath.$newFileName, 'r'),
						])
						->wait()
						->download($videoPath.$webmName);
						$filehash = $randomOut;
						if(filesize($videoPath.$webmName) > 4194000){
							$toobig = true;
						}
				}
				if($supported==0){
					echo "nur webm, mp4, mkv, mov, avi, wmv, flv, 3gp, gif3";
					return;
				}
			}
			if($supported==2){
				$newFileName = $randomTitle.".source.".$format;
				$filehash = $randomOut.".source";
			}
			$gformat = $format;
			if($supported != 3){
				move_uploaded_file($_FILES['datei']['tmp_name'], $videoPath.$newFileName);
			}
			$gformat = $format;
		}
		if(strcasecmp($filehash, "") == 0){
			echo "Ohhhh irgendwas ist schief gelaufen, Sorry :/";
			return;
		}
		$query = "SELECT COUNT(ip) FROM pr0verter";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		$row = $row[0];
		$updateTime = "UPDATE pr0verter SET tstamp='$currentTimestamp' WHERE ip='$hashedIp';";
		$update = mysql_query($updateTime);
		
		if($toobig == true OR $supported != 3){
		

		$movie = new ffmpeg_movie($videoPath.$filehash.".".$format, false);
		$duration = $movie->getDuration();
		$limit = 4;
		
		//((4mb*1024)/sekunden)*8
		$limit = $_COOKIE['limit'];
		if($limit == 0){
			$limit = 4;
		}
		if($duration > 120){
			$duration = 120;
		}
		if($limit == 4){
			$bitrate = (4096/$duration)*8;
			$bitrate = $bitrate."k";
		} else {
			$bitrate = (($limit*1024)/$duration)*8;
			$bitrate = $bitrate."k";
		}
		
		// das scalen läuft noch nicht wie es sollte 
		// runden ist scheisse...
		
		$px = $movie->getFrameHeight();
		$py = $movie->getFrameWidth();
					
		if($duration > 30){
			if($duration < 60){
				if(($px > 490) AND ($px < 800)){
					$px = $px/1.5;
					$py = $py/1.5;
				}
				if($px > 800){
					$px = $px/2;
					$py = $py/2;
				}
			}
			if($duration > 60){
				if(($px > 490) AND ($px < 800)){
					$px = $px/2;
					$py = $py/2;
				}
				if($px > 800){
					$px = $px/2.7;
					$py = $py/2.7;
				}
			}
		}
		$px = round($px);
		$py = round($py);
		$rsize = $py."x".$px;
		$webm = explode(".", $filehash)[0].".webm";
		$max_size = $limit;
		$max_size = $max_size*8192;
		$durationURL = "duration.php?id=".$randomTitle;
		} else {
			$webm = $filehash.".webm";
		}
		
		$url = "http://pr0verter.de/videos/".$randomTitle.".webm";
		
		// der ganze script teil soll weg und es soll auf eine neue seite geleitet werden
		?>
		<script>
		function move() {
		  var elem = document.getElementById("myBar");   
		  var width = 0;
		  var id = setInterval(frame, 2000);
		  function frame() {
			if (width >= 100) {
				
				var child = document.getElementById("myProgress");
				child.parentNode.removeChild(child);
				
				var child = document.getElementById("ft");
				child.parentNode.removeChild(child);
				
				window.history.pushState('Object', 'Title', '/');
				
				var vid = document.createElement("VIDEO");
				vid.setAttribute("src","<?php echo $url; ?>");
				vid.setAttribute('height', '480');
				vid.setAttribute('width', '720');
				vid.play();
				vid.loop = true;
				vid.controls = true;
				document.body.appendChild(vid);
				
				var txt1 = "<a href='/videos/<?php echo $randomTitle.'.webm'; ?>' download>Download</a>";   
				$("body").append(txt1); 
				
				var txt2 = "<p>Link zum kopieren: http://pr0verter.de/videos/<?php echo $randomTitle.'.webm'; ?> </p>"; 
				$("body").append(txt2); 
				
				clearInterval(id);
			} else {
				$.get("<?php echo $durationURL; ?>", function(data, status){
					var jsonarray = jQuery.parseJSON(data);
					//alert("Data: " + jsonarray.duration + "\nStatus: " + status);
					width = jsonarray.progress; 
					elem.style.width = width + '%'; 
					document.getElementById("label").innerHTML = width * 1  + '%';
				});
			  
			}
		  }
}

$(document).ready(function(){
	move();
});
</script>
<?php

		$log1 = "/var/www/logs/".$filehash."1.txt";
		$log2 = "/var/www/logs/".$filehash."2.txt";
		
		$passlog1 = "/var/www/logs/".$filehash;
		$passlog2 = "/var/www/logs/".$filehash;
		// old : shell_exec( "ffmpeg -i /var/www/videos/$filehash.$format -vcodec libvpx -b $bitrate -minrate $bitrate -maxrate $bitrate -bufsize $bitrate -qmin 0 -qmax 54 -an -t 120 -s $rsize -threads 4 -fs $max_size /var/www/videos/$webm" . "> /dev/null 2>/dev/null &" );
		
		$cmd = "ffmpeg -y -i /var/www/videos/$filehash.$format -vb $bitrate -minrate $bitrate -maxrate $bitrate -s $rsize -an -t 120 -passlogfile $passlog1 -pass 1 /var/www/videos/$webm 1> $log1 2>&1 && ffmpeg -y -i /var/www/videos/$filehash.$format -vb $bitrate -minrate $bitrate -maxrate $bitrate -s $rsize -an -t 120 -passlogfile $passlog2 -pass 2 /var/www/videos/$webm 1> $log2 2>&1";
		$outputfile = "/var/www/logs/".$randomTitle."outputfile";
		$pidfile = "/var/www/logs/".$filehash."pidfile";
		// don't touch a running system...
		// wenn jemand ne alternative für den scheiss hat, immer her damit
		exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile));
					
	} else {
		echo "<script>
			alert('Du kannst nur jede minute ein Video konvertieren :/');
		</script>";
	}
}
?>

