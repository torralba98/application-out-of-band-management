<?php
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;

   require 'PHPMailer/Exception.php'; 
   require 'PHPMailer/PHPMailer.php';
   require 'PHPMailer/SMTP.php';

   header('Content-Type: text/html; charset=UTF-8');
   session_start();

   $root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>

<!doctype html>
<html lang="es">
	
   <head>
		
	<style>

		 #title {
			background-color: #104a1d;
			color: #c1d9c7;
			width: 420px;
			height: 25px;
			text-align: center;
			border-radius: 10px;
		  }

		.alert-success{
			border-radius: 10px;
			width: 870px;
		  }

		#deviceOff{
			margin-left:100px;
			border-radius: 10px;
		  }

		.dark{
			border-bottom:solid 1px black; 
			width:66%;
			margin: 30px;
			margin-left:17%;   		   
		  }

		.pageCover {
			position:fixed;
			z-index:0;
			background-color:rgba(0,0,0,.25);
			width:100vw;
			height:100vh;
			top:0;
			left:0;
		}

	</style>

	<title>Inicio</title>
	<link rel="icon" type="image/png" href="/images/icon.png" />
	<link rel="stylesheet" href="../css/alerts.css">
	<link rel="stylesheet" href="../css/pop-up.css">
	<link rel="stylesheet" href="../css/bootstrap.min.css">

	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- CSS -->
	<script src="/jquery/jquery-3.5.1.min.js"></script>

	<script>			

		var ctrlKeyDown = false;

		$(document).ready(function(){    
			$(document).on("keydown", keydown);
			$(document).on("keyup", keyup);
		});

		function keydown(e) { 

			if ((e.which || e.keyCode) == 116 || ((e.which || e.keyCode) == 82 && ctrlKeyDown)) {
				// Pressing F5 or Ctrl+R
				e.preventDefault();
			} else if ((e.which || e.keyCode) == 17) {
				// Pressing  only Ctrl
				ctrlKeyDown = true;
			}
		};

		function keyup(e){
			// Key up Ctrl
			if ((e.which || e.keyCode) == 17) 
				ctrlKeyDown = false;
		};

		function popUp(){
		  document.getElementById("pop-up").click();
		}

		if ( window.history.replacein_use ) {
			window.history.replacein_use( null, null, window.location.href );
		}

		function countDown() {

			var seg = localStorage.getItem('segs');
		  var min = localStorage.getItem('mins');

			if ((seg == 0 && min == 0) || min < 0 || seg < 0 || is_null(min) || is_null(seg)) {
				seg = 0;
				min = 3;
		  }

		onTimer();

		function onTimer() {
			document.getElementById('timer').innerHTML = " • Tendrás que esperar <b>" + min + "m " + seg + "s</b> antes de que podamos enviarte otro.";
			seg--;
			if (seg < 0 && min == 0) {
				localStorage.setItem('mins', 0);
				localStorage.setItem('segs', 0);
				window.location.href = "inicio";
			}else {
			   if (seg == -1){
				 if (min > 0) {
					min--;
					seg = 59;
				 }
			    }

			  setTimeout(onTimer, 1000);
			  localStorage.setItem('mins', min);
			  localStorage.setItem('segs', seg);
			}
		  }
		}
	</script>

     </head>

	<body background="/images/background.jpg">
		
		<div class="ocultar">
			<b id="pop_up"></b>
			<a id='pop-up' href="#pop_up" ></a>

			<div class="popup">
				<div>
				   <h4>¡Hola!</h4>
				   <p>Para cualquier problema relacionado con la aplicación, comunícate con el profesorado.</p>
				   <a  href="" class="btn">cerrar</a>
				</div>
			</div>
		</div>
		
		<br><br><br>
		<div id="header"></div>
		<div class="container">

		   <?php

			// Connection info. file
			include "./web_config/configuration_properties.php";

			// Connection variables
			$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

			// Check connection
			if (!$conn) {
			  die("Connection failed: " . mysqli_connect_error());
			}

			if (isset($_SESSION['username'])){
				if (time() - $_SESSION['start'] > 3600) {
					   session_unset($_SESSION['username']);
						 session_destroy();
						 header("Location: index");
						 die();
				} else {
				$username = $_SESSION['username'];
					include "$root/web/header.php";

					$result = mysqli_query($conn, "SELECT is_admin FROM  user WHERE username = '$username' AND is_admin = 1");

					// Variable $row hold the result of the query
					$row = mysqli_fetch_assoc($result);

					if ($row > 0)	
						adminAccess($username, $conn);
					else
						userAccess($username, $conn);
				}

			} else {

				// Check connection
				if (!$conn) {
					die("Connection failed: " . mysqli_connect_error());
				}

				if (isset($_SESSION['username']))
					$username = $_SESSION['username'];

				// data sent from form index.php
				if (isset($_POST['username']) && isset($_POST['password'])) {
					$username = $_POST['username'];
					$password = $_POST['password'];

					// Query sent to database
					$result = mysqli_query($conn, "SELECT username, password FROM user WHERE username = '$username'");

					// Variable $row hold the result of the query
					$row = mysqli_fetch_assoc($result);

					if ($row > 0){

						// Variable $hash hold the password hash on database
						$md5 = $row['password'];
						$username = $row['username'];

						if (md5($_POST['password']) == $md5) {

							$_SESSION['loggedin'] = true;
							$_SESSION['username'] = $username;
							$_SESSION['start'] = time();
							if (!isset($_SESSION['popup']))
								$_SESSION['popup'] = true;

							include "$root/web/header.php";
							$username = $row['username'];

							$result = mysqli_query($conn, "SELECT is_admin FROM  user WHERE username = '$username' AND is_admin = 1");

							// Variable $row hold the result of the query
							$row = mysqli_fetch_assoc($result);

							if ($row > 0)	
								adminAccess($username, $conn);
							else
								userAccess($username, $conn);

						} else {
							$username = "";
								include "$root/web/header.php";
								incorrectPassword();
						 }
					} else {
						  $username = "";
							include "$root/web/header.php";
							unknownUsername();
						}
				} else{
						$username = "";
						include "$root/web/header.php";
						needLogin();
				  }
			}

			function userAccess($username, $conn) {
				$init = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username' AND verified = 'YES' ORDER BY username DESC");
				if (!empty($init) && mysqli_num_rows($init) == 0) {
						echo "<p style='margin-left: 2em; color:red'> <br><b>Todavía no has <u>verificado</u> tu cuenta.</b><br><br></p>";
						echo "<br><b>¿No te llegó el e-mail de verificación?";
						echo "<br><br>";

						echo "<form action='' method='post'>";
						echo "<p style='margin-left: 2em' id='confirm'> </p>";
						echo "<p id='timer' style='margin-left: 2em'>  • Click ";
						echo "<input type='submit' style='background-color:transparent;border:none;color:red;overflow: hidden;text-decoration:underline;' name='button1' value='aquí'/>";
						echo " para reenviártelo.</b><br>";
						echo "</p>";
						echo "</form>";

		?>

		<script>

		var seg = localStorage.getItem('segs');
		var min = localStorage.getItem('mins');

		if (seg != 0 || min != 0)
			countDown();

		</script>

		<?php

			include "./web_config/configuration_properties.php";

			$token = md5($username).rand(10,99999);

			$link = "<a href=".$web_url."/verify?user=".$username."&amp;token=".$token.">Click para verificar tu cuenta.</a>";

			$mail = new PHPMailer(true);

			$mail->CharSet =  "utf-8";
			$mail->SMTPDebug = 0;
			$mail->IsSMTP();
			$mail->Host = "smtp.gmail.com";
			$mail->SMTPAuth = true;
			$mail->Username = $emailPHP;
			$mail->Password = $emailPHPpass;
			$mail->SMTPSecure = "tls";
			$mail->Port = 587;

			$mail->setFrom($emailPHP, 'Soporte CiberSec - UDC');
			$mail->AddAddress($username . "@udc.es");

			$mail->IsHTML(true);
			$mail->Subject  =  'Máster CiberSec - Verifica tu Cuenta';
			$mail->Body    = '¡Enhorabuena, te has registrado con éxito! <br><br> Para finalizar el registro necesitamos que verifiques tu cuenta haciendo
													click en el siguiente enlace. <br> '.$link.'';


			if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['button1'])) {
		?> <script> 
				countDown();
		   </script> 
		<?php
				$root = realpath($_SERVER["DOCUMENT_ROOT"]);
				include "$root/web/header.php";
				echo "<img id='loading' src='images/loading.gif' class='pageCover' >";
				include "$root/web/footer.php";
				flush();
				ob_flush();
				sleep(0.01);

				flush();
				ob_flush();
				sleep(1);
						if($mail->Send()) {
							echo "<script> document.getElementById('loading').style.display = 'none'; </script>";
							$result = mysqli_query($conn, "UPDATE user SET verify_token = '$token' WHERE username = '$username'");
							if (!$result)
								die('Invalid query: ' . mysql_error());
						?>
							<script> document.getElementById('confirm').innerHTML =  "• E-mail de verificación <u>enviado</u> con éxito."; </script>
						<?php
					}else{
						echo "<script> document.getElementById('loading').style.display = 'none'; </script>";
						?>
							<script> document.getElementById('confirm').innerHTML =  "• <u>Error</u> al enviar el e-mail de verificación. Inténtelo de nuevo más tarde."; </script>
						<?php
					}
					echo "<script>window.location.href = ".$web_url."/inicio';</script>";
			}

		} else {
	?>
	<script>
		localStorage.setItem('mins', 0);
		localStorage.setItem('segs', 0);
	</script>
	<?php

		$init2 = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username' AND user_group_id IS NOT NULL ORDER BY username DESC");

		include "./web_config/configuration_properties.php";
		$devices = simplexml_load_file($web_url."/web_config/devices_info.xml");

		echo "<div id='deviceOff' class='alert alert-success mt-4' role='alert'>";
		if (!empty($init2) && mysqli_num_rows($init2) == 0)
			 echo "<p style='margin-left: 2em; color:red'> <br><b>Todavía no tienes ningún <u>Grupo de Dispositivos</u> asignado.</b><br><br>";
		else {

					echo "<div id='title'><FONT SIZE=4><p><a><i><b>DISPOSITIVOS ACCESIBLES</b><i></a></p></FONT></div><br><br>";
					echo "<div><a id='actualizar' href='inicio' class='btn btn-primary btn-lg active'>Actualizar disp.</a></div>";

					$result = mysqli_query($conn, "SELECT d.Id, username, in_use FROM user u JOIN user_group ug ON u.user_group_id=ug.id JOIN device_group dg ON ug.device_group_id_assigned=dg.id JOIN device d ON dg.id=d.device_group_id WHERE u.username = '$username' AND in_use = 'NO'");

					printf ("<form action='' method='post'>");
					if (mysqli_num_rows($result)==0)
						 echo "<p style='margin-left: 2em; color:red'> No hay ningún <u>dispositivo libre</u>.<br><br>";
					else {

						while ($row = mysqli_fetch_array($result)) {
							$flagExist = false;
							$description = "- (Este dispositivo no tiene ningún comentario adicional)";
							foreach($devices as $device)
								if ($device->idDb == $row[0]) {
									$flagExist = true;
									$description = "- (".$device->description.")";
									$deviceName = $device->name;
								}

								if ($flagExist){
									echo "<form action='' method='post'>";
											echo "<p style='margin-left: 2em'><b> ► $deviceName &nbsp;~</b>";
											echo "<input style='background-color:red;' name='namedevice' value='$deviceName' hidden/>";
											echo "&nbsp;&nbsp;&nbsp;&nbsp;<label style='padding-top: 8px;'>$description</label>";
											echo "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»&nbsp;&nbsp;<input type='submit' id='entrarCons$deviceName' class='large green button' name='enterConsole' value='Entrar a la Consola    '/>";
											$int = $int + 1;
										    if ((mysqli_num_rows($result)>1) && (mysqli_num_rows($result)>$int))
													echo "<br><br><div class='dark'></div>";
									echo "</form><br>";
								}
						}
					}

					if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['enterConsole'])) {
						  initiateServer($conn, $_POST['namedevice'], $username);
					}

					echo "</div><div id='deviceOff' class='alert alert-success mt-4' role='alert'><div id='title'><FONT SIZE=4><p><a><i><b>DISPOSITIVOS NO ACCESIBLES</b><i></a></p></FONT></div><br>";

					$result = mysqli_query($conn, "SELECT d.Id, used_by FROM user u JOIN user_group ug ON u.user_group_id=ug.id JOIN device_group dg ON ug.device_group_id_assigned=dg.id JOIN device d ON d.device_group_id=dg.id WHERE u.username = '$username' AND in_use = 'YES'");
					if (mysqli_num_rows($result)==0)
						 echo "<br><p style='margin-left: 2em; color:red'> No hay ningún <u>dispositivo en uso</u>.<br><br>";

					include "./web_config/configuration_properties.php";
					$devices = simplexml_load_file($web_url."/web_config/devices_info.xml");

					while ($row = mysqli_fetch_array($result)) {
						foreach($devices as $device)
							if ($device->idDb == $row[0]) {
								$deviceName = $device->name;
							}
						printf("<p style='margin-left: 2em'> • El dispositivo <b>$deviceName</b> está siendo usado ahora mismo por el usuario <b>%s</b>.<br>", $row[1]);
					}
			}
		  }



		}

		function getRootDir() {
			$rootDir = "";
			$root = getcwd();
			$root = explode('\\', $root);
			foreach($root as $str)
				if(!empty($str))
				$rootDir = $rootDir . $str . "/";
			return $rootDir;
		}

		function adminAccess($username, $conn) {


			echo "<div id='deviceOff' class='alert alert-success mt-4' role='alert'>
						<div id='title'><FONT SIZE=4><p><a><i><b>TODOS LOS DISPOSITIVOS ACCESIBLES</b> <i></a></p></FONT></div><br><br>";
			echo "<div><a id='actualizar' href='inicio' class='btn btn-primary btn-lg active'>Actualizar disp.</a></div>";

			$result = mysqli_query($conn, "SELECT Id, in_use FROM device WHERE in_use = 'NO'");

			printf ("<form action='' method='post'>");
			if (mysqli_num_rows($result)==0)
				 echo "<p style='margin-left: 2em; color:red'> No hay ningún <u>dispositivo accesible</u>.<br><br>";
			else {
					include "./web_config/configuration_properties.php";

					$devices = simplexml_load_file($web_url."/web_config/devices_info.xml");

					$int = 0;
					while ($row = mysqli_fetch_array($result)) {
						$flagExist = false;
						$description = "(Este dispositivo no tiene ningún comentario adicional)";
						foreach($devices as $device)
							if ($device->idDb == $row[0]) {
								$flagExist = true;
								$description = "(".$device->description.")";
								$deviceName = $device->name;
							}
							if ($flagExist){
								echo "<form action='' method='post'>";
												echo "<p style='margin-left: 2em'><b> ► $deviceName &nbsp;~</b>";
												echo "<input style='background-color:red;' name='namedevice' value='$deviceName' hidden/>";
												echo "&nbsp;&nbsp;&nbsp;&nbsp;<label style='padding-top: 8px;'>$description</label>";
												echo "<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»&nbsp;&nbsp;<input type='submit' id='entrarCons$deviceName' class='large green button' name='enterConsole' value='Entrar a la Consola    '/>";
												$int = $int + 1;
											    if ((mysqli_num_rows($result)>1) && (mysqli_num_rows($result)>$int))
														echo "<br><br><div class='dark'></div>";
								echo "</form><br>";
							}
					
					}
			}

			if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['enterConsole'])) {
				initiateServer($conn, $_POST['namedevice'], $username);
			}

			echo "</div><div id='deviceOff' class='alert alert-success mt-4' role='alert'><div id='title'><FONT SIZE=4><p><a><i><b>TODOS LOS DISPOSITIVOS NO ACCESIBLES</b><i></a></p></FONT></div><br>";

			$result = mysqli_query($conn, "SELECT Id, used_by FROM device WHERE in_use = 'YES'");
			if (mysqli_num_rows($result)==0)
				 echo "<br><p style='margin-left: 2em; color:red'> No hay ningún <u>dispositivo en uso</u>.<br><br>";
				 
		    include "./web_config/configuration_properties.php";
			$devices = simplexml_load_file($web_url."/web_config/devices_info.xml");
			
			while ($row = mysqli_fetch_array($result)) {
				foreach($devices as $device)
					if ($device->idDb == $row[0]) {
						$deviceName = $device->name;
						
					}
				printf("<form action='' method='post'>
								<p style='margin-left: 2em'> • El dispositivo <b>$deviceName</b> está siendo usado ahora mismo por el usuario <b>%s</b>.
								</form>", $row[1]);
			}
		}

		function incorrectPassword() {
			echo "<div class='alert alert-danger mt-4' role='alert'>Lo sentimos, la contraseña introducida no es correcta.
			<p><a href='index'><br><strong>Por favor, inténtelo de nuevo.</strong></a></p></div>";
		}

		function unknownUsername() {
			echo "<div class='alert alert-danger mt-4' role='alert'>Lo sentimos, el usuario introducido no existe.
			<p><a href='index'><br><strong>Por favor, inténtelo de nuevo.</strong></a></p></div>";
		}

		function needLogin() {
			echo "<div class='alert alert-danger mt-4' role='alert'>Lo sentimos, necesitas iniciar sesión para acceder a esta página.
			<p><a href='index'><br><strong>Volver a la página de inicio.</strong></a></p></div>";
		}

		function initiateServer($conn, $device, $username){
			
			include "./web_config/configuration_properties.php";

			$devices = simplexml_load_file($web_url."/web_config/devices_info.xml");

			foreach($devices as $device1)
				if ($device1->name == $device) {
					$deviceId = $device1->idDb;
				}

				$result = mysqli_query($conn, "SELECT Id, in_use FROM device WHERE Id = '$deviceId' AND in_use = 'NO'");
				if (!mysqli_num_rows($result)==0){


						foreach($devices as $device1)
							if ($device1->idDb == $deviceId) {
								$name = $device1->name;
								$com = $device1->com;
								$port = $device1->port;
								$baudRate = $device1->baudRate;
								$dataBits = $device1->dataBits;
								$stopBits = $device1->stopBits;
								$flowControl = $device1->flowControl;
								$lock = $device1->lock;
								}

						 $server_status = mysqli_query($conn, "SELECT Id, in_use FROM device WHERE Id = '$deviceId' AND server_status = 'OFF'");

						 echo "<img src='images/loading.gif' class='pageCover'>";
						 include "$root/web/footer.php";
						 echo "<div class='pageCover'></div>";
						 flush();
						 ob_flush();
						 sleep(0.1);
						 $token2 = md5(time()).rand(10,9999);

						 $add2 = mysqli_query($conn, "UPDATE device SET token = '$token2' WHERE Id = '$deviceId'");
							if (!$add2)
								die('Invalid query: ' . mysql_error());

							if (!mysqli_num_rows($server_status)==0) {
								
								$rootDir = getRootDir();
								$rootDir = $rootDir . "server_node/server.js";
								$processId = shell_exec("DISPLAY=:0.0 xterm -hold -e 'node $rootDir $com $port $baudRate $dataBits $stopBits $flowControl $lock $deviceId $name' > /dev/null 2>&1 & echo $!");	
								
								flush();
								ob_flush();
								sleep(3.2);
								
								$processId = substr($processId,0,strlen($processId)-1);
							}
							flush();
							ob_flush();
							sleep(4.3);
							$in_use = mysqli_query($conn, "UPDATE device SET in_use = 'YES' WHERE Id = '$deviceId'");
							echo "<script type='text/javascript'>window.top.location='console$deviceId?pidConsole=$processId&user=$username&port=$port&token=$token2&deviceName=$device';</script>";
				 }
				else {
					echo "<div id='dialog'>";
					 echo "<div id='dialog-bg'>";
								echo "<div id='dialog-title'>¡Ups!</div>";
								 echo "<div id='dialog-description'>Lo sentimos, el dispositivo <b>$device</b> ya está siendo usado por otro usuario.</div>";
									 echo "<div id='dialog-buttons'>";
									 echo "<a href='inicio' class='large green button'>Aceptar</a>";
							echo "</div>";
						echo "</div>";
					 echo "</div>";
					 echo "<div class=' pageCover'></div>";
				}
			}

			mysqli_close($conn);

		?>

		</div>

  </body>
	
<br><br><br><br>

<?php
	if (isset($_SESSION['username'])){
		if ($_SESSION['popup'] == true){
			echo "<script> popUp();</script>";
			echo "<div class='pageCover'></div>";
			$_SESSION['popup'] = false;
		}
	}
	include "$root/web/footer.php"; 
?>

</html>
