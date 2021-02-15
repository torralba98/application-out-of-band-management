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

		<title>Panel Admin ~ Administrar Asignaciones Usuarios / Dispositivos</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
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

      function updateView(user_group) {
           window.location = "assignments?user_group=" + user_group;
      }

      function createAsign() {
        document.getElementById('create').removeAttribute("hidden");
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
						<FONT SIZE=4><i><p><a>Aquí podrás crear nuevas <u>asignaciones entre Grupos de Usuarios y Grupos de Dispositivos.</u></a></p></i></font>
              <br>

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
                  $result = mysqli_query($conn, "SELECT Id FROM user_group WHERE device_group_id_assigned IS NULL
                                                 ORDER BY id ASC");

                  echo "<form action='' method='post'>";
                  if (mysqli_num_rows($result)==0)
                     echo "<label id='label'><b> <p style='color:red'>Todos los <u>Grupos de Usuarios</u> ya tienen asignado un <u>Grupo de Dispositivos</u>.</p></b></label>";
                  else {
                   echo "<label id='label'>Seleccionar <b>Grupo de Usuarios: </b> </label> ";
                   echo "<select id='device'; name='device'; style='width:200px'; onchange='updateView(this.options[this.selectedIndex].value);'>";
                   echo "<option> Seleccione grupo... </option>";

                    while ($row = mysqli_fetch_array($result)) {
                      printf("<option>Grupo%s</option>", $row[0]);
                    }
                  }
                ?>

              </select>
              </form>

                <?php

                  $url = $_SERVER['REQUEST_URI'];
                  $user_group = explode("user_group=", $url);

                  if (isset($user_group[1])) {

                    echo "<script> document.getElementById('device').style.display = 'none'; </script>";
                    echo "<script> document.getElementById('label').style.display = 'none'; </script>";
                    $user_group = str_replace('%20'," ",$user_group[1]);

                    // Query sent to database
                    $result = mysqli_query($conn, "SELECT d.id FROM device_group d LEFT JOIN user_group u ON d.id=u.device_group_id_assigned");

                   echo "<form action='' method='post'>";
                   if (mysqli_num_rows($result)==0)
                      echo "<label id='label'><b> <p style='color:red'>Todos los <u>Grupos de Dispositivos</u> ya están asignados a un <u>Grupo de Usuarios</u>.</p></b></label>";
                   else {
                     echo "<label id='label'>Seleccionar <b>Grupo de Dispositivos</b> a asignar al grupo <b>$user_group</b>: </label>";

                  ?>
                    <select id="device"; name="device"; style="width:200px"; onchange="createAsign();";>
                    <option> Seleccione grupo... </option>

                    <?php

                      while ($row = mysqli_fetch_array($result)) {
                        printf("<option>POD%s</option>", $row[0]);
                      }

                      echo "</select>";

                    echo "&nbsp;&nbsp;<input id='create' style='background-color:green;' type='submit' name='addAssign' value='Crear Asignación' hidden>
                          </form>";

                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['addAssign'])) {

                      if(!($_POST['device'] == 'Seleccione grupo...')) {
                            $device = $_POST['device'];
                            echo "<div id='dialog'>";
             	               echo "<div id='dialog-bg'>";
                    	            echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                                   echo "<div id='dialog-description'>Recuerda, una vez confirmes se asignará el grupo de dispositivos <b>$device</b> al grupo de usuarios <b>$user_group</b>.</div>";
                                   echo "<div id='dialog-buttons'>";
                                   echo "<form action='' method='post'>";
                                   echo "<input style='background-color:red;' name='nameAsign' value='$user_group-$device' hidden/>";
                                   echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
                                   echo "<a href='$url' class='large red button'>Mejor no lo hago.</a>";
                                   echo "</form>";
             		               echo "</div>";
             	              echo "</div>";
                           echo "</div>";
                           echo "<div class='pageCover'></div>";
                     }
                   }

                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['nameAsign'])) {
                        $asignacion = explode("-", $_POST['nameAsign']);
                        $deviceGroup = $asignacion[1];
                        $user_group = $asignacion[0];

                        $deviceGroup = explode("POD", $deviceGroup);
                        $user_group = explode("Grupo", $user_group);

                          $add = mysqli_query($conn, "UPDATE user_group SET device_group_id_assigned = '$deviceGroup[1]' WHERE id = '$user_group[1]'");
                        if (!$add) {
                          die('Invalid query: ' . mysql_error());
                        } else {
                              echo "<div id='dialog'>";
                               echo "<div id='dialog-bg'>";
                                    echo "<div id='dialog-title'>¡Listo!</div>";
                                     echo "<div id='dialog-description'>Se ha añadido exitosamente la asignación <b>Grupo$user_group[1]</b> - <b>POD$deviceGroup[1]</b></div>";
                                     echo "<div id='dialog-buttons'>";
                                     echo "<a href='assignments' class='large green button'>Aceptar</a>";
                               echo "</div>";
                              echo "</div>";
                             echo "</div>";
                             echo "<div class='pageCover'></div>";
                          }
                      }
                    }
                  }

                  mysqli_close($conn);

                ?>

              </p>
            <br>
      </div>
		</div>

    <nav class='menuNew container'>
      <ul>
        <li><a href="existing-assigns">Administrar Asignaciones Existentes</a></li>
      </ul>
    </nav>

	</body>

  <?php include "$root/web/footer.php"; ?>
  
</html>
