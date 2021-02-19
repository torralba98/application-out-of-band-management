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

   <title>Panel Admin ~ Administrar Grupos Usuarios</title>
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

   <script>

      function updateView(user_group) {
           window.location = "user-groups?user_group=" + user_group;
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

      <div id='all' class='alert alert-success mt-4' role='alert'>
        <div><a id='volver' href='registered-users' class='large green button'>Volver</a></div>
          <FONT SIZE=4><i><p><a>Aquí podrás administrar los <u>Grupos de Usuarios</u>.</u></a></p></i></font>
          <br>
	  <p><a><i>Editar Grupo:  &nbsp;<i></a>
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
                    $result = mysqli_query($conn, "SELECT DISTINCT user_group_id FROM user WHERE user_group_id IS NOT NULL ORDER BY user_group_id ASC");

                    while ($row = mysqli_fetch_array($result)) {
                      printf("<option>Grupo%s</option>", $row[0]);
                    }
                  ?>

            </select>

	    <?php

		  $url = $_SERVER['REQUEST_URI'];
		  $user_group = explode("user_group=Grupo", $url);

		  if (isset($user_group[1])) {
		    $user_group = str_replace('%20'," ",$user_group[1]);

		    echo "<br><br><FONT SIZE=4><p><b><a><i>Usuarios asignados al grupo <b>Grupo$user_group</b>. <i></a></b></p></FONT>";
		    echo "<FONT SIZE=2><i><p><a>ATENCIÓN. Si eliminas todos los usuarios de un grupo, este desaparecerá automáticamente (y sus aginaciones a Grupos de Dispositivos).</a></p></i></font><br>";

		    $result = mysqli_query($conn, "SELECT username FROM user WHERE user_group_id = '$user_group' ORDER BY username ASC");
		    while ($row = mysqli_fetch_array($result)) {
		      printf("<form action='' method='post'>
			      <p style='margin-left: 2em'> <label> • <b>%s</b> &nbsp; - &nbsp;</label>
			      <input style='background-color:red;' name='nameUser' value='%s' hidden/>
			      <input type='submit' class='large red button' name='removeUser' value='ELIMINAR     ' />

			      ", $row[0], $row[0]);
		      printf("</form>");
		    }

		    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['removeUser'])) {

		      echo "<script> document.querySelector('.pageCover').classList.remove('hidden'); </script>";


		      $userName = $_POST['nameUser'];


		      echo "<div id='dialog'>";
		       echo "<div id='dialog-bg'>";
			    echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
			     echo "<div id='dialog-description'>Recuerda que una vez confirmes se eliminará a <b>$userName</b> del grupo <b>Grupo$user_group</b>.</div>";
			     echo "<div id='dialog-buttons'>";
			     echo "<form action='' method='post'>";
			     echo "<input style='background-color:red;' name='nameUsuario' value='$userName' hidden/>";
			     echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
			     echo "<a href='$url' class='large red button'>Mejor no lo hago.</a>";
			     echo "</form>";
			       echo "</div>";
		      echo "</div>";
		     echo "</div>";
		     echo "<div class='pageCover'></div>";
		    }

		   if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['confirm'])) {

		      $user =  $_POST['nameUsuario'];
		      $assign = false;
		      $delGroup = false;

		      $result = mysqli_query($conn, "SELECT user_group_id FROM user WHERE username = '$user'");
		      while ($row = mysqli_fetch_array($result)) {
			  $result1 = mysqli_query($conn, "SELECT user_group_id, username FROM user WHERE user_group_id = '$row[0]'");
			      if (mysqli_num_rows($result1)==1) {
				  $delete = mysqli_query($conn, "UPDATE user SET user_group_id = NULL WHERE username = '$user'");

				  $delGroup = true;
				  while ($row1 = mysqli_fetch_array($result1)) {
				    $assignation = mysqli_query($conn, "SELECT id FROM user_group WHERE id='$row1[0]' AND device_group_id_assigned IS NOT NULL");
				    if (mysqli_num_rows($assignation)!=0) {
				      $assign = true;
				      while ($row1 = mysqli_fetch_array($assignation)){
					 $deviceAssign = $row1[0];
					 $delete1 = mysqli_query($conn, "UPDATE user_group SET device_group_id_assigned = NULL WHERE id='$row1[0]'");
					 if (!$delete1)
					   die('Invalid query: ' . mysql_error());
				      }
				    }
				    $delete2 = mysqli_query($conn, "DELETE FROM user_group WHERE Id='$row[0]'");
					if (!$delete2)
					  die('Invalid query: ' . mysql_error());
				  }
			      } else
				  $delete = mysqli_query($conn, "UPDATE user SET user_group_id = NULL WHERE username = '$user'");
		     }

		      if (!$delete) {
			die('Invalid query: ' . mysql_error());
		      } else {
			echo "<div id='dialog'>";
			 echo "<div id='dialog-bg'>";
			      echo "<div id='dialog-title'>¡Listo!</div>";
			       echo "<div id='dialog-description'><b>$user</b> eliminado del grupo <b>Grupo$user_group</b>.</div>";
			       if ($delGroup) {
				 echo "<div id='dialog-description'>Al quedar sin miembros, se eliminó el grupo <b>Grupo$user_group</b>";
				 if ($assign)
				  echo " y su asignación al grupo <b>$deviceAssign</b>";
				 echo ".</div>";
				 echo "<div id='dialog-buttons'>";
				 echo "<a href='user-groups' class='large green button'>Aceptar</a>";
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
				 echo "al grupo <b>Grupo$user_group</b>.</div>";
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
			 echo "<div class=' pageCover'></div>";
		       } else {
			     echo "<div id='dialog'>";
			      echo "<div id='dialog-bg'>";
				   echo "<div id='dialog-title'>¡Ups!</div>";
				    echo "<div id='dialog-description'>Por favor, para poder añadir usuarios al grupo <b>Grupo$user_group</b> es necesario que, al menos, selecciones un usuario.</div>";
				      echo "<div id='dialog-buttons'>";
				      echo "<a href='$url' class='large green button'>Aceptar</a>";
				 echo "</div>";
			       echo "</div>";
			      echo "</div>";
			      echo "<div class=' pageCover'></div>";
		       }
		    }


		     if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['nameUsuarios'])) {

			$usuariosArray = explode("|", $_POST['nameUsuarios']);

			foreach($usuariosArray as $selected){

			  $add = mysqli_query($conn, "UPDATE user SET user_group_id = '$user_group' WHERE username = '$selected'");
			  if (!$add) {
			    die('Invalid query: ' . mysql_error());
			  } else
			    header("Refresh:0");
			}
		    }

		    if (mysqli_num_rows($result)==0)
			echo "<p style='margin-left: 2em'> El Grupo$user_group no tiene ningún usuario asignado.";

		    echo "<br><br><FONT SIZE=4><p><b><a><i>Aquí puedes asignar más usuarios al grupo <b>Grupo$user_group</b>. <i></a></b></p></FONT>";

		    $result = mysqli_query($conn, "SELECT username FROM user
						   WHERE user_group_id IS NULL AND username != 'admin' ORDER BY username DESC");

		    if (mysqli_num_rows($result)==0)
		       echo "<p style='margin-left: 2em; color:red'> Lo sentimos, todos los usuarios ya pertenecen a algún grupo.";
		    else {
			echo "<FONT SIZE=2><i><p><a>Puedes seleccionar varios a la vez con la tecla 'Ctrl'.</a></p></i></font>";
			echo "<form action='' method='post'>
				<select id='devices' name='users[]' style='width:200px' size=6 onchange='' multiple='multiple'>";


			    while ($row = mysqli_fetch_array($result)) {
			      printf("<option>%s</option>", $row[0]);
			    }

			    echo "</select>";
			    echo "<br><br><input type='submit' name='add' value='Añadir Usuario'>
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
        <li><a href="new-user-group">Crear Grupo</a>|</li>
        <li><a href="user-group-summary">Ver Grupos Existentes</a></li>
      </ul>
    </nav>

  </body>

<?php include "$root/web/footer.php"; ?>
  
</html>
