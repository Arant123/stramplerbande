<?php
header('Access-Control-Allow-Origin: *');
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
 
require 'Slim/vendor/autoload.php';
 
$app = new \Slim\App;


$app->get('/', function (Request $request, Response $response) 
{ 
	echo "Hello World";  
});

/*
Hochladen eines Bilds für die Kita
Prüfung der Größe, des Dateityps und der Berechtigungen
*/
$app->post('/upload/kitapic', function (Request $request, Response $response) 
{ 
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$art = $nutzer['art'];
	$kitaid = $nutzer['kita_id'];
	
	$imageFileType = pathinfo($_FILES["fileToUpload"]["name"],PATHINFO_EXTENSION);

	$target_file = "images/" . $kitaid . "." . $imageFileType;


	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	if($check == false) 
	{
		//Die Datei ist kein Bild
		return $response->withStatus(404);
	} 
	else if ($_FILES["fileToUpload"]["size"] > 500000) 
	{
		//Die Datei ist zu groß
		return $response->withStatus(401);
	}
	// Allow certain file formats
	else if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) 
	{
		//Die Datei ist kein Bild (jpg, png, jpeg, gif)
		return $response->withStatus(402);
	}
	else 
	{
		if($art == "Admin" || $art == "KitaMit")
		{
			//Der Nutzer hat bereits ein Bild hochgeladen. Das alte Bild wird gelöscht
			if (file_exists($target_file)) 
			{
				unlink($target_file);
			}
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
			{
				$dateipfad = array('dateipfad' => $target_file);
				echo json_encode($dateipfad, JSON_PRETTY_PRINT);
			} 
			else 
			{
				//Das Hochladen des Bilds hat nicht funktioniert
				return $response->withStatus(403);
			}
		}
		else
		{
		//Kein berechtigter Mitarbeiter
		return $response->withStatus(400);
		}
	}
	
});

/*
Setzen der Kita Infos durch den Kita-Mitarbeiter
*/
$app->post('/kita', function (Request $request, Response $response) 
{ 
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$krippe = $_POST['krippe'];
	$kindergarten = $_POST['kindergarten'];
	$mail = $_POST['mail'];
	$strasse = $_POST['strasse'];
	$telefon = $_POST['telefon'];
	$stadt = $_POST['stadt'];
	$kitaname = $_POST['name'];
	
	$nutzer = holeNutzer();
	$nutzerid = $nutzer['id'];
	$art = $nutzer['art'];
	
	$kitaid = $nutzer['kita_id'];
	
	if($art == "Admin" || $art == "KitaMit")
	{
		$sql = "UPDATE kita SET 
		krippe='$krippe',
		kindergarten='$kindergarten',
		mail='$mail',
		strasse='$strasse',
		telefon='$telefon',
		stadt='$stadt',
		name='$kitaname'
		WHERE id='$kitaid'";
		if(mysqli_query($conn, $sql)){}
	}
	else
	{
		//Kein berechtigter Mitarbeiter
		return $response->withStatus(400);
	}
});

/*
Holen der allgemeinen Kita-Infos
*/
$app->get('/kita', function (Request $request, Response $response) 
{ 
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$nutzerid = $nutzer['id'];
	$kitaid = $nutzer['kita_id'];
	
	
	$sql = "SELECT * FROM kita WHERE id='$kitaid'";
	$result = mysqli_query($conn, $sql);
	$kita = mysqli_fetch_assoc($result);
	
	$kitadaten = array(
	'name' => $kita['name'],
	'tel' => $kita['telefon'], 
	'ort' => $kita['stadt'],
	'strasse' => $kita['strasse'],  
	'krippe' => $kita['krippe'], 
	'kindergarten' => $kita['kindergarten'], 
	'mail' => $kita['mail']);
	
	echo json_encode($kitadaten, JSON_PRETTY_PRINT);
});

/*
Hochladen eines B+Profilbilds für den User
Prüfung der Größe und des Dateityps
*/
$app->post('/imageupload', function (Request $request, Response $response) 
{ 
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$nutzerid = $nutzer['id'];


	$imageFileType = pathinfo($_FILES["fileToUpload"]["name"],PATHINFO_EXTENSION);

	$target_file = "Uploads/" . $nutzerid . "." . $imageFileType;


	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	if($check == false) 
	{
		//Die Datei ist kein Bild
		return $response->withStatus(400);
	} 
	else if ($_FILES["fileToUpload"]["size"] > 500000) 
	{
		//Die Datei ist zu groß
		return $response->withStatus(401);
	}
	// Allow certain file formats
	else if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) 
	{
		//Die Datei ist kein Bild (jpg, png, jpeg, gif)
		return $response->withStatus(402);
	}
	else 
	{
		//Der Nutzer hat bereits ein Bild hochgeladen. Das alte Bild wird gelöscht
		if (file_exists($target_file)) 
		{
			unlink($target_file);
		}
		if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
		{
			$dateipfad = array('dateipfad' => $target_file);
			echo json_encode($dateipfad, JSON_PRETTY_PRINT);
		} 
		else 
		{
			//Das Hochladen des Bilds hat nicht funktioniert
			return $response->withStatus(403);
		}
	}
	
});

/*
Setzen einer neuen Kita-Chat Nachricht
*/
$app->post('/kitachat', function (Request $request, Response $response) 
{ 
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$nutzerid = $nutzer['id'];
	
	$text = $_POST['text'];
	$datum = date("Y-m-d H:i:s");
	
	$sql = "INSERT INTO kitachat (zeit, text, benutzer_id) VALUES ('$datum', '$text', '$nutzerid')";
	if(mysqli_query($conn, $sql)){}
	
});

/*
Holen aller Kita-Chat-Nachrichten
*/
$app->get('/kitachat', function (Request $request, Response $response) 
{ 
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$kitaid = $nutzer['kita_id'];
	
	
	$sql = "SELECT * FROM kitachat WHERE benutzer_id IN (SELECT id FROM benutzer WHERE kita_id='$kitaid') ORDER BY zeit ASC";
	$result = mysqli_query($conn, $sql);
	$i = 0;
		
	while($nachricht = mysqli_fetch_assoc($result)) 
	{
		$benutzer_id = $nachricht['benutzer_id'];
		
		$sql2 = "SELECT * FROM benutzer WHERE id='$benutzer_id'";
		$result2 = mysqli_query($conn, $sql2);
		$versender = mysqli_fetch_assoc($result2);
		
		if($versender['id'] == $nutzer['id']){$eigene = true;}else{$eigene = false;}

		
		$anfragen[$i] = array(
		'text' => $nachricht['text'],
		'zeit' => $nachricht['zeit'],
		
		'vorname' => $versender['vorname'], 
		'nachname' => $versender['nachname'],
		
		'eigene' => $eigene,
		'art' => $versender['art']);
		$i++;
	}
	echo json_encode($anfragen, JSON_PRETTY_PRINT);
});

/*
Alle angenommenen Babysitteranfragen holen
*/
$app->get('/babysitter/angenommen', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$benutzerid = $nutzer['id'];
	
	$sql = "SELECT * FROM babysitteranfrage WHERE angenommen_id='$benutzerid'";
	$result = mysqli_query($conn, $sql);
	$i = 0;
		
	while($anfrage = mysqli_fetch_assoc($result)) 
	{
		$benutzerid = $anfrage['benutzer_id'];
			
		$sql2 = "SELECT * FROM benutzer WHERE id='$benutzerid'";
		$result2 = mysqli_query($conn, $sql2);
		$benutzer = mysqli_fetch_assoc($result2);
			
		$kindid = $benutzer['kind_id'];
			
		$sql3 = "SELECT vorname, nachname FROM kind WHERE id='$kindid'";
		$result3 = mysqli_query($conn, $sql3);
		$kind = mysqli_fetch_assoc($result3);
			
		$anfragen[$i] = array(
		'id' => $anfrage['id'],
		'von' => $anfrage['von'],
		'bis' => $anfrage['bis'], 
		'beschreibung' => $anfrage['beschreibung'],
			
		'vorname' => $benutzer['vorname'],
		'nachname' => $benutzer['nachname'],
		'plz' => $benutzer['plz'],
		'stadt' => $benutzer['stadt'],
		'strasse' => $benutzer['strasse'],
		'telefon' => $benutzer['telefon'],
			
		'kindvorname' => $kind['vorname'],
		'kindnachname' => $kind['nachname']);
		$i++;
	}
	echo json_encode($anfragen, JSON_PRETTY_PRINT);
	   
});

/*
Babysitteranfrage annehmen
*/
$app->post('/babysitter/aktion', function (Request $request, Response $response) 
{ 
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$benutzerid = $nutzer['id'];
	
	$id = $_POST['id'];
	
	$sql = "UPDATE babysitteranfrage SET angenommen_id='$benutzerid' WHERE id='$id' AND angenommen_id='0'";
	if(mysqli_query($conn, $sql)){}
});

/*
Die eigene Babysitteranfrage setzen
*/
$app->post('/babysitter/eigene', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$von = $_POST['von'];
	$bis = $_POST['bis'];
	$beschreibung = $_POST['beschreibung'];
	
	$nutzer = holeNutzer();
	$benutzerid = $nutzer['id'];
	
	$sql = "SELECT * FROM babysitteranfrage WHERE benutzer_id='$benutzerid' AND angenommen_id='0'";
	$result = mysqli_query($conn, $sql);
	$anfrage = mysqli_fetch_assoc($result);
	
	if(mysqli_num_rows($result) == 1)
	{
		$sql2 = "UPDATE babysitteranfrage SET 
		von='$von',
		bis='$bis',
		beschreibung='$beschreibung'
		WHERE benutzer_id='$benutzerid'";
		if(mysqli_query($conn, $sql2)){}
	}
	else
	{
		$sql3 = "INSERT INTO babysitteranfrage (von, bis, beschreibung, benutzer_id) VALUES ('$von', '$bis', '$beschreibung', '$benutzerid')";
		if(mysqli_query($conn, $sql3)){}
	}
		   
});

/*
Die eigene Babysitteranfrage anzeigen
*/
$app->get('/babysitter/eigene', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$benutzerid = $nutzer['id'];
	
	$sql = "SELECT * FROM babysitteranfrage WHERE benutzer_id='$benutzerid'";
	$result = mysqli_query($conn, $sql);
	$anfrage = mysqli_fetch_assoc($result);
	
	$eanfrage = array(
	'von' => $anfrage['von'],
	'bis' => $anfrage['bis'], 
	'beschreibung' => $anfrage['beschreibung'],
	'angenommen' => $anfrage['angenommen_id']);
	
	echo json_encode($eanfrage, JSON_PRETTY_PRINT);
		   
});

/*
Alle relevanten Babysitteranfragen anzeigen
*/
$app->get('/babysitter', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();
	
	$nutzer = holeNutzer();
	$kitaid = $nutzer['kita_id'];
	$nutzerid = $nutzer['id'];
	$eigene_anfrage = false;
	
	$sql = "SELECT * FROM babysitteranfrage WHERE benutzer_id IN (SELECT id FROM benutzer WHERE kita_id='$kitaid') AND angenommen_id != '0'";
	$result = mysqli_query($conn, $sql);
	$i = 0;
		
	while($anfrage = mysqli_fetch_assoc($result)) 
	{
		$benutzerid = $anfrage['benutzer_id'];
		if($nutzerid == $benutzerid){$eigene_anfrage = true;}
		$sql2 = "SELECT id, vorname, nachname, kind_id FROM benutzer WHERE id='$benutzerid'";
		$result2 = mysqli_query($conn, $sql2);
		$benutzer = mysqli_fetch_assoc($result2);
			
		$kindid = $benutzer['kind_id'];
			
		$sql3 = "SELECT vorname, nachname FROM kind WHERE id='$kindid'";
		$result3 = mysqli_query($conn, $sql3);
		$kind = mysqli_fetch_assoc($result3);
			
		$anfragen[$i] = array(
		'id' => $anfrage['id'],
		'von' => $anfrage['von'],
		'bis' => $anfrage['bis'], 
		'beschreibung' => $anfrage['beschreibung'],
			
		'vorname' => $benutzer['vorname'],
		'nachname' => $benutzer['nachname'],
		'eigene_anfrage' => $eigene_anfrage,
		'kindvorname' => $kind['vorname'],
		'kindnachname' => $kind['nachname']);
		$i++;
		$eigene_anfrage = false;
	}
	echo json_encode($anfragen, JSON_PRETTY_PRINT);
	   
});

/*
Löschen der Session um sich auszuloggen
*/
$app->get('/logout', function (Request $request, Response $response) 
{   
	session_start(); 
	session_destroy();  
});

$app->post('/admin', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$art = $nutzer['art'];
	
	if($art == "Admin")
	{
		$kitaname = $_POST['kitaname'];
		$telefon = $_POST['telefon'];
		$strasse = $_POST['strasse'];
		$plz = $_POST['plz'];
		$stadt = $_POST['stadt'];
		$mail = $_POST['mail'];
		$homepage = $_POST['homepage'];
		
		$sql = "SELECT mail FROM kita WHERE mail='" . $mail ."'";
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) < 1) 
		{
			$sql2 = "INSERT INTO kita (name, telefon, strasse, plz, stadt, mail, homepage) VALUES ('$kitaname', '$telefon', '$strasse', '$plz', '$stadt', '$mail', '$homepage')";
			$result = mysqli_query($conn, $sql2);
		}
		else 
		{
			//Mail bereits vorhanden
			return $response->withStatus(401);
		}
	}
	else
	{
		//Nutzer nicht berechtigt
		return $response->withStatus(400);
	}
});

/*
Zufälligen Kitacode generieren
Es wird bis zu 9999 mal versucht einen zufälligen Code der noch nicht
existiert zuzuweisen. Wird in diesem Fenster kein Code gefunden schlägt
die Generierung fehl
*/
$app->post('/admin/kitacode', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$art = $nutzer['art'];
	
	if($art == "Admin")
	{
		$kita_id = $_POST['kitaID'];
		$i = 0;
		do
		{
			$zufaelligerString = zufaelligerString();
			$sql = "SELECT code FROM kita WHERE code='$zufaelligerString'";
			$result = mysqli_query($conn, $sql);
			$doppelt = mysqli_num_rows($result);
			if($doppelt == 0)
			{
				$sql2 = "UPDATE kita SET code='$zufaelligerString' WHERE id='$kita_id'";
				if(mysqli_query($conn, $sql2)){}
			}
			$i++;
		}while($doppelt>0 && $i<9999);
		if($i >= 9998)
		{
			//Codegenerierung fehlgeschlagen
			return $response->withStatus(401);
		}
		else
		{
			$kitacode = array('kitacode' => $zufaelligerString);
			echo json_encode($kitacode, JSON_PRETTY_PRINT);
		}
	}
	else
	{
		//Nutzer nicht berechtigt
		return $response->withStatus(400);
	}
});

/*
Holen der Admininfos
*/
$app->get('/admin', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$art = $nutzer['art'];
	
	if($art == "Admin")
	{
		$sql = "SELECT * FROM kita";
		$result = mysqli_query($conn, $sql);
		$i = 0;
		
		while($kita = mysqli_fetch_assoc($result)) 
		{
			$ansprechpartnerid = $kita['benutzer_id'];
			
			$sql2 = "SELECT * FROM benutzer WHERE id='$ansprechpartnerid'";
			$result2 = mysqli_query($conn, $sql2);
			$ansprechpartner = mysqli_fetch_assoc($result2);
			
			$kitas[$i] = array(
			'id' => $kita['id'],
			'name' => $kita['name'],
			'code' => $kita['code'], 
			'nummer' => $kita['telefon'],
			'strasse' => $kita['strasse'],  
			'plz' => $kita['plz'], 
			'ort' => $kita['stadt'], 
				
			'vorname' => $ansprechpartner['vorname'],
			'nachname' => $ansprechpartner['nachname']);
			$i++;
		}
		echo json_encode($kitas, JSON_PRETTY_PRINT);
	}
	else
	{
		//Nutzer nicht berechtigt
		return $response->withStatus(400);
	}
});

/*
Löschen einer Infokarte anhand der übergebenen ID
*/
$app->post('/infocard/delete', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$art = $nutzer['art'];
	
	if($art == "Admin" || $art == "KitaMit")
	{
		$infokarte_id = $_POST['id'];
		
		$sql = "DELETE FROM infokarte WHERE id='$infokarte_id'";
		if(mysqli_query($conn, $sql)){}
	}
	else
	{
		//Nutzer nicht berechtigt
		return $response->withStatus(400);
	}
});

/*
Aktualisieren einer Infokarte anhand der übergebenen ID
*/
$app->post('/infocard/update', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$art = $nutzer['art'];
	
	if($art == "Admin" || $art == "KitaMit")
	{
		$infokarte_id = $_POST['id'];
		$informationen = $_POST['informationen'];
		
		$sql = "UPDATE infokarte SET 
		information='$informationen'
		WHERE id='$infokarte_id'";
		if(mysqli_query($conn, $sql)){}
	}
	else
	{
		//Nutzer nicht berechtigt
		return $response->withStatus(400);
	}
});

/*
Holen aller Infokarten
*/
$app->get('/infocard', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php'); 
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$kitaid = $nutzer['kita_id'];

	$sql2 = "SELECT * FROM infokarte WHERE kita_id='$kitaid'";
	
	$result = mysqli_query($conn, $sql2);
	
	$i = 0;
	$infokarte = [];
	while($row = mysqli_fetch_assoc($result)) 
	{
		
		$infokarte[$i] = array(
		'id' => $row['id'], 
		'name' => $row['titel'], 
		'art' => $row['typ'], 
		'erstelldatum' => $row['erstelldatum'],
		'text' => $row['information'],
		'ablaufdatum' => $row['ablaufdatum'],
		'beginndatum' => $row['beginndatum'],
		'important' => $row['important']);
		$i++;
		echo json_encode($infokarte, JSON_PRETTY_PRINT);
	}
	   
});

/*
Holen aller Informationen um sie in die Timeline zu integrieren
*/
$app->get('/timeline', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php'); 
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$kitaid = $nutzer['kita_id'];

	$sql2 = "SELECT * FROM infokarte WHERE kita_id='$kitaid' AND important='1'";
	
	$result = mysqli_query($conn, $sql2);
	
	$i = 0;
	while($row = mysqli_fetch_assoc($result)) 
	{
		
		$infokarte[$i] = array(
		'id' => $row['id'], 
		'name' => $row['titel'], 
		'art' => $row['typ'], 
		'erstelldatum' => $row['erstelldatum'],
		'text' => $row['information'],
		'ablaufdatum' => $row['ablaufdatum'],
		'beginndatum' => $row['beginndatum'],
		'important' => $row['important']);
		$i++;
		echo json_encode($infokarte, JSON_PRETTY_PRINT);
	}
	   
});

/*
Setzen einer Infokarte
*/
$app->post('/infocard', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$titel = $_POST['name'];
	$typ = $_POST['art'];
	$information = $_POST['information'];
	$ablaufdatum = $_POST['ablaufdatum'];
	$beginndatum = $_POST['beginndatum'];
	$important = $_POST['important'];

	$nutzer = holeNutzer();
	$art = $nutzer['art'];
	$kitaid = $nutzer['kita_id'];
	$datum = date("Y-m-d");
	
	if($art == "Admin" || $art == "KitaMit")
	{
		$sql = "INSERT INTO infokarte (titel, information, typ, ablaufdatum, kita_id, erstelldatum, beginndatum, important)VALUES 
			('$titel',
			'$information',
			'$typ',
			'$ablaufdatum',
			'$kitaid',
			'$datum',
			'$beginndatum',
			'$important')";
			
		if(mysqli_query($conn, $sql)){}
	}
	else
	{
		//Kein berechtigter Mitarbeiter
		return $response->withStatus(400);
	}
});

/*
Einstellungen speichern
*/
$app->post('/settings', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$mail = $_POST['mail'];
	$vorname = $_POST['vorname'];
	$nachname = $_POST['nachname'];
	$strasse = $_POST['strasse'];
	$plz = $_POST['plz'];
	$ort = $_POST['ort'];
	$rufnummer = $_POST['rufnummer'];
	$pushnotifikation = $_POST['pushnotifikation'];
	
	$kindervorname = $_POST['kindervorname'];
	$kindernachname = $_POST['kindernachname'];
	$kinderjahr = $_POST['kinderjahr'];
	$kinderkrankheit = $_POST['kinderkrankheit'];
	$kindermedikamente = $_POST['kindermedikamente'];
	
	$notfallkontaktvorname = $_POST['notfallkontaktvorname'];
	$notfallkontaktnachname = $_POST['notfallkontaktnachname'];
	$notfallkontaktstrasse = $_POST['notfallkontaktstrasse'];
	$notfallkontaktplz = $_POST['notfallkontaktplz'];
	$notfallkontaktort = $_POST['notfallkontaktort'];
	$notfallnummer = $_POST['notfallnummer'];
	
	$nutzer = holeNutzer();
	$kindid = $nutzer['kind_id'];
	$notfallkontaktid = $nutzer['notfallkontakt_id'];
	
	$id = $_SESSION['id'];
	
	$sql = "UPDATE benutzer SET 
	mail='$mail',
	vorname='$vorname',
	nachname='$nachname',
	strasse='$strasse',
	plz='$plz',
	stadt='$ort',
	telefon='$rufnummer',
	push='$pushnotifikation'
	WHERE id='$id'";
	if(mysqli_query($conn, $sql)){}
			
	if($kindid == 0)
	{
		$sql2 = "INSERT INTO kind (vorname, nachname, geburtsdatum, krankheiten, medikamente)VALUES 
		('$kindervorname',
		'$kindernachname',
		'$kinderjahr',
		'$kinderkrankheit',
		'$kindermedikamente')";
		if(mysqli_query($conn, $sql2)){}else{}
				
		$kindid_neu = mysqli_insert_id($conn);
		$sql3 = "UPDATE benutzer SET kind_id='$kindid_neu' WHERE id='$id'";
		if(mysqli_query($conn, $sql3)){}	
	}
	else
	{
		$sql4 = "UPDATE kind SET 
		vorname='$kindervorname',
		nachname='$kindernachname',
		geburtsdatum='$kinderjahr',
		krankheiten='$kinderkrankheit',
		medikamente='$kindermedikamente'
		WHERE id='$kindid'";
		if(mysqli_query($conn, $sql4)){}
	}
		
	if($notfallkontaktid == 0)
	{
		$sql65 = "INSERT INTO notfallkontakt (vorname, nachname, strasse, plz, stadt, telefon)VALUES 
		('$notfallkontaktvorname',
		'$notfallkontaktnachname',
		'$notfallkontaktstrasse',
		'$notfallkontaktplz',
		'$notfallkontaktort',
		'$notfallnummer')";
		if(mysqli_query($conn, $sql5)){}
				
		$notfallkontaktid_neu = mysqli_insert_id($conn);
		$sql6 = "UPDATE benutzer SET notfallkontakt_id='$notfallkontaktid_neu' WHERE id='$id'";
		if(mysqli_query($conn, $sql6)){}
	}
	else
	{
		$sql7 = "UPDATE notfallkontakt SET 
		vorname='$notfallkontaktvorname',
		nachname='$notfallkontaktnachname',
		strasse='$notfallkontaktstrasse',
		plz='$notfallkontaktplz',
		stadt='$notfallkontaktort',
		telefon='$notfallnummer'
		WHERE id='$notfallkontaktid'";
		if(mysqli_query($conn, $sql7)){}
	}
});

/*
Einstellungen holen
*/
$app->get('/settings', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	$nutzer = holeNutzer();
	$kindid = $nutzer['kind_id'];
	$notfallkontaktid = $nutzer['notfallkontakt_id'];
	
	$sql = "SELECT * FROM kind WHERE id='$kindid'";
	$result = mysqli_query($conn, $sql);
	$kind = mysqli_fetch_assoc($result);
			
	$sql2 = "SELECT * FROM notfallkontakt WHERE id='$notfallkontaktid'";
	$result2 = mysqli_query($conn, $sql2);
	$notfallkontakt = mysqli_fetch_assoc($result2);
			
	$settings = array(
	'mail' => $nutzer['mail'],
	'vorname' => $nutzer['vorname'], 
	'nachname' => $nutzer['nachname'],
	'strasse' => $nutzer['strasse'],  
	'plz' => $nutzer['plz'], 
	'ort' => $nutzer['stadt'], 
	'rufnummer' => $nutzer['telefon'], 
	'pushnotifikation' => $nutzer['push'], 
			
	'kindervorname' => $kind['vorname'],
	'kindernachname' => $kind['nachname'],
	'kinderjahr' => $kind['geburtsdatum'],
	'kinderkrankheit' => $kind['krankheiten'],
	'kindermedikamente' => $kind['medikamente'],
			
	'notfallkontaktvorname' => $notfallkontakt['vorname'],
	'notfallkontaktnachname' => $notfallkontakt['nachname'],
	'notfallkontaktstrasse' => $notfallkontakt['strasse'],
	'notfallkontaktplz' => $notfallkontakt['plz'],
	'notfallkontaktort' => $notfallkontakt['stadt'],
	'notfallnummer' => $notfallkontakt['telefon']);
	
	echo json_encode($settings, JSON_PRETTY_PRINT);
});

/*
Einloggen. Bei Erfolg wird eine Sitzung gestartet
*/
$app->post('/login', function (Request $request, Response $response) 
{
	session_start(); 
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	if(isset($_POST['mail']) && isset($_POST['password']))
	{
		$mail = mysqli_real_escape_string($conn,trim($_POST['mail']));
		$password = $_POST['password'];
		
		$sql = "SELECT id,passwort,art FROM benutzer WHERE mail='$mail'";
		
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) == 1) 
		{
			$row = mysqli_fetch_assoc($result);
			
			if(password_verify($password, $row['passwort']))
			{
				$_SESSION['id'] = $row['id'];
				
				$art = array('art' => $row['art']);
				echo json_encode($art, JSON_PRETTY_PRINT);
				//Eingelogt
			}
			else
			{
				//Mail und passwort passen nicht zusammen
				return $response->withStatus(400);
			}
		} 
		else 
		{
			//Mail nicht vorhanden
			return $response->withStatus(401);
		}
}
else
{
	//Nicht alle Daten ausgefüllt
	return $response->withStatus(402);
}
});

/*
Registrieren
*/
$app->post('/register', function (Request $request, Response $response) 
{
	require_once('PHP/funktionen.php');
	$conn = dbVerbindung();

	if(isset($_POST['mail']) && isset($_POST['password']) && isset($_POST['kitacode']) && isset($_POST['vorname']) && isset($_POST['nachname']))
	{
		$mail = mysqli_real_escape_string($conn,trim($_POST['mail']));
		$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		$kitacode = mysqli_real_escape_string($conn,trim($_POST['kitacode']));
		$vorname = mysqli_real_escape_string($conn,$_POST['vorname']);
		$nachname = mysqli_real_escape_string($conn,$_POST['nachname']);
		
		$sql = "SELECT id FROM benutzer WHERE mail='" . $mail ."'";
		
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) < 1) 
		{
			if(filter_var($mail, FILTER_VALIDATE_EMAIL))
			{
				$sql2 = "SELECT id FROM kita WHERE code='$kitacode'";
				$result2 = mysqli_query($conn, $sql2);
				if (mysqli_num_rows($result2) < 1) 
				{	
					//Kitacode nicht vorhanden
					return $response->withStatus(403);
				}
				else
				{
					$row = mysqli_fetch_assoc($result2);
					$id = $row['id'];
					$sql = "INSERT INTO benutzer (vorname, nachname, passwort, mail, art, kita_id) VALUES ('$vorname', '$nachname', '$password', '$mail', 'Eltern', '$id')";
					$result = mysqli_query($conn, $sql);
				}
			}
			else
			{
				//Ungültige Mail
				return $response->withStatus(400);
			}
		} 
		else 
		{
			//Mail bereits vorhanden
			return $response->withStatus(401);
		}
	}
	else
	{
		//Nicht alle Daten angegeben
		return $response->withStatus(402);
	}
});
$app->run();
?>
 