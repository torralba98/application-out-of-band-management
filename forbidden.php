<?php
	$root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>

<!doctype html>
<html lang="en">
	
	<head>
		
		<title>Panel Admin ~ Usuarios Registrados</title>
		<link rel="icon" type="image/png" href="/images/icon.png" />
    
		<?php 
			include "$root/web/header.php"; 
		?>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- CSS -->
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/alerts.css">
		
	</head>

	<body background="/images/background.jpg" onLoad="WriteToFile()">

		<div class="container">
			<div class='alert alert-success mt-4' role='alert'>
				<br>
				<FONT color=red SIZE=4><i><p><a><b>¡UPS! Parece que has intentado colarte en la consola de un dispositivo. Eso no está bien...</b></a></p></i></font>
				<FONT color=red SIZE=2><i><p><a><b><u>Se notificará al Administrador.</u></b></a></p></i></font>
				<br>
				<p><a class="btn btn-info" href='inicio'>Volver al Inicio</a></p>
			</div>
		</div>
		
	</body>
	
    <?php 
	  include "$root/web/footer.php"; 
    ?>
  
</html>
