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
<html lang="es">

	<head>

		<title>Panel Admin ~ Dispositivos</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- CSS -->
    <link rel="stylesheet" href="../admin/css/admin.css">
    <link rel="stylesheet" href="../css/alerts.css">
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

		<div id='d' class="container">
		  <div class='alert alert-success mt-4' role='alert'>
						<FONT SIZE=4><i><p><a>Estos son todos los <u>dispositivos existentes</u>.</a></p></i></font>
                <?php

                if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['eliminar'])) {

                  $dispositivo = $_POST['device'];

                  echo "<div id='dialog'>";
                   echo "<div id='dialog-bg'>";
                        echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                         echo "<div id='dialog-description'>Recuerda que una vez confirmes se eliminará el dispositivo <b>$dispositivo</b>.</div>";
                         echo "<div id='dialog-buttons'>";
                         echo "<form action='' method='post'>";
                         echo "<input style='background-color:red;' name='device' value='$dispositivo' hidden/>";
                         echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
                         echo "<a href='devices' class='large red button'>Mejor no lo hago.</a>";
                        echo "</form>";
                      echo "</div>";
                     echo "</div>";
                  echo "</div>";
                 echo "<div class='pageCover'></div>";

                }

                if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['confirm'])) {

                  $dispositivo = $_POST['device'];

                  $xml = simplexml_load_file("../web_config/devices_info.xml");

                  $toDelete = array();

                  foreach ($xml->device as $item) {

                      $nombreDevice = $item->name;

                      if ($nombreDevice == $dispositivo) {

                          $toDelete[] = $item;
                      }
                  }

                    foreach ($toDelete as $item) {

                      $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                      if (!$conn) {
                        die("Connection failed: " . mysqli_connect_error());
                      }

                      $result = mysqli_query($conn, "DELETE FROM device WHERE Id = '$item->idDb'");
                      if (!$result)
                        die('Invalid query: ' . mysql_error());

                      mysqli_close($conn);

                      $dom = dom_import_simplexml($item);
                      $dom->parentNode->removeChild($dom);
                    }

                    $xml->asXML("../web_config/devices_info.xml");

                    echo "<div id='dialog'>";
                     echo "<div id='dialog-bg'>";
                          echo "<div id='dialog-title'>¡Listo!</div>";
                           echo "<div id='dialog-description'>¡Se ha eliminado el dispositivo <b>$dispositivo</b> exitosamente!</div>";
                           echo "<div id='dialog-buttons'>";
                           echo "<a href='devices' class='large green button'>Aceptar</a>";
                     echo "</div>";
                    echo "</div>";
                   echo "</div>";
                   echo "<div class='pageCover'></div>";

                }

                $devices = simplexml_load_file("../web_config/devices_info.xml");
                if (count($devices) == 0) {
                  echo "<p style='margin-left: 2em; color:red'> <br><b>En estos momentos no hay <u>ningún</u> dispositivo existente.</b><br></p>";
                }
                foreach($devices as $device) {
                    echo "<form action='' method='post'>";
                    echo "<p style='margin-left: 2em; display:inline';> • <b>$device->name</b></p>";
                    echo "&nbsp;&nbsp;<a href='edit-device?device=$device->name'>¿Editar?</a>&nbsp;&nbsp;&nbsp;-";
                    echo "<input style='background-color:red;' name='device' value='$device->name' hidden/>";
                    echo "<input type='submit' name='eliminar' style='border:none;background:none;color:red' value='¿Eliminar?'>";
                    echo "</form><br>";
                }

                ?>
      </div>
		</div>
	</body>

  <nav class='menuNew container'>
    <ul>
      <li><a href="new-device">Crear Dispositivo</a>|</li>
      <li><a href="device-groups">Administrar Grupos de Disp.</a></li>
    </ul>
  </nav>

  <?php include "$root/web/footer.php"; ?>

</html>
