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

    <title>Panel Admin ~ Resetear Dispositivos</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>
    <link rel="stylesheet" href="../css/alerts.css">
    <link rel="stylesheet" href="../admin/css/admin.css">

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    
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
	<div class='alert alert-success mt-4' role='alert'>
	      <FONT SIZE=4><i><p><a>El uso de este botón hará que todos los <u>dispositivos</u> adquieran el estado de <u>accesibles</u>. <br><br>&nbsp;&nbsp; ~ Usar <b><u>solo</u></b> en casos de fallas eléctricas u otras circunstancias que impliquen un apagado imprevisto de los mismos.</a></p></i></font>
              <br>
              <form action='' method='post'>
                      <p style='margin-left: 2em'>
                        <input type='submit' class='cont large red button' name='reset' value='RESETEAR DISPOSITIVOS' onclick=''/>
                      </p>
            </form>

            <?php
              if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['reset'])) {
               echo "<div id='dialog'>";
	               echo "<div id='dialog-bg'>";
       	            echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                        echo "<div id='dialog-description'>Recuerda que una vez confirmes todos los dispositivos aparecerán como disponibles. Revisa primero que no haya ningún usuario conectado a ninguno.</div>";

                      echo "<div id='dialog-buttons'>";
                      echo "<form action='' method='post'>";
                      echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
                      echo "<a href='./reset-devices' class='large red button'>Mejor no lo hago.</a>";
                      echo "</form>";
		               echo "</div>";
	              echo "</div>";
              echo "</div>";
              echo "<div class='pageCover'></div>";
              }

              if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['confirm'])) {
                // Connection info. file
                include '../web_config/configuration_properties.php';

                // Connection variables
                $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                // Check connection
                if (!$conn) {
                  die("Connection failed: " . mysqli_connect_error());
                }

                // Query sent to database
                $result = mysqli_query($conn, "SELECT Id FROM device");

                while ($row = mysqli_fetch_array($result)) {
                  $add = mysqli_query($conn, "UPDATE device SET in_use = 'NO', server_status = 'OFF' WHERE Id = '$row[0]'");
                  if (!$add)
                    die('Invalid query: ' . mysql_error());
                  else {
                    $flag = true;
                  }
                }
                 if ($flag) {
                    echo "<div id='dialog'>";
                     echo "<div id='dialog-bg'>";
                          echo "<div id='dialog-title'>¡Listo!</div>";
                           echo "<div id='dialog-description'>¡Se han reseteado todos los dispositivos con éxito!</div>";
                           echo "<div id='dialog-buttons'>";
                           echo "<a href='admin-pan' class='large green button'>Volver al Menú</a>";
                     echo "</div>";
                    echo "</div>";
                   echo "</div>";
                   echo "<div class='pageCover'></div>";
                 }

              mysqli_close($conn);
            }

          ?>
        <br>
       <p>¿Prefieres resetear un dispositivo en concreto? <a href="/admin/reset-device" data-toggle="collapse" aria-expanded="false" aria-controls="collapse">Click <u>aquí</u>.</a></p>
     </div>
    </div>

</body>

  <?php include "$root/web/footer.php"; ?>

</html>
