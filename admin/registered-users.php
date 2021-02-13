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
		<title>Panel Admin ~ Usuarios Registrados</title>
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

<script>

  function handleClick(user,cb) {
    window.location.href = "registered-users?user=" + user + "&admin=" + cb.checked;
}

</script>

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
						<FONT SIZE=4><i><p><a>Estos son todos los <u>usuarios registrados</u>.</a></p></i></font>
                <?php
                  // Connection info. file
                  include '../configDevices/connectionSetup.php';

                  // Connection variables
                  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  // Check connection
                  if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                  }

                  // Query sent to database
                  $result = mysqli_query($conn, "SELECT username FROM user ORDER BY username ASC");

                  while ($row = mysqli_fetch_array($result)) {
                    printf("<p style='margin-left: 2em; display:inline';> • <b>%s</b>", $row[0]);
                    $result2 = mysqli_query($conn, "SELECT is_admin FROM user WHERE username = '$row[0]'");
                    while ($row2 = mysqli_fetch_array($result2)) {
                      if ($row2[0])
                        echo "&nbsp;&nbsp;<label><input type='checkbox' id='cbox1' value='checkbox' onclick='handleClick(\"$row[0]\",this);' checked> ¿Administrador? </label><br></p>";
                      else
                        echo "&nbsp;&nbsp;<label><input type='checkbox' id='cbox1' value='checkbox' onclick='handleClick(\"$row[0]\",this);'> ¿Administrador? </label><br></p>";
                    }
                  }
                ?>

                </select>

                <?php

                if (isset($_GET['user']) && isset($_GET['admin'])) {

                  if ($_GET['user'] == 'admin') {
                    echo "<div id='dialog'>";
                     echo "<div id='dialog-bg'>";
                          echo "<div id='dialog-title'>¡Ups!</div>";
                           echo "<div id='dialog-description'>Lo sentimos, no puedes quitar el rol de <b>Administrador</b> al usuario <b>admin</b>.</div>";
                             echo "<div id='dialog-buttons'>";
                             echo "<a href='registered-users' class='large green button'>Aceptar</a>";
                        echo "</div>";
                      echo "</div>";
                     echo "</div>";
                     echo "<div class=' pageCover'></div>";
                  }
                  else {
                      if ($_GET['admin'] == "true")
                        $is_admin = 1;
                      else
                        $is_admin = 0;

                      $username = $_GET['user'];

                      $add = mysqli_query($conn, "UPDATE user SET is_admin = '$is_admin' WHERE username = '$username'");
                      if (!$add)
                        die('Invalid query: ' . mysql_error());
                      else
                        header('Location: registered-users');
                  }

                }

                mysqli_close($conn);
                ?>

              </p>

		</div>
	</body>

  <nav class='menuNew container'><ul>
  <li><a href="user-groups">Administrar Grupos de Users</a></li>
  </ul></nav>

  <?php include "$root/web/footer.php"; ?>
</html>
