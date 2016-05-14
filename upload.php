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

// code gore
function isSupported($arg){
	$supportedTypes = array("webm", "mp4", "mkv", "mov", "avi", "wmv", "flv", "3gp", "gif");
	$supported = 0;
	$wem = false;
	$supportgif = false;
	for($i=0; $i < count($supportedTypes); $i++)
	{
		if(strcasecmp($supportedTypes[$i], $arg) == 0)
		{
			if(strcasecmp($arg, "gif")==0){
				$supportgif = true;
			}
			if(strcasecmp($arg, "webm")==0){
				$wem = true;
			}
			$supported = 1;
			break;
		}
	}
	
	if($supported == 0){
		return 0;
	}
	if($wem){
		return 2;
	}
	if($supportgif){
		return 3;
	}
	return 1;
}

// der name sollte vllt kürzer sein
function randomName(){
	$milliseconds = round(microtime(true) * 1000);
	$hash = md5($milliseconds.uniqid());
	return $hash;
}

$verbindung = mysql_connect ("xx","xx", "xx") or die ("keine Verbindung möglich. Benutzername oder Passwort sind falsch");
mysql_select_db("xxx") or die ("Die Datenbank existiert nicht.");
$nip = getenv ("REMOTE_ADDR");
$ip = md5($nip);
$abfrage = "SELECT tstamp FROM pr0verter WHERE ip = '$ip'";
$tim = time();
$stam = $tim;
$ergebnis = mysql_query($abfrage);
$boo = false;

	while($row = mysql_fetch_object($ergebnis))
	   {
		   $boo = true;
		   $stam = $row->tstamp;
		}
if($boo == false){
	$abfrage2 = "SELECT COUNT(ip) FROM pr0verter";
	$ergebnis2 = mysql_query($abfrage2);
	$menge = mysql_fetch_row($ergebnis2);
	$menge = $menge[0];
	$sql = "INSERT INTO pr0verter (num, ip, tstamp) VALUES ('$menge', '$ip', '$tim')";
	$row = $menge;
	$out = mysql_query($sql);
	$boo = true;
}

$toobig;
$toobig = false;

if($boo){
	if(($tim-$stam) > 60 OR ($tim-$stam) == 0){
		$gformat = "";
		$namehash = "";
		if(isset($_GET["remote_upload"])){
			$name = $_GET["remote_upload"];

			$namearray = explode("/",$name);
			$size = count($namearray);
			$size = $size-1;
			$format = explode(".", $namearray[$size])[1];
			$randomOut = randomName();
			$random = $randomOut.".".$format;
			$ll = isSupported($format);
			$filesize = exec("wget --spider $name 2>&1 | awk '/Length/ {print $2}'");
			if($ll==1){
				if($filesize < 50000000){
					exec("wget -P /var/www/videos -A webm,mp4,mkv,mov,avi,wmv,flv,3gp,gif $name");
					exec("mv /var/www/videos/$namearray[$size] /var/www/videos/$random");
					$namehash = $randomOut;
				} else {
					echo "hmmm die Datei ist über 50MB gross :/";
					return;
				}
			} else {
				if($ll==0){
					echo "nur webm, mp4, mkv, mov, avi, wmv, flv, 3gp, gif";
					echo "nur direkte links. yt, fb und co funktionieren NICHT, nutzt dafür http://keepvid.com/";
					
					return;
				}
				if($ll==3){
					if($filesize < 50000000){
						exec("wget -P /var/www/videos -A webm,mp4,mkv,mov,avi,wmv,flv,3gp,gif $name");
						exec("mv /var/www/videos/$namearray[$size] /var/www/videos/$random");
						
						
						$api = new Api("xxx");

						$api->convert([
							'inputformat' => 'gif',
							'outputformat' => 'webm',
							'input' => 'upload',
							'file' => fopen('/var/www/videos/'.$random, 'r'),
						])
						->wait()
						->download('/var/www/videos/'.$randomOut."."."webm");
						$namehash = $randomOut;
						if(filesize("/var/www/videos/".$namehash."."."webm") > 4194000){
							$toobig = true;
						}
					} else {
						echo "hmmm die Datei ist über 50MB gross :/";
						return;
					}
				}
				if($ll==2){
					if($filesize < 50000000){
						$random = $randomOut.".source.".$format;
						exec("wget -P /var/www/videos -A webm,mp4,mkv,mov,avi,wmv,flv,3gp,gif $name");
						exec("mv /var/www/videos/$namearray[$size] /var/www/videos/$random");
						$namehash = $randomOut.".source";
					} else {
						echo "hmmm die Datei ist über 50MB gross :/";
						return;
					}
				} else {
					if($ll != 3){
						echo "nur webm, mp4, mkv, mov, avi, wmv, flv, 3gp, gif";
						return;
					}
				}
			}
			
			$gformat = $format;
			
			
		} else {
			$name = $_FILES['datei']['name'];
			$format = pathinfo('/var/www/videos/'.$name, PATHINFO_EXTENSION);
			$llimit = 4;
			$randomOut = randomName();
			$random = $randomOut.".".$format;
			$ll = isSupported($format);
			error_log($ll.":".$format.":".$name);
			if($ll==1){
				$namehash = $randomOut;
			} else {
				if($ll==3){
						move_uploaded_file($_FILES['datei']['tmp_name'], "/var/www/videos/".$random);
						
						$api = new Api("xxx");

						$api->convert([
							'inputformat' => 'gif',
							'outputformat' => 'webm',
							'input' => 'upload',
							'file' => fopen('/var/www/videos/'.$random, 'r'),
						])
						->wait()
						->download('/var/www/videos/'.$randomOut."."."webm");
						$namehash = $randomOut;
						if(filesize("/var/www/videos/".$namehash."."."webm") > 4194000){
							$toobig = true;
						}
				}
				if($ll==0){
					echo "nur webm, mp4, mkv, mov, avi, wmv, flv, 3gp, gif";
					return;
				}
			}
			if($ll==2){
				$random = $randomOut.".source.".$format;
				$namehash = $randomOut.".source";
			}
			$gformat = $format;
			if($ll != 3){
				move_uploaded_file($_FILES['datei']['tmp_name'], "/var/www/videos/".$random);
			}
			$gformat = $format;
		}
		if(strcasecmp($namehash, "") == 0){
			echo "Ohhhh irgendwas ist schief gelaufen, Sorry :/";
			return;
		}
		$abfrage2 = "SELECT COUNT(ip) FROM pr0verter";
		$ergebnis2 = mysql_query($abfrage2);
		$menge = mysql_fetch_row($ergebnis2);
		$menge = $menge[0];
		$aendern = "UPDATE pr0verter SET tstamp='$tim' WHERE ip='$ip';";
		$update = mysql_query($aendern);
		
		if($toobig == true OR $ll != 3){
		

		$movie = new ffmpeg_movie("/var/www/videos/$namehash.$format", false);
		$duration = $movie->getDuration()+5;
		$llimit;
		
		//((4mb*1024)/sekunden)*8
		// -> 
		$llimit = $_COOKIE['limit'];
		if($llimit == 0){
			$llimmit = 4;
		}
		if($duration > 120){
			$duration = 120;
		}
		if($llimit == 4){
			$bitrate = (4096/$duration)*8;
			$bitrate = $bitrate."k";
		} else {
			$bitrate = (($llimit*1024)/$duration)*8;
			$bitrate = $bitrate."k";
		}
		$px = $movie->getFrameHeight();
		$py = $movie->getFrameWidth();
		
		// WICHTIG: Hier muss richtig gescalt werden
		// d.h nicht mit geteilt arbeiten
		// irgendwie kommt die ffmpeg version mit px:-1 nicht klar
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
		$webm = explode(".", $namehash)[0].".webm";
		$webm2 = "/var/www/videos/".$webm;
		$max_size = $llimit;
		$max_size = $max_size*8192;
		$durationURL = "duration.php?id=".$randomOut;
		} else {
			$webm = $namehash.".webm";
		}
		
		$path = "/videos/".$webm;
		$path2 = "/videos/".$namehash.".".$format;
		
		

		
		$url = "http://pr0verter.de/videos/".$webm;
		$url2 = "http://pr0verter.de/videos/".$namehash.".".$format;
		?>
		<script>

		function move() {
		  var elem = document.getElementById("myBar");   
		  var width = 0;
		  var id = setInterval(frame, 2000);
		  function frame() {
			if (width >= 100) {
				// hier kommt noch ne extra seite hin
				// also es wird z.b an pr0verter.de/beta/id.php?asdfasdf
				// weitergeleitet, dort wird dann das video angezeigt
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
				
				// ultra hässlich
				var txt1 = "<a href='/videos/<?php echo $namehash.'.webm'; ?>' download>Download</a>";   
				$("body").append(txt1); 
				
				var txt2 = "<p>Link zum kopieren: http://pr0verter.de/videos/<?php echo $namehash.'.webm'; ?> </p>"; 
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
		$log1 = "/var/www/logs/".$namehash."1.txt";
		$log2 = "/var/www/logs/".$namehash."2.txt";
		
		$passlog1 = "/var/www/logs/".$namehash;
		$passlog2 = "/var/www/logs/".$namehash;
		// old : shell_exec( "ffmpeg -i /var/www/videos/$namehash.$format -vcodec libvpx -b $bitrate -minrate $bitrate -maxrate $bitrate -bufsize $bitrate -qmin 0 -qmax 54 -an -t 120 -s $rsize -threads 4 -fs $max_size /var/www/videos/$webm" . "> /dev/null 2>/dev/null &" );
		
		
		// sollte eigentlich einen prozess im hintergrund starten, funktioniert so halb wegs
		// alternative wäre gern willkommen
		$cmd = "ffmpeg -y -i /var/www/videos/$namehash.$format -vb $bitrate -minrate $bitrate -maxrate $bitrate -s $rsize -an -t 120 -passlogfile $passlog1 -pass 1 /var/www/videos/$webm 1> $log1 2>&1 && ffmpeg -y -i /var/www/videos/$namehash.$format -vb $bitrate -minrate $bitrate -maxrate $bitrate -s $rsize -an -t 120 -passlogfile $passlog2 -pass 2 /var/www/videos/$webm 1> $log2 2>&1";
		$outputfile = "/var/www/logs/".$namehash."outputfile";
		$pidfile = "/var/www/logs/".$namehash."pidfile";
		exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $outputfile, $pidfile));
		
		// früher hab ich mal die dateien auf einem anderen server abgespeichert (s.pr0verter.de)
		// noez.de hat leider wegen zu hohem traffic den server gelöscht und die preise erhöht
		// danach hatte ich kein bock mehr auf den scheiss
		//$ch = curl_init("http://s.pr0verter.de/save.php?pass=VRd9O4l6bPCg4edu&dl=http://31.214.243.205".$path);
		//curl_setopt($ch, CURLOPT_NOBODY, true);
		//curl_exec($ch);
		//$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// $retcode >= 400 -> not found, $retcode = 200, found.
		//curl_close($ch);
		
		//$ch1 = curl_init("http://s.pr0verter.de/save.php?pass=VRd9O4l6bPCg4edu&dl=http://31.214.243.205".$path2);
		//curl_setopt($ch1, CURLOPT_NOBODY, true);
		//curl_exec($ch1);
		//$retcode = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
		// $retcode >= 400 -> not found, $retcode = 200, found.
		//curl_close($ch1);
					
		
		
		
		
		
		
	} else {
		echo "<script>
			alert('Du kannst nur jede minute ein Video konvertieren :/');
		</script>";
	}
}
?>

