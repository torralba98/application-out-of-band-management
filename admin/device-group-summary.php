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

		<title>Panel Admin ~ Grupos de Dispositivos Existentes</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- CSS -->
    <link rel="stylesheet" href="../admin/css/admin.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/alerts.css">

    <script>

      function updateView(user) {
           window.location = "/admin-pan?user=" + user;
      }

    </script>
	
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

		  <div class='alert alert-success mt-4' role='alert'>
          <div><a id='volver' href='device-groups' class='large green button'>Volver</a></div>
						<FONT SIZE=4><i><p><a>Aquí podrás ver un <u>resumen</u> de la distribución en <u>grupos</u> de todos los dispositivos existentes.</a></p></i></font>
               
                <?php

                  // Connection info. file
                  include '../web_config/configuration_properties.php';

                  // Connection variables
                  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  // Check connection
                  if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                  }

                    $devices = simplexml_load_file("../web_config/devices_info.xml");

                    $result = mysqli_query($conn, "SELECT DISTINCT device_group_id FROM device WHERE device_group_id IS NOT NULL ORDER BY device_group_id ASC");
                    while ($row = mysqli_fetch_array($result)) {
                      printf("<br><p style='margin-left: 2em'>Dispositivos que pertenecen al grupo  <b>POD%s</b>.</p>", $row[0]);

                      $result1 = mysqli_query($conn, "SELECT Id FROM device WHERE device_group_id = '$row[0]'");
                      while ($row1 = mysqli_fetch_array($result1)) {

                        foreach($devices as $device)
                            if ($device->idDb == $row1[0])
                              echo "<p style='margin-left: 4em'> • <i>$device->name</i></p>";
                      }
                    }

                  // Query sent to database
                  $result = mysqli_query($conn, "SELECT Id FROM device WHERE device_group_id IS NULL");

                  if (mysqli_num_rows($result)!=0) {
                    printf("<br><b>Dispositivos sin grupo asignado.</b> <br><br>");
                    while ($row = mysqli_fetch_array($result)) {
                      foreach($devices as $device)
                          if ($device->idDb == $row[0])
                            echo "<p style='margin-left: 4em'> • <i>$device->name</i></p>";
                    }
                }
                ?>

                </select>

                <?php

                  mysqli_close($conn);

                ?>

              </p>
      </div>
		</div>
	</body>

  <?php include "$root/web/footer.php"; ?>
  
</html>
