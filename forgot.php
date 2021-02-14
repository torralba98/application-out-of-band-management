<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  require 'PHPMailer/Exception.php';
  require 'PHPMailer/PHPMailer.php';
  require 'PHPMailer/SMTP.php';

  // Include config file
  require_once "./web_config/configuration_properties.php";

  $param_email = $email = $confirm_password = $password = "";
  $email_err = $password_err = $confirm_password_err = $registry_err = "";

  $root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>

<!doctype html>
<html lang="es">
  
  <head>
    
    <title>Contraseña Olvidada</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    
    <?php $
      username = ""; 
      include "$root/web/header.php"; 
    ?>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
		<link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="../css/alerts.css">
    
    <style>
      .pageCover {
        position:fixed;
        z-index:1;
        background-color:rgba(0,0,0,.25);
        width:100vw;
        height:100vh;
        top:0;
        left:0;
      }

      .card {
        z-index:0;
      }

      #dialog{
        margin-top: -450px;
        z-index:5;
      }

    </style>
  
  </head>

  <body background="/images/background.jpg">
    
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<div class="card">
						<div class="loginBox">
							<img style="vertical-align:middle;margin:0px 0px" src="images/udc-logo.png" class="img-responsive" width="190" height="110">
              <h3>Recuperar Contraseña</h3>
              <br>
              
              <?php

                // Connection variables
                $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                // Check connection
                if (!$conn) {
                  die("Connection failed: " . mysqli_connect_error());
                }

                $url = $_SERVER['REQUEST_URI'];
                $user = explode("user=", $url);
                if (isset($user[1])) {
                    $user = explode("&token=", $user[1]);
                    $token = explode("&token=", $url);
                    $result = mysqli_query($conn, "SELECT username, exp_date, reset_link_token FROM user WHERE username = '$user[0]'");
                    if (mysqli_num_rows($result)==0) {
                      echo "<br><br><br><br>";
                      $usuario = $user[0];
                      echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                      echo "<div id='dialog'>";
                       echo "<div id='dialog-bg'>";
                            echo "<div id='dialog-title'>¡Ups!</div>";
                             echo "<div id='dialog-description'>Lo sentimos, el usuario $user[0] no existe.</div>";
                               echo "<div id='dialog-buttons'>";
                               echo "<a href='index' class='large green button'>Aceptar</a>";
                          echo "</div>";
                        echo "</div>";
                       echo "</div>";
                       echo "<div class='pageCover'></div>";
                    }
                    else {
                      while ($row = mysqli_fetch_array($result)) {
                        $date = time();
                        $date = date("Y-m-d H:i:s", $date);
                        if ($row[1] < $date){
                            echo "<br><br><br><br>";
                            echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                            echo "<div id='dialog'>";
                             echo "<div id='dialog-bg'>";
                                  echo "<div id='dialog-title'>¡Ups!</div>";
                                   echo "<div id='dialog-description'>Lo sentimos, el enlace de recuperación ha caducado.</div>";
                                     echo "<div id='dialog-buttons'>";
                                     echo "<a href='index' class='large green button'>Aceptar</a>";
                                echo "</div>";
                              echo "</div>";
                             echo "</div>";
                             echo "<div class='pageCover'></div>";
                        } else if ($token[1] != $row[2]) {
                                  echo "<br><br><br><br>";
                                  echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                                  echo "<div id='dialog'>";
                                   echo "<div id='dialog-bg'>";
                                        echo "<div id='dialog-title'>¡Ups!</div>";
                                         echo "<div id='dialog-description'>Lo sentimos, el código de restablecimiento no es válido.</div>";
                                           echo "<div id='dialog-buttons'>";
                                           echo "<a href='index' class='large green button'>Aceptar</a>";
                                      echo "</div>";
                                    echo "</div>";
                                   echo "</div>";
                                   echo "<div class='pageCover'></div>";
                                } else  {

              ?>

              <form action="" method="post">
                <div class="form-group">
                    <label>Introduce tu nueva contraseña</label>
                    <input type="password" name="password" class="form-control">
                    <div id="passwordErr"></div>
                </div>
                  <div class="form-group">
                      <label>Confirmar nueva contraseña</label>
                      <input type="password" name="confirm_password" class="form-control">
                      <div id="confirmErr"></div>
                  </div>
                  <div class="form-group">
                      <input type="submit" name="actualizar" class="btn btn-primary" value="Restablecer Contraseña">
                      <div id="registerErr"></div>
                  </div>
                  <p>¿Recuerdas tu contraseña? <a href="index"><u>Inicia sesión</u> aquí</a>.</p>
              </form>

              <?php
              
                if($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST['actualizar'])){

                  // Connection variables
                  $conn2 = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  // Check connection
                  if (!$conn2) {
                    die("Connection failed: " . mysqli_connect_error());
                  }
                  
                  // Validate password
                  if(empty(trim($_POST["password"]))){
                      $password_err = "failure";
                      echo "<script> var html = document.createElement('div');";
                      echo "html.innerHTML = `<a style='color:#FF0000';><a style='color:#FF0000';>Por favor, introduzca una contraseña.</a>`;";
                      echo "document.getElementById('passwordErr').appendChild(html); </script>";
                  } elseif(strlen(trim($_POST["password"])) < 6){
                      $password_err = "failure";
                      echo "<script> var html = document.createElement('div');";
                      echo "html.innerHTML = `<a style='color:#FF0000';><a style='color:#FF0000';>La contraseña debe tener al menos 6 caracteres.</a>`;";
                      echo "document.getElementById('passwordErr').appendChild(html); </script>";
                  } else{
                      $password = trim($_POST["password"]);
                  }
                  
                  echo $password;

                  // Validate confirm password
                  if(empty(trim($_POST["confirm_password"]))){
                      $confirm_password_err = "failure";
                      echo "<script> var html = document.createElement('div');";
                      echo "html.innerHTML = `<a style='color:#FF0000';><a style='color:#FF0000';>Por favor, confirme la contraseña.</a>`;";
                      echo "document.getElementById('confirmErr').appendChild(html); </script>";
                  } else{
                      $confirm_password = trim($_POST["confirm_password"]);
                      if(empty($password_err) && ($password != $confirm_password)){
                          $confirm_password_err = "failure";
                          echo "<script> var html = document.createElement('div');";
                          echo "html.innerHTML = `<a style='color:#FF0000';><a style='color:#FF0000';>Las contraseñas no coinciden.</a>`;";
                          echo "document.getElementById('confirmErr').appendChild(html); </script>";
                      }
                  }

                  // Check input errors before inserting in database
                  if(empty($password_err) && empty($confirm_password_err)){
                      $pass = md5($password);

                      $url = $_SERVER['REQUEST_URI'];
                      $user = explode("user=", $url);
                      if (isset($user[1]))
                          $user = explode("&token=", $user[1]);

                      $result = mysqli_query($conn2, "UPDATE user SET password = '$pass' WHERE username = '$user[0]'");

                      if (!$result) {
                        $registry_err = "failure";
                        echo "<script> var html = document.createElement('div');";
                        echo "html.innerHTML = `<a style='color:#FF0000';><a style='color:#FF0000';>Algo ha fallado. Por favor, inténtelo de nuevo más tarde.</a>`;";
                        echo "document.getElementById('registerErr').appendChild(html); </script>";
                      } else {
                          echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                          echo "<div id='dialog'>";
                           echo "<div id='dialog-bg'>";
                                echo "<div id='dialog-title'>¡Listo!</div>";
                                 echo "<div id='dialog-description'>¡Se ha actualizado tu contraseña con éxito! Ya puedes iniciar sesión.</div>";
                                   echo "<div id='dialog-buttons'>";
                                   echo "<a href='index' class='large green button'>Aceptar</a>";
                              echo "</div>";
                            echo "</div>";
                           echo "</div>";
                           echo "<div class='pageCover'></div>";
                        }
                        
                     // Close connection
                     mysqli_close($conn2);
                     }
                    }
                   }
                  }
                 }
                } else {
                  
              ?>

              <form action="" method="post">
                  <div class="form-group">
                      <label>Introduce tu e-mail de la UDC</label>
                      <input type="text" name="email" class="form-control">
                      <div id="emailErr"></div>
                  </div>
                  <div class="form-group">
                      <input type="submit" name="submit1" class="btn btn-primary" value="Enviar Código de Recuperación">
                  </div>
                  <p>¿Recuerdas tu contraseña? <a href="index"><u>Inicia sesión</u> aquí</a>.</p>
              </form>

            <?php

              if($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST['submit1'])){

                  // Connection variables
                  $conn1 = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  // Check connection
                  if (!$conn1) {
                    die("Connection failed: " . mysqli_connect_error());
                  }

                  // Validate username
                  
                  if(empty(trim($_POST["email"]))){
                     $email_err = "failure";
                     echo "<script> var html = document.createElement('div');";
                     echo "html.innerHTML = `<a style='color:#FF0000';>Por favor, introduce tu e-mail de la UDC.</a>`;";
                     echo "document.getElementById('emailErr').appendChild(html); </script>";
                  } else{
                      $param_email = trim($_POST["email"]);

                      if (strpos($param_email, "@udc.es") === false) {
                        $email_err = "failure";
                        echo "<script> var html = document.createElement('div');";
                        echo "html.innerHTML = `<a style='color:#FF0000';>Por favor, introduce tu e-mail de la UDC.</a>`;";
                        echo "document.getElementById('emailErr').appendChild(html); </script>";
                      }
                      else {
                        $usernamePos = strpos($param_email,"@udc.es");
                        $username = substr($param_email, 0, $usernamePos);

                        $result = mysqli_query($conn1, "SELECT username FROM user WHERE username = '$username'");

                        $row = mysqli_fetch_assoc($result);

                        if ($row == 0){
                          $email_err = "failure";
                          echo "<script> var html = document.createElement('div');";
                          echo "html.innerHTML = `<a style='color:#FF0000';>No existe ningún usuario asociado a este e-mail.</a>`;";
                          echo "document.getElementById('emailErr').appendChild(html); </script>";
                        } else {
                          
                           $username = ""; include "$root/web/header.php";
                           echo "<img id='loading' src='images/loading.gif' class='pageCover' >";
                           include "$root/web/footer.php";
                           echo "<div class=' pageCover'></div>";
                           flush();
                           ob_flush();
                           sleep(0.01);
                           
                           include "./web_config/configuration_properties.php";

                           $param_email = strtolower($param_email);
                           $token = md5($username).rand(10,9999);

                           $expFormat = mktime(
                           date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y")
                           );

                          $expDate = date("Y-m-d H:i:s",$expFormat);

                          $update = mysqli_query($conn1,"UPDATE user set reset_link_token='" . $token . "' ,exp_date='" . $expDate . "' WHERE username='" . $username . "'");

                          $link = "<a href=".$web_url."forgot?user=".$username."&amp;token=".$token."'>Click para restablecer la contraseña.</a>";

                          $mail = new PHPMailer(true);

                          $mail->CharSet =  "utf-8";
                          $mail->SMTPDebug = 0;
                          $mail->IsSMTP();
                          $mail->Host = "smtp.gmail.com";
                          $mail->SMTPAuth = true;
                          $mail->Username = $emailPHP;
                          $mail->Password = $emailPHPpass;
                          $mail->SMTPSecure = "tls";
                          $mail->Port = 587;

                          $mail->setFrom($emailPHP, 'Soporte CiberSec - UDC');
                          $mail->AddAddress($param_email);

                          $mail->IsHTML(true);
                          $mail->Subject  =  'Master CiberSec - Restablecer Contraseña';
                          $mail->Body    = 'Click en el siguiente enlace para restablecer tu contraseña. <br> '.$link.'';

                          flush();
                          ob_flush();
                          sleep(2);
                          if($mail->Send())
                          {
                            echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                            echo "<script> document.getElementById('loading').style.display = 'none'; </script>";
                            echo "<div id='dialog'>";
                             echo "<div id='dialog-bg'>";
                                  echo "<div id='dialog-title'>¡Listo!</div>";
                                   echo "<div id='dialog-description'>Se ha enviado un e-mail con el código de recuperación. Revisa la bandeja de entrada de tu correo electrónico.</div>";
                                     echo "<div id='dialog-buttons'>";
                                     echo "<a href='index' class='large green button'>Aceptar</a>";
                                echo "</div>";
                              echo "</div>";
                             echo "</div>";
                             echo "<div class='pageCover'></div>";
                          }
                          else
                          {
                            echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                            echo "<script> document.getElementById('loading').style.display = 'none'; </script>";
                            echo "<div id='dialog'>";
                             echo "<div id='dialog-bg'>";
                                  echo "<div id='dialog-title'>¡Ups!</div>";
                                   echo "<div id='dialog-description'>Lo sentimos, no se ha podido enviar el código de recuperación.</div>";
                                     echo "<div id='dialog-buttons'>";
                                     echo "<a href='index' class='large green button'>Aceptar</a>";
                                echo "</div>";
                              echo "</div>";
                             echo "</div>";
                             echo "<div class='pageCover'></div>";
                          }
                        }
                    }
                  }

                  // Close connection
                  mysqli_close($conn1);
              }
              }

              // Close connection
              mysqli_close($conn);

            ?>

						</div>
					</div>
				</div>
			</div>
		</div>

</body>

<?php 
  include "$root/web/footer.php"; 
?>

</html>
