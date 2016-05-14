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
// kopiert
// sorry die db kann ich dir nicht geben
// n "cache" wäre ganz nett, jedes mal ne db abfrage durchzuführen ist vllt n bisschen heftig
// WICHTIG: pw auf externe datei auslagern
$verbindung = mysql_connect ("xxx","xx", "xxx") or die ("keine Verbindung möglich. Benutzername oder Passwort sind falsch");
mysql_select_db("xxx") or die ("Die Datenbank existiert nicht.");
$unhashedIp = getenv ("REMOTE_ADDR");
// ich weiss die namen der variablen sind einfach nur mega beschissen
// bringts was die ip zu hashen?
$ip = md5($unhashedIp);
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
if($boo){
	if(($tim-$stam) > 60){ 
		?>
<form action="upload.php" name="fu2" method="post" id="fu" enctype="multipart/form-data">
		<input type="file" name="datei" id="fileupload">
		</form>
		<p>ODER remote Upload</p>
		<form action="upload.php" name="rm" method="get" enctype="multipart/form-data">
		<input type="text" size=30 name="remote_upload" id="ru" placeholder="z.B http://i.imgur.com/mGtnm6U.webm">
		</form>
		<br>
		<p> Größe die das Video haben soll in MB </p>
		<input type="number" id="limit" name="limit" min="1" max="30" value="4">
		<script>
			function cphp(){
				var remote = document.getElementById("ru").value;
				var fileul = document.getElementById("fu").value;
				var bool = false;
				if(remote != ""){
					document.rm.submit();
					bool = true;
				}
				if(fileul != ""){
					if(bool != true){
						document.fu2.submit();
						bool = true;
					}
					
				}
				if(bool == false){
					alert("Wähl was aus du oppa");
				}
				
				var nameValue = document.getElementById("limit").value;
				var second = "limit=" + nameValue;
				document.cookie=second;
			}
			</script>
			<br>
			<input type="submit" value="Konvertieren" onclick="cphp()">
		<?php
	} else {
		echo "<script>
			alert('Du kannst nur jede minute ein Video konvertieren :/');
		</script>";
	}
} else {
?>
		<form action="upload.php" name="fu2" method="post" id="fu" enctype="multipart/form-data">
		<input type="file" name="datei" id="fileupload">
		</form>
		<p>ODER remote Upload</p>
		<form action="upload.php" name="rm" method="get" enctype="multipart/form-data">
		<input type="text" size=30 name="remote_upload" id="ru" placeholder="z.B http://i.imgur.com/mGtnm6U.webm">
		</form>
		<br>
		<p> Größe die das Video haben soll in MB </p>
		<input type="number" id="limit" name="limit" min="1" max="30" value="4">
		<script>
			function cphp(){
				var remote = document.getElementById("ru").value;
				var fileul = document.getElementById("fu").value;
				var bool = false;
				if(remote != ""){
					document.rm.submit();
					bool = true;
				}
				if(fileul != ""){
					if(bool != true){
						document.fu2.submit();
						bool = true;
					}
					
				}
				if(bool == false){
					alert("Wähl was aus du oppa");
				}
				
				var nameValue = document.getElementById("limit").value;
				var second = "limit=" + nameValue;
				document.cookie=second;
			}
			</script>
			<br>
			<input type="submit" value="Konvertieren" onclick="cphp()">
		<?php
}

?>

<h5>Kontakt: pr0verter@gmail.com</h5>

                </div>
        </div>
</body>
</html>