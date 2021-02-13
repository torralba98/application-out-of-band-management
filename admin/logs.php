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
		<title>Panel Admin ~ Log</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../admin/css/admin.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    </head>

<script>

    window.addEventListener("keypress", function(event){
        if (event.keyCode == 13){
            event.preventDefault();
        }
    }, false);

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
						<FONT SIZE=4><i><p><a>Aquí puedes ver los <u>logs</u> disponibles de los Dispositivos</u>.</a></p></i></font>
              <br>
              <?php
              $flag = false;
              $flagIntrussion = false;


                $dir = opendir("../server_node/logs/");
                $i = 0;
                while ($elemento = readdir($dir)){
                    if( $elemento != "." && $elemento != ".."){
                      $flag = true;
                      $elemento = str_replace('.txt',"",$elemento);
                      if ($elemento == "AttempIntrusionConsole")
                        $flagIntrussion = true;
                      else {
                        echo "<form action='' method='post'>
                              <p style='margin-left: 2em'> • <input type='submit' name='logdevice' value='$elemento' /></p>";
                        $i++;
                      }
                    }
                }

                echo "</form>";

                if ($i == 0)
                  echo "<p style='margin-left: 2em; color:red'><b>No existe ningún log de dispositivo.</b><br></p>";

                if ($flagIntrussion) {
                  echo "<br>";
                  echo "<form method='post'>";
                  echo "<label> ¿Quieres ver el log de los usuarios que intentaron <u>entrar</u> en dispositivos de manera <u>fraudulenta</u>? </label>";
                  echo "<input style='background-color:red;' name='logIntrussion' value='' hidden/>";
                  echo "&nbsp;&nbsp;<input id='searchBt' type='submit' value='Click aquí'></p>";
                  echo "</form>";
                }

                if (!$flag)
                  echo "<FONT color=red SIZE=4><i><p><a><b>¡UPS! Parece que por el momento no existe ningún log...</b></a></p></i></font>";

                if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['logdevice'])) {
                    $device = $_POST['logdevice'];
                    echo "<br>";
                  	echo "<br><FONT SIZE=4><b>Log del Dispositivo <u>$device</u></b></font><br><br>";
                    echo "<form method='post'>";
                    echo  "<input style='background-color:red;' name='namedevice' value='$device' hidden/>";
                    echo "<p> Búsqueda por palabra clave: &nbsp;<input class='search' type='search' name='busqueda' placeholder='Usuario, comando...'>&nbsp;&nbsp;";
                    echo "<input id='searchBtn' type='submit' value='Buscar'></p>";
                    echo "</form>";
                    echo "<br>";

                    $file = fopen("../server_node/logs/" . $_POST['logdevice'] . ".txt", "r");

                    while(!feof($file)) {
                      $traer = fgets($file);
                      echo nl2br($traer);
                    }

                    fclose($file);
                 }


                 if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['busqueda'])) {
                   $busqueda = $_POST['busqueda'];
                   $logdevice = $_POST['namedevice'];
                   $file = fopen("../server_node/logs/" . $logdevice . ".txt", "r");

                   echo "<br>";
                   if ($logdevice == "AttempIntrusionConsole")
                    echo "<br><FONT SIZE=4><b>Log de usuarios que intentaron entrar en <u>dispositivos fraudulentamente</u>.</b></font><br><br>";
                   else
                    echo "<br><FONT SIZE=4><b>Log del Dispositivo <u>$logdevice</u></b></font><br><br>";
                   echo "<form method='post'>";
                   echo  "<input style='background-color:red;' name='namedevice' value='$logdevice' hidden/>";
                   echo "<p> Búsqueda por palabra clave: &nbsp;<input class='search' type='search' name='busqueda' placeholder='Usuario, comando...'>&nbsp;&nbsp;";
                   echo "<input id='searchBtn' type='submit' value='Buscar'></p>";
                   echo "</form>";
                   echo "<br>";

                   if ($busqueda)
                     while(!feof($file)) {
                       $traer = fgets($file);

                       if (strpos($traer, $busqueda) !== FALSE)
                            echo nl2br($traer);
                      }
                   else
                      while(!feof($file)) {
                        $traer = fgets($file);
                        echo nl2br($traer);
                      }

                   fclose($file);
                 }

                 if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['logIntrussion'])) {

                     echo "<br><br><FONT SIZE=4><b>Log de usuarios que intentaron entrar en <u>dispositivos fraudulentamente</u>.</b></font><br><br>";
                     echo "<form method='post'>";
                     echo  "<input style='background-color:red;' name='namedevice' value='AttempIntrusionConsole' hidden/>";
                     echo "<p> Búsqueda por palabra clave: &nbsp;<input class='search' type='search' name='busqueda' placeholder='Usuario, comando...'>&nbsp;&nbsp;";
                     echo "<input id='searchBtn' type='submit' value='Buscar'></p>";
                     echo "</form>";
                     echo "<br>";

                     $file = fopen("../server_node/logs/AttempIntrusionConsole.txt", "r");

                     while(!feof($file)) {
                       $traer = fgets($file);
                       echo nl2br($traer);
                     }

                     fclose($file);
                 }

            ?>

		</div>
    <br>
    <br>
	</body>
  <?php include "$root/web/footer.php"; ?>
</html>
