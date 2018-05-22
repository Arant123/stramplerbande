<?php
function dbVerbindung()
{
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "strampler";

	$conn = mysqli_connect($servername, $username, $password, $dbname);
	mysqli_set_charset($conn, "utf8");
	
	if (!$conn) 
	{
		die("Datenbankverbindung fehlgeschlagen: " . mysqli_connect_error());
	}
	return $conn;
}
	
function holeNutzer()
{
	$conn = dbVerbindung();
	$id = $_SESSION['id'];
	$sql = "SELECT * FROM benutzer WHERE id='$id'";
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row;
}

function zufaelligerString($laenge = 5)
{
	$zeichen = "abcdefghijklmnopqrstuvwxyz0123456789";
	$ergebnis = "";
	for($i = 0; $i < $laenge; $i++)
	{
		$zufallszahl = random_int(0, strlen($zeichen)-1);
		$ergebnis .= $zeichen[$zufallszahl];
	}
	return $ergebnis;
}

?>