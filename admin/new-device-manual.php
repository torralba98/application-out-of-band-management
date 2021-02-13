<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

include '../web_config/configuration_properties.php';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (isset($_SESSION['username'])){
  if (time() - $_SESSION['start'] > 3600) {
       session_unset($_SESSION['username']);
       session_destroy();
       header("Location: ../index");
       die();
  } else {

    $username = $_SESSION['username'];
    $isAdm = mysqli_query($conn, "SELECT is_admin FROM user WHERE username = '$username'");

    while ($isAdmRow = mysqli_fetch_array($isAdm)) {

      if ($isAdmRow[0] == 0){
        header('Location: ../index');
        die() ;
      }
    }
  }
}else{
header('Location: ../index');
 die() ;
}

mysqli_close($conn);

$root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>

<!doctype html>
<html lang="en">
	<head>
   <title>Panel Admin ~ Crear Dispositivo Manualmente</title>
   <link rel="icon" type="image/png" href="/images/icon.png" />
   <?php include "$root/web/header.php"; ?>
   <link rel="stylesheet" href="../css/alerts.css">
   <link rel="stylesheet" href="../admin/css/admin.css">

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
			<link rel="stylesheet" href="../css/bootstrap.min.css">
      </head>

	<body background="/images/background.jpg">

    <br>
    <nav class='menuHK container'><ul>
    <li><a href="registered-users">Usuarios</a>|</li>
    <li><a href="devices">Dispositivos</a>|</li>
    <li><a href="logs">Logs</a>|</li>
    <li><a href="assignments">Asignaciones Users/Devices</a>|</li>
    <li><a href="reset-devices">Resetear Dispositivos</a></li>
    </ul></nav>

		<div class="container">

    <style>
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

		<div id='all' class='alert alert-success mt-4' role='alert'>
          <div><a id='volver' href='new-device' class='large green button'>Volver</a></div>
          <FONT SIZE=4><i><p id='title'><a>Aquí podrás crear un <u>dispositivo manualmente</u>.</u></a></p></i></font>
              <br>

              <form id='first' action='' method='post'>
                <p><b>Primero será necesario crear una nueva entrada en la base de datos -</b>
                  <input type='submit' class='cont large green button' name='newDevice' value='Crear Entrada     ' onclick=''/>
                </p>
            </form>

            <div id='code'>
            </div>

                <?php

                if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['newDevice'])) {

                  // Connection variables
                  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  // Check connection
                  if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                  }

                   $add = mysqli_query($conn, "INSERT INTO device (server_status, in_use) VALUES ('OFF', 'NO')");
                   if (!$add)
                     die('Invalid query: ' . mysql_error());

                  echo "<script> document.getElementById('first').style.display = 'none'; </script>";

                  echo "<script> var html = document.createElement('div'); </script>";

                  echo "<script> html.innerHTML = `<div> <b>¡Listo! Se ha creado la entrada con éxito.</b><br><br>";

                  echo "A continuación copia, modifica e inserta el siguiente código en el archivo XML de configuración de los dispositivos.<br><br>";

                  echo "<p style='margin-left: 2em; color:red'> &lt;device><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;name>&lt;/name><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;port>&lt;/port><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;com>&lt;/com><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;description>&lt;/description><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;baudRate>&lt;/baudRate><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;dataBits>&lt;/dataBits><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;stopBits>&lt;/stopBits><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;flowControl>&lt;/flowControl><br>";
                  echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;lock>&lt;/lock><br>";

                  $result = mysqli_query($conn, "SELECT Id FROM device ORDER BY Id DESC LIMIT 1");

                  while ($row = mysqli_fetch_array($result)) {
                    $id = $row[0];
                    echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&lt;idDb>$id&lt;/idDb><br>";
                  }

                  echo "&lt;/device></p><br>";

                  echo "<b>IMPORTANTE.</b> Asegurate de no modificar el atributo <i><u>idDb</u></i> pues será el que enlace al dispositivo con su correspondiente entrada en la base de datos.";

                  echo "</div>`;</script>";

                  echo "<script> document.getElementById('code').appendChild(html);</script>";

                  mysqli_close($conn);
                }

                ?>

              </p>
              <br>
		</div>

	</body>
  <?php include "$root/web/footer.php"; ?>
</html>
