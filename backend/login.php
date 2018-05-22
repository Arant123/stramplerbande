<?php	
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
?>