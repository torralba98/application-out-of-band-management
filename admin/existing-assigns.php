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

    <title>Panel Admin ~ Administrar Asignaciones existentes</title>
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
        <div><a id='volver' href='assignments' class='large green button'>Volver</a></div>
	<FONT SIZE=4><i><p><a>Aquí podrás administrar las <u>asignaciones existentes</u>.</a></p></i></font>
        <br>
        <FONT SIZE=2><p>[Grupo Usuarios] - [Grupo Dispositivos]</p></font>
                <?php

                  // Connection info. file
                  include '../web_config/configuration_properties.php';


                  // Connection variables
                  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  // Check connection
                  if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                  }

                  // Query sent to database
                  $result = mysqli_query($conn, "SELECT id, device_group_id_assigned FROM user_group WHERE device_group_id_assigned IS NOT NULL ORDER BY id ASC");

                  if (mysqli_num_rows($result)==0)
                    printf("<p style='margin-left: 2em; color:red'> <b>Ninguna asignación existente.</b></p>");

                  while ($row = mysqli_fetch_array($result)) {
                    printf(" <form action='' method='post'>
                              • <b>Grupo%s - POD%s</b> &nbsp; - &nbsp;
                              <input style='background-color:red;' name='nameAsign' value='%s-%s' hidden/>
                              <input type='submit' class='large red button' name='removeAsign' value='ELIMINAR     ' /> <br><br>
                              ", $row[0], $row[1], $row[0], $row[1]);
                    printf("</form>");
                  }

                  if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['removeAsign'])) {

                    $asignacion = explode("-", $_POST['nameAsign']);
                    $device_group = $asignacion[1];
                    $user_group = $asignacion[0];

                    echo "<div id='dialog'>";
                     echo "<div id='dialog-bg'>";
                          echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                           echo "<div id='dialog-description'>Recuerda que una vez confirmes se eliminará la asignación <b>Grupo$user_group</b> - <b>POD$device_group</b>.</div>";
                           echo "<div id='dialog-buttons'>";
                           echo "<form action='' method='post'>";
                           echo "<input style='background-color:red;' name='nameAsign2' value='$user_group-$device_group' hidden/>";
                           echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
                           echo "<a href='$url' class='large red button'>Mejor no lo hago.</a>";
                           echo "</form>";
                       echo "</div>";
                    echo "</div>";
                   echo "</div>";
                   echo "<div class='pageCover'></div>";
                   }

                  if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['confirm'])) {

                    $asignacion = explode("-", $_POST['nameAsign2']);
                    $device_group = $asignacion[1];
                    $user_group = $asignacion[0];

                    $delete = mysqli_query($conn, "UPDATE user_group SET device_group_id_assigned = NULL WHERE id ='$user_group'");
                    if (!$delete) {
                      die('Invalid query: ' . mysql_error());
                    } else {
                        echo "<div id='dialog'>";
                         echo "<div id='dialog-bg'>";
                              echo "<div id='dialog-title'>¡Listo!</div>";
                               echo "<div id='dialog-description'>Se ha eliminado exitosamente la asignación <b>Grupo$user_group</b> - <b>POD$device_group</b></div>";
                               echo "<div id='dialog-buttons'>";
                               echo "<a href='$url' class='large green button'>Aceptar</a>";
                         echo "</div>";
                        echo "</div>";
                       echo "</div>";
                       echo "<div class='pageCover'></div>";
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
