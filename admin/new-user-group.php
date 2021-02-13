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
		<title>Panel Admin ~ Crear Grupo de Usuarios</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>
    <link rel="stylesheet" href="../css/alerts.css">
    <link rel="stylesheet" href="../admin/css/admin.css">

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
			<link rel="stylesheet" href="../css/bootstrap.min.css">
      </head>

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
          <div><a id='volver' href='user-groups' class='large green button'>Volver</a></div>
						<FONT SIZE=4><i><p><a>Aquí podrás crear un nuevo <u>Grupo de Usuarios.</u></a></p></i></font>
              <br>
              <?php

                // Connection info. file
                include '../configDevices/connectionSetup.php';

                // Connection variables
                $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                // Check connection
                if (!$conn) {
                  die("Connection failed: " . mysqli_connect_error());
                }

                $result = mysqli_query($conn, "SELECT Id FROM user_group ORDER BY Id DESC LIMIT 1");

               ?>
              <form action="">
                <p style='margin-left: 2em;'> <label id='label' for="fname">• Se creará el grupo: &nbsp;</label>

                <?php
                  if (mysqli_num_rows($result)==0) {
                    $groupNumber = 1;
                    echo "<label id='name' name='newGroup'> <b>Grupo$groupNumber</b></label>";
                    echo "<input id='name' type='text' id='newGroup' name='newGroup' size='23' value='Grupo$groupNumber' hidden>";
                  }
                  while ($row = mysqli_fetch_array($result)) {
                    $groupNumber = $row[0] + 1;
                    echo "<label id='name' name='newGroup'> <b>Grupo$groupNumber</b></label>";
                    echo "<input id='name' type='text' id='newGroup' name='newGroup' size='23' value='Grupo$groupNumber' hidden>";
                  }
                ?>

                <input id='crear' style="margin-left: 10px" type="submit" value="Crear Grupo">
                </p>
              </form>

              <?php

                  $url = $_SERVER['REQUEST_URI'];
                  $newGroup = explode("newGroup=", $url);

                  if (isset($newGroup[1])) {

                    echo "<script> document.getElementById('name').style.display = 'none'; </script>";
                    echo "<script> document.getElementById('label').style.display = 'none'; </script>";
                    echo "<script> document.getElementById('crear').style.display = 'none'; </script>";
                    $newGroup = explode("Grupo",$newGroup[1]);
                    $newGroup = $newGroup[1];


                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['submit'])) {
                      if(!empty($_POST['users'])) {

                          $userName  = $_POST['users'];
                          echo "<div id='dialog'>";
                           echo "<div id='dialog-bg'>";
                                 echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                                 echo "<div id='dialog-description'>Recuerda que una vez confirmes se añadirá a ";
                                 $max = sizeof($userName);
                                 if ($max == 1)
                                    echo "<b>$userName[0]</b> ";
                                 else {
                                   echo "los usuarios ";
                                   $counter = 0;
                                   foreach($userName as $selected) {
                                      $counter+=1;
                                      if ($counter == $max-1)
                                        echo "<b>$selected</b> y ";
                                      else
                                        echo "<b>$selected</b>, ";
                                    }
                                 }
                                 echo "al grupo <b>Grupo$newGroup</b> y se creará este.</div>";
                                 echo "<div id='dialog-buttons'>";
                                 echo "<form action='' method='post'>";
                                 $usuariosArray = implode("|",$userName);
                                 echo "<input style='background-color:red;' name='nameUsuarios' value='$usuariosArray' hidden/>";
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
                               echo "<div id='dialog-description'>Por favor, para poder añadir usuarios al grupo <b>Grupo$newGroup</b> es necesario que, al menos, selecciones un usuario.</div>";
                                 echo "<div id='dialog-buttons'>";
                                 echo "<a href='$url' class='large green button'>Aceptar</a>";
                            echo "</div>";
                          echo "</div>";
                         echo "</div>";
                         echo "<div class='pageCover'></div>";
                      }
                    }

                    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['nameUsuarios'])) {

                         $usuariosArray = explode("|", $_POST['nameUsuarios']);
                         
                         $add1 = mysqli_query($conn, "INSERT INTO user_group(Id,group_name) VALUES ('$newGroup','Grupo$newGroup')");
                           if (!$add1) 
                             die('Invalid query: ' . mysql_error());

                         foreach($usuariosArray as $selected){

                           $add = mysqli_query($conn, "UPDATE user SET user_group_id = '$newGroup' WHERE username = '$selected'");
                           if (!$add) 
                             die('Invalid query: ' . mysql_error());         
                            
                         }
                          $link = str_replace(" ", "%20", $newGroup);
                              echo "<div id='dialog'>";
                               echo "<div id='dialog-bg'>";
                                    echo "<div id='dialog-title'>¡Listo!</div>";
                                     echo "<div id='dialog-description'>¡Felicidades! ¡El grupo <b>Grupo$newGroup</b> ha sido creado con éxito!</div>";
                                       echo "<div id='dialog-buttons'>";
                                       echo "<a href='user-groups?user_group=$link' class='large green button'>Ver el Grupo</a>";
                                  echo "</div>";
                                echo "</div>";
                               echo "</div>";
                               echo "<div class='pageCover'></div>";
                               exit;
                     }

                    $result = mysqli_query($conn, "SELECT user_group_id FROM user WHERE user_group_id = '$newGroup'");
                    if (mysqli_num_rows($result)!=0) {
                      echo "<div id='dialog'>";
                       echo "<div id='dialog-bg'>";
                            echo "<div id='dialog-title'>¡Ups!</div>";
                             echo "<div id='dialog-description'>Lo sentimos, ya existe un grupo llamado <b>Grupo$newGroup</b>, prueba con otro nombre.</div>";
                               echo "<div id='dialog-buttons'>";
                               echo "<a href='new-user-group' class='large green button'>Aceptar</a>";
                          echo "</div>";
                        echo "</div>";
                       echo "</div>";
                       echo "<div class='pageCover'></div>";
                       exit;
                    }else{

                      if (!isset($newGroup) || $newGroup == " ") {
                        echo "<div id='dialog'>";
                         echo "<div id='dialog-bg'>";
                              echo "<div id='dialog-title'>¡Ups!</div>";
                               echo "<div id='dialog-description'>Por favor, para poder crear un nuevo grupo de usuarios necesitamos que especifiques un nombre para este.</div>";
                                 echo "<div id='dialog-buttons'>";
                                 echo "<a href='new-user-group' class='large green button'>Aceptar</a>";
                            echo "</div>";
                          echo "</div>";
                         echo "</div>";
                         echo "<div class='pageCover'></div>";
                         exit;
                      }

                      echo "<p><b><a><i>Asignar usuarios al nuevo grupo <b>Grupo$newGroup</b>. <i></a></b></p>";

                      $result = mysqli_query($conn, "SELECT username FROM user
                                                     WHERE user_group_id IS NULL AND username != 'admin'");

                      if (mysqli_num_rows($result)==0)
                         echo "<p style='margin-left: 2em; color:red'> Lo sentimos, todos los usuarios ya pertenecen a algún grupo.</p>";
                      else {

                        echo "<FONT SIZE=2><i><p><a>Puedes seleccionar varios a la vez con la tecla 'Ctrl'.</a></p></i></font>";
                        echo "<form action='' method='post'>
                                <select id='devices' name='users[]' style='width:200px' size=6 onchange='' multiple='multiple'>";

                            while ($row = mysqli_fetch_array($result)) {
                              printf("<option>%s</option>", $row[0]);
                            }

                            echo "</select>";
                            echo "<br><br><input style='background-color:green;' type='submit' name='submit' value='Crear Grupo' Z>
                                  </form>";
                      }
                    }
                  }
                mysqli_close($conn);
                ?>

              </p>

		</div>
	</body>
  <?php include "$root/web/footer.php"; ?>
</html>