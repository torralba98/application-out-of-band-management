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

	 <title>Panel Admin ~ Administrar Grupos Dispositivos</title>
   <link rel="icon" type="image/png" href="/images/icon.png" />
   <?php include "$root/web/header.php"; ?>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- CSS -->
		<link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/alerts.css">
    <link rel="stylesheet" href="../admin/css/admin.css">

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

    <script>

      function updateView(deviceGroup) {
           window.location = "device-groups?deviceGroup=" + deviceGroup;
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
          <div><a id='volver' href='devices' class='large green button'>Volver</a></div>
          <FONT SIZE=4><i><p><a>Aquí podrás administrar los <u>Grupos de Dispositivos</u>.</u></a></p></i></font>
              <br>
							<p><a><i>Editar Grupo: &nbsp;<i></a>
                <select id="usuario"; name="usuario"; style="width:200px"; onchange="updateView(this.options[this.selectedIndex].value);">
                  <option> Seleccione grupo... </option>

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
                    $result = mysqli_query($conn, "SELECT DISTINCT device_group_id FROM device WHERE device_group_id IS NOT NULL ORDER BY device_group_id ASC");

                    while ($row = mysqli_fetch_array($result)) {
                      printf("<option>POD%s</option>", $row[0]);
                    }
                  ?>

                </select>

                <?php

                  $url = $_SERVER['REQUEST_URI'];
                  $deviceGroup = explode("deviceGroup=POD", $url);

                  $devices = simplexml_load_file("../web_config/devices_info.xml");

                  if (isset($deviceGroup[1])) {
                    $deviceGroup = str_replace('%20'," ",$deviceGroup[1]);

                    echo "<br><br><FONT SIZE=4><p><b><a><i>Dispositivos asignados al grupo <b>POD$deviceGroup</b>. <i></a></b></p></FONT>";
                    echo "<FONT SIZE=2><i><p><a>ATENCIÓN. Si eliminas todos los dispositivos de un grupo, este desaparecerá automáticamente (y sus aginaciones a Grupos de Usuarios).</a></p></i></font><br>";

                    $result = mysqli_query($conn, "SELECT Id FROM device WHERE device_group_id = '$deviceGroup'");
                    if (mysqli_num_rows($result)==0)
                      header('Location: device-groups');
                    while ($row = mysqli_fetch_array($result)) {

                      foreach($devices as $device)
                          if ($device->idDb == $row[0]){
                                echo "<form action='' method='post'>";
                                        echo "<p style='margin-left: 2em'> <label> • <b>$device->name</b> &nbsp; - &nbsp; </label>";
                                        echo "<input style='background-color:red;' name='namedevice' value='$device->name' hidden/>";
                                        echo "<input type='submit' class='large red button' name='removedevice' value='ELIMINAR     ' />";
                                echo "</form>";
                        }
                    }

                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['removedevice'])) {

                      $devicename = $_POST['namedevice'];

                      echo "<div id='dialog'>";
       	               echo "<div id='dialog-bg'>";
              	            echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                             echo "<div id='dialog-description'>Recuerda que una vez confirmes se eliminará a <b>$devicename</b> del grupo <b>POD$deviceGroup</b>.</div>";
                             echo "<div id='dialog-buttons'>";
                             echo "<form action='' method='post'>";
                             echo "<input style='background-color:red;' name='nombredevice' value='$devicename' hidden/>";
                             echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
                             echo "<a href='$url' class='large red button'>Mejor no lo hago.</a>";
                             echo "</form>";
       		               echo "</div>";
       	              echo "</div>";
                     echo "</div>";
                     echo "<div class='pageCover'></div>";
                     }

                   if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['confirm'])) {

                      $device =  $_POST['nombredevice'];
                      $assign = false;
                      $delGroup = false;

                      $devices = simplexml_load_file("../web_config/devices_info.xml");

                      foreach($devices as $device1)
                          if ($device1->name == $device)
                              $id = $device1->idDb;

                      $result = mysqli_query($conn, "SELECT device_group_id FROM device WHERE id = '$id'");
                      while ($row = mysqli_fetch_array($result)) {
                          $result1 = mysqli_query($conn, "SELECT device_group_id, id FROM device WHERE device_group_id = '$row[0]'");
                              if (mysqli_num_rows($result1)==1) {
                                  $delete = mysqli_query($conn, "UPDATE device SET device_group_id = NULL WHERE Id = '$id'");
                                  
                                  $delGroup = true;
                                  while ($row1 = mysqli_fetch_array($result1)) {
                                    $assignation = mysqli_query($conn, "SELECT ug.id FROM user_group ug JOIN device_group dg ON ug.device_group_id_assigned=dg.id WHERE dg.id='$row[0]' AND device_group_id_assigned IS NOT NULL");
                                    if (mysqli_num_rows($assignation)!=0) {
                                      $assign = true;
                                      while ($row1 = mysqli_fetch_array($assignation)){
                                        $deviceAssign = $row1[0];
                                        $delete1 = mysqli_query($conn, "UPDATE user_group SET device_group_id_assigned = NULL WHERE id='$row1[0]'");
                                        if (!$delete1)
                                          die('Invalid query: ' . mysql_error());
                                      }
                                    }
                                    $delete2 = mysqli_query($conn, "DELETE FROM device_group WHERE Id='$row[0]'");
                                        if (!$delete2)
                                          die('Invalid query: ' . mysql_error());
                                  }
                              } else 
                                  $delete = mysqli_query($conn, "UPDATE device SET device_group_id = NULL WHERE Id = '$id'");
                     }

                      if (!$delete) {
                        die('Invalid query: ' . mysql_error());
                      } else {
                        echo "<div id='dialog'>";
                         echo "<div id='dialog-bg'>";
                              echo "<div id='dialog-title'>¡Listo!</div>";
                               echo "<div id='dialog-description'><b>$device</b> eliminado del grupo <b>POD$deviceGroup</b>.</div>";
                               if ($delGroup) {
                                 echo "<div id='dialog-description'>Al quedar sin miembros, se eliminó el grupo <b>POD$deviceGroup</b>";
                                 if ($assign)
                                  echo " y su asignación al grupo <b>$deviceAssign</b>";
                                 echo ".</div>";
                                 echo "<div id='dialog-buttons'>";
                                 echo "<a href='device-groups' class='large green button'>Aceptar</a>";
                               }
                               else {
                                 echo "<div id='dialog-buttons'>";
                                 echo "<a href='$url' class='large green button'>Aceptar</a>";
                               }
                           echo "</div>";
                        echo "</div>";
                       echo "</div>";
                       echo "<div class='pageCover'></div>";
                      }

                    }

                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['add'])) {
                      if(!empty($_POST['devices'])) {

                          $devicename  = $_POST['devices'];
                          echo "<div id='dialog'>";
                           echo "<div id='dialog-bg'>";
                                 echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                                 echo "<div id='dialog-description'>Recuerda que una vez confirmes se añadirá a ";
                                 $max = sizeof($devicename);
                                 if ($max == 1)
                                    echo "<b>$devicename[0]</b> ";
                                 else {
                                   echo "los devices ";
                                   $counter = 0;
                                   foreach($devicename as $selected) {
                                      $counter+=1;
                                      if ($counter == $max-1)
                                        echo "<b>$selected</b> y ";
                                      else
                                        echo "<b>$selected</b>, ";
                                    }
                                 }
                                 echo "al grupo <b>POD$deviceGroup</b>.</div>";
                                 echo "<div id='dialog-buttons'>";
                                 echo "<form action='' method='post'>";
                                 $devicesArray = implode("|",$devicename);
                                 echo "<input style='background-color:red;' name='nombredevices' value='$devicesArray' hidden/>";
                                 echo "<input type='submit' name='confirmar' class='large green button' value='¡Sí, quiero hacerlo!'>";
                                 echo "<a href='$url' class='large red button'>Mejor no lo hago.</a>";
                                 echo "</form>";
                             echo "</div>";
                          echo "</div>";
                         echo "</div>";
                         echo "<div class='pageCover'></div>";
                       } else {
                             echo "<div id='dialog'>";
                              echo "<div id='dialog-bg'>";
                                   echo "<div id='dialog-title'>¡Ups!</div>";
                                    echo "<div id='dialog-description'>Por favor, para poder añadir dispositivos al grupo <b>POD$deviceGroup</b> es necesario que, al menos, selecciones un dispositivo.</div>";
                                      echo "<div id='dialog-buttons'>";
                                      echo "<a href='$url' class='large green button'>Aceptar</a>";
                                 echo "</div>";
                               echo "</div>";
                              echo "</div>";
                              echo "<div class='pageCover'></div>";
                       }
                    }


                     if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['nombredevices'])) {

                        $devicesArray = explode("|", $_POST['nombredevices']);

                        foreach($devicesArray as $selected){

                          foreach($devices as $device1)
                              if ($device1->name == $selected)
                                  $id = $device1->idDb;

                          $add = mysqli_query($conn, "UPDATE device SET device_group_id = '$deviceGroup' WHERE Id = '$id'");
                          if (!$add) {
                            die('Invalid query: ' . mysql_error());
                          } else
                            header("Refresh:0");
                        }
                    }

                    if (mysqli_num_rows($result)==0)
                        echo "<p style='margin-left: 2em'> El $deviceGroup no tiene ningún dispositivo asignado.";

                    echo "<br><br><FONT SIZE=4><p><b><a><i>Aquí puedes asignar más dispositivos al grupo <b>POD$deviceGroup</b>. <i></a></b></p></FONT>";

                    $result = mysqli_query($conn, "SELECT Id FROM device
                                                   WHERE device_group_id IS NULL");

                    if (mysqli_num_rows($result)==0)
                       echo "<p style='margin-left: 2em; color:red'> Lo sentimos, todos los dispositivos ya pertenecen a algún grupo.";
                    else {
                        echo "<FONT SIZE=2><i><p><a>Puedes seleccionar varios a la vez con la tecla 'Ctrl'.</a></p></i></font>";
                        echo "<form action='' method='post'>
                                <select id='devices' name='devices[]' style='width:200px' size=6 onchange='' multiple='multiple'>";

                            while ($row = mysqli_fetch_array($result)) {
                              foreach($devices as $device)
                                  if ($device->idDb == $row[0])
                                      echo "<option>$device->name</option>";
                            }

                            echo "</select>";
                            echo "<br><br><input type='submit' name='add' value='Añadir Dispositivo'>
                                  </form>";

                    }
                    echo "<br>";
                  }
                  
                  mysqli_close($conn);
                ?>

              </p>
              <br>
		  </div>
    </div>

    <nav class='menuNew container'>
      <ul>
        <li><a href="new-device-group">Crear Grupo</a>|</li>
        <li><a href="device-group-summary">Ver Grupos Existentes</a></li>
      </ul>
    </nav>

	</body>

  <?php include "$root/web/footer.php"; ?>
  
</html>
