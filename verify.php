<?php
  $root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>
 
<!doctype html>
<html lang="es">
  
  <head>
      
  	<title>Verificar Cuenta</title>
  	<link rel="icon" type="image/png" href="/images/icon.png" />
      
  	<?php 
  	  $username = ""; 
      	  include "$root/web/header.php"; 
  	?>
  	 
  	<!-- Required meta tags -->
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  	<!-- CSS -->
  	<link rel="stylesheet" href="../css/bootstrap.min.css">
  	<link rel="stylesheet" href="../css/alerts.css">
	
  </head>

  <body background="/images/background.jpg">
      
  	<div class="container">
  	  <div class='alert alert-success mt-4' role='alert'>
  	      <FONT SIZE=3><p><a><b>VERIFICACIÓN DE CUENTA</b></a></p></font>
  	      
  	      <?php
		  // Connection info. file
		  include './web_config/configuration_properties.php';

              // Connection variables
              $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

              // Check connection
              if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
              }

              $url = $_SERVER['REQUEST_URI'];
              $user = explode("user=", $url);
              $token = explode("token=", $url);

              if (isset($user[1]) && isset($token[1])) {
                $tokenPos = strpos($user[1],"&token=");
                $user = substr($user[1], 0, $tokenPos);

                $result = mysqli_query($conn, "SELECT username FROM user WHERE username = '$user'");

                $row = mysqli_fetch_assoc($result);

                if ($row == 0){
                    echo "<p style='margin-left: 2em; color:red'> <br><b>Hay un problema con este código de verificación (USUARIO NO REGISTRADO).</b><br></p>";
                } else  {
                  $result1 = mysqli_query($conn, "SELECT username FROM user WHERE verify_token = '$token[1]'");

                  $row1 = mysqli_fetch_assoc($result1);

                  if ($row1 == 0){
                      echo "<p style='margin-left: 2em; color:red'> <br><b>Este código de verificación ya no existe.</b><br></p>";
                  } else {
                      $update = mysqli_query($conn,"UPDATE user set verified = 'YES' WHERE username = '$user'");
                      if ($update) {
                        echo "<p style='margin-left: 2em; color:green'> <br><b>¡Cuenta <u>verificada</u> con éxito!</b><br></p>";
                        echo "<br>";
                        echo "<p><a class='btn btn-info' href='index'>Volver al Inicio</a></p>";
                      } else
                        echo "<p style='margin-left: 2em; color:red'> <br><b>Se ha producido un <u>error</u> inesperado. Inténtelo de nuevo más tarde.</b><br></p>";
                  }
                }

              } else
                header('Location: index');

              ?>

              </select>

              <?php
                 mysqli_close($conn);
              ?>

  	  </div>
  	 </div>
  	    
  	</body>
	
<?php 
  include "$root/web/footer.php"; 
?>
  
</html>
