<?php
  header('Content-Type: text/html; charset=UTF-8');
  session_start();

  $root = realpath($_SERVER["DOCUMENT_ROOT"]);

  include "../web_config/configuration_properties.php";

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
?>

<!doctype html>
<html lang="es">

   <head>

     <title>Panel de Administración</title>
     <link rel="icon" type="image/png" href="/images/icon.png" />
     <?php include "$root/web/header.php"; ?>

     <!-- Required meta tags -->
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

     <!-- CSS -->
     <link rel="stylesheet" href="../admin/css/admin.css">
     <link rel="stylesheet" href="../css/bootstrap.min.css">

     <script>

        function updateView(user) {
             window.location = "/admin-pan?user=" + user;
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
        <div id="content1">
          <img src="/images/admin_pan.png" style="width:150px;height:150px; position: absolute; top: 0; right: 0; margin-right: 25px; margin-top: 18px;" />
        </div>
        <br><FONT SIZE=4> ¡Bienvenido/a al <b>Panel de Administración</b>! </FONT>
            <br><br><br>
              <FONT SIZE=3><i><p><a>Aquí podrás <b>administrar</b> información relativa a los diferentes <b>grupos de usuarios</b>, <b>grupos de dispositivos</b> y <b>logs</b>.</a></p></i></font>
              <br>
        </div>
      </div>

  </body>

 <?php include "$root/web/footer.php"; ?>
  
</html>
