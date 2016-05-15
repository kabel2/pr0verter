<!DOCTYPE html>
<html>
<head>
        <meta charset="UTF-8">
        <title>pr0verter</title>
        <link rel="icon" href="favicon.png">
        <link rel="stylesheet" href="style.css">
</head>
<body>
        <div id="wrapper">
                <h1>
                        pr0verter
                </h1>
                <ul>
                        <li>Der Pr0verter wandelt deine Videos ins WebM Format um.</li>
                        <li>Videos dürfen max. 50MB groß sein. Videos, die länger als 120 Sekunden sind, werden gekürzt.</li>
                        <li>Das Konvertieren kann je nach Videolänge bis zu 10 Minuten dauern, also Geduld. ¯\_(ツ)_/¯</li>
                </ul>
                <h2>Los geht's</h2>
                <div id="upload">
<?php
$connection = mysql_connect ("xxx","xxx", "xxx") or die ("keine Verbindung möglich. Benutzername oder Passwort sind falsch");
mysql_select_db("xxx") or die ("Die Datenbank existiert nicht.");
$unhashedIp = getenv ("REMOTE_ADDR");

$ip = md5($unhashedIp);

// tstamp=timestamp
$query = "SELECT tstamp FROM pr0verter WHERE ip = '$ip'"; 
$time = time();
$stamp = $time;
$result = mysql_query($query);
$isExisting = false;

	while($row = mysql_fetch_object($result))
	   {
	   	$isExisting = true;
	   	$stamp = $row->tstamp;
	   }
	   
//is ip in db
if($isExisting){ 
	if(($time-$stamp) < 60){
		echo "<script>
			alert('Du kannst nur jede minute ein Video konvertieren :/');
			</script>";	
	}
}
		?>
<form action="upload.php" name="browserUpload" method="post" id="browserUpload" enctype="multipart/form-data">
		<input type="file" name="datei" id="fileupload">
		</form>
		<p>ODER remote Upload</p>
		<form action="upload.php" name="remoteUpload" id="remoteUpload" method="get" enctype="multipart/form-data">
		<input type="text" size=30 name="remote_upload" id="remoteUpload" placeholder="z.B http://i.imgur.com/mGtnm6U.webm">
		</form>
		<br>
		<p> Größe die das Video haben soll in MB </p>
		<input type="number" id="limit" name="limit" min="1" max="30" value="4">
		<script>
			function cphp(){
				var remote = document.getElementById("remoteUpload").value;
				var browser = document.getElementById("browserUpload").value;
				var isSelected = false;
				
				if(new String(remote).valueOf() != new String("undefined").valueOf()){
					alert(remote);
					document.remoteUpload.submit();
					isSelected = true;
				}
				if(new String(browser).valueOf() != new String("undefined").valueOf()){
					if(isSelected != true){
						alert(2);
						document.browserUpload.submit();
						isSelected = true;
					}
				}
				if(isSelected == false){
					alert("Wähl was aus du oppa");
				}
				
				
				document.cookie= "limit=" + (document.getElementById("limit").value);
			}
			</script>
			<br>
			<input type="submit" value="Konvertieren" onclick="cphp()">

<h5>Kontakt: pr0verter@gmail.com</h5>

                </div>
        </div>
</body>
</html>
