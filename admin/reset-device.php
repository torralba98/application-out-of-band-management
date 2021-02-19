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

   <title>Panel Admin ~ Resetear Dispositivo</title>
   <link rel="icon" type="image/png" href="/images/icon.png" />
   <?php include "$root/web/header.php"; ?>
   <link rel="stylesheet" href="../css/alerts.css">
   <link rel="stylesheet" href="../admin/css/admin.css">

   <!-- Required meta tags -->
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <!--CSS -->
   <link rel="stylesheet" href="../css/bootstrap.min.css">

   <script>

      function isSelected(device){
        window.location = "reset-device?device=" + device;
      }

      function enableButton() {
            document.getElementById('resetear').removeAttribute("hidden");
            document.getElementById('hidden2').removeAttribute("hidden");
      }

      function disableButton() {
        document.getElementById("resetear").disabled = true;
      }

   </script>

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

      <div id='all' class='alert alert-success mt-4' role='alert'>
          <div><a id='volver' href='reset-devices' class='large green button'>Volver</a></div>
              <FONT SIZE=4><i><p id='title'><a>Aquí podrás resetear un <u>dispositivo en concreto</u>.</u></a></p></i></font>
              <br>
		<p><a id="deviceY"><i>Seleccionar Dispositivo:  <i></a>
                <select id="deviceX"; name="deviceX"; style="width:200px"; onchange="isSelected(this.options[this.selectedIndex].value);">
                  <option> Seleccione dispositivo... </option>

                  <?php

                    $devices = simplexml_load_file("../web_config/devices_info.xml");

                    foreach($devices as $device)
                          echo "<option>$device->name</option>";

                  ?>

                  </select>

                  <?php

                    $dispositivo = "";

                    $url = $_SERVER['REQUEST_URI'];
                    $dispositivo = explode("device=", $url);

                    $dispositivo = str_replace('%20',' ',$dispositivo);

                    if (isset($dispositivo[1])) {

                      echo "<form action='' method='post'> <label id='label'><FONT SIZE=4><p id='hidden2' hidden > Estás a punto de resetear el dispositivo <b>$dispositivo[1]</b></FONT></label>";

                      echo "&nbsp;&nbsp; - &nbsp;&nbsp;<input id='resetear' class='large red button' type='submit' name='reset' value='Resetear Dispositivo    ' hidden></form></p>";

                      echo "<script> enableButton(); </script>";
                      echo "<script> document.getElementById('deviceX').style.display = 'none'; </script>";
                      echo "<script> document.getElementById('deviceY').style.display = 'none'; </script>";
                      echo "<script> document.getElementById('title').style.display = 'none'; </script>";
                  }
                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['reset'])) {

                      echo "<script> disableButton(); </script>";

                      echo "<div id='dialog'>";
       	               echo "<div id='dialog-bg'>";
              	            echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                             echo "<div id='dialog-description'>Recuerda que una vez confirmes se resetará el dispositivo <b>$dispositivo[1]</b>.</div>";
                             echo "<div id='dialog-buttons'>";
                             echo "<form action='' method='post'>";
                             echo "<input style='background-color:red;' name='namedeviceX' value='$dispositivo[1]' hidden/>";
                             echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
                             echo "<a href='reset-device' class='large red button'>Mejor no lo hago.</a>";
                             echo "</form>";
       		                  echo "</div>";
       	                 echo "</div>";
                      echo "</div>";
                      echo "<div class='pageCover'></div>";
                    }

                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['namedeviceX'])) {

                      echo "<script> disableButton(); </script>";

                      $deviceToReset = $_POST['namedeviceX'];

                      $devices = simplexml_load_file("../web_config/devices_info.xml");

                      foreach($devices as $device)
                          if ($device->name == $deviceToReset)
                            $id = $device->idDb;

                      $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                      if (!$conn) {
                        die("Connection failed: " . mysqli_connect_error());
                      }

                      $reset = mysqli_query($conn, "UPDATE device SET in_use = 'NO', server_status = 'OFF' WHERE Id = '$id'");
                      if (!$reset)
                        die('Invalid query: ' . mysql_error());
                        else {
                          $flag = true;
                        }
                       if ($flag) {
                          echo "<div id='dialog'>";
                           echo "<div id='dialog-bg'>";
                                echo "<div id='dialog-title'>¡Listo!</div>";
                                 echo "<div id='dialog-description'>¡Se ha reseteado el dispositivo <b>$deviceToReset</b> exitosamente!</div>";
                                 echo "<div id='dialog-buttons'>";
                                 echo "<a href='admin-pan' class='large green button'>Volver al Menú</a>";
                           echo "</div>";
                          echo "</div>";
                         echo "</div>";
                         echo "<div class='pageCover'></div>";
                       }
                    }

                  ?>

              </p>
            <br>
	</div>
    </div>

  </body>

<?php include "$root/web/footer.php"; ?>
  
</html>
