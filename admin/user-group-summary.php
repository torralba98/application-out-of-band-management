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
		<title>Panel Admin ~ Grupos de Usuarios Existentes</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
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

		<div class="container">

    <script>

      function updateView(user) {
           window.location = "/admin-pan?user=" + user;
      }

    </script>

		<div class='alert alert-success mt-4' role='alert'>
          <div><a id='volver' href='user-groups' class='large green button'>Volver</a></div>
						<FONT SIZE=4><i><p><a>Aquí podrás ver un <u>resumen</u> de la distribución en <u>grupos</u> de todos los usuarios registrados.</a></p></i></font>
                <?php
                  // Connection info. file
                  include '../configDevices/connectionSetup.php';

                  // Connection variables
                  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  // Check connection
                  if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                  }

                    $result = mysqli_query($conn, "SELECT DISTINCT user_group_id FROM user WHERE user_group_id IS NOT NULL ORDER BY user_group_id ASC");
                    while ($row = mysqli_fetch_array($result)) {
                      printf("<br><p style='margin-left: 2em'> Usuarios que pertenecen al grupo  <b>Grupo%s</b>.</p>", $row[0]);
                      $result1 = mysqli_query($conn, "SELECT username FROM user WHERE user_group_id = '$row[0]' AND username != 'admin' ORDER BY username ASC");
                      while ($row1 = mysqli_fetch_array($result1)) {
                        printf("<p style='margin-left: 4em'> • <i>%s</i></p>", $row1[0]);
                      }
                    }

                  // Query sent to database
                  $result = mysqli_query($conn, "SELECT username FROM user WHERE user_group_id IS NULL AND username != 'admin' ORDER BY username ASC");

                  if (mysqli_num_rows($result)!=0) {
                    printf("<br><b>Usuarios sin grupo asignado.</b> <br><br>");
                    while ($row = mysqli_fetch_array($result)) {
                      printf("<p style='margin-left: 2em'> • %s</p>", $row[0]);
                    }
                }
                ?>

                </select>

                <?php


                mysqli_close($conn);
                ?>

              </p>

		</div>
	</body>
  <?php include "$root/web/footer.php"; ?>
</html>
