<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  require 'PHPMailer/Exception.php';
  require 'PHPMailer/PHPMailer.php';
  require 'PHPMailer/SMTP.php';

  // Include config file
  require_once "./web_config/configuration_properties.php";

  // Connection variables
  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Define variables and initialize with empty values
  $email = $password = $confirm_password = "";
  $email_err = $password_err = $confirm_password_err = $registry_err = "";

  $root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>

<!doctype html>
<html lang="es">
  
  <head>
    
    <title>Servicio de Autenticación</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    
    <?php 
      $username = ""; 
      include "$root/web/header.php"; 
    ?>
    
    <link rel="stylesheet" href="../css/alerts.css">

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
		<link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    
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
        z-index:5;
      }

      #dialog {
          padding: 0px;
          margin-top: -600px;
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
              <br> <br>
              <h2>¡Regístrate!</h2>
              <br>
              <form id="registro" action="" method="post">
                  <div class="form-group">
                      <label>E-mail de la UDC</label>
                      <input type="text" name="email" class="form-control">
                      <div id="emailErr"></div>
                  </div>
                  <div class="form-group">
                      <label>Contraseña</label>
                      <input type="password" name="password" class="form-control">
                      <div id="passwordErr"></div>
                  </div>
                  <div class="form-group">
                      <label>Confirmar Contraseña</label>
                      <input type="password" name="confirm_password" class="form-control">
                      <div id="confirmErr"></div>
                  </div>
                  <div class="form-group">
                      <input type="submit" class="btn btn-primary" value="Registrarme">
                      <div id="registerErr"></div>
                      <br>
                  </div>
                  <p>¿Ya tienes una cuenta? <a href="index"><u>Inicia sesión</u> aquí</a>.</p>
              </form>

              <?php

                if($_SERVER["REQUEST_METHOD"] == "POST"){

                    // Validate email
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
                        } else {

                          $result = mysqli_query($conn, "SELECT email FROM user WHERE email = '$param_email'");

                          $row = mysqli_fetch_assoc($result);

                          if ($row > 0){
                             $email_err = "failure";
                              echo "<script> var html = document.createElement('div');";
                              echo "html.innerHTML = `<a style='color:#FF0000';><a style='color:#FF0000';>Este e-mail ya está en uso.</a>`;";
                              echo "document.getElementById('emailErr').appendChild(html); </script>";
                          } else
                              $email = trim($_POST["email"]);
                        }
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
                    if(empty($email_err) && empty($password_err) && empty($confirm_password_err)){

                        $username = ""; include "$root/web/header.php";
                        echo "<img id='loading' src='images/loading.gif' class='pageCover' >";
                        include "$root/web/footer.php";
                        echo "<div class=' pageCover'></div>";
                        flush();
                        ob_flush();
                        sleep(0.01);

                        $password2 = md5($password);
                        $usernamePos = strpos($param_email,"@udc.es");
                        $username = substr($param_email, 0, $usernamePos);

                        $token = md5($param_email).rand(10,9999);
                        
                        include "./web_config/configuration_properties.php";

                        $result = mysqli_query($conn, "INSERT INTO user (username, email, password, verified, verify_token) VALUES ('$username', '$param_email', '$password2', 'NO', '$token')");

                            if (!$result) {
                              $registry_err = "failure";
                              echo "<script> var html = document.createElement('div');";
                              echo "html.innerHTML = `<a style='color:#FF0000';><a style='color:#FF0000';>Algo ha fallado. Por favor, inténtelo de nuevo más tarde.</a>`;";
                              echo "document.getElementById('registerErr').appendChild(html); </script>";
                            } else {
                                $link = "<a href=".$web_url."/verify?user=".$username."&amp;token=".$token.">Click para verificar tu cuenta.</a>";

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
                                $mail->Subject  =  'Máster CiberSec - Verifica tu Cuenta';
                                $mail->Body    = '¡Enhorabuena, te has registrado con éxito! <br><br> Para finalizar el registro necesitamos que verifiques tu cuenta haciendo
                                                    click en el siguiente enlace. <br> '.$link.'';

                                flush();
                                ob_flush();
                                sleep(2);
                                if($mail->Send())
                                {
                                  echo "<script> document.getElementById('loading').style.display = 'none'; </script>";
                                  echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                                  echo "<div id='dialog'>";
                                   echo "<div id='dialog-bg'>";
                                        echo "<div id='dialog-title'>¡Listo!</div>";
                                         echo "<div id='dialog-description'>¡Te has registrado con éxito! Se te ha enviado un e-mail para que verifiques tu cuenta.</div>";
                                           echo "<div id='dialog-buttons'>";
                                           echo "<a href='index' class='large green button'>Aceptar</a>";
                                      echo "</div>";
                                    echo "</div>";
                                   echo "</div>";
                                   echo "<div class='pageCover'></div>";
                                }
                                else
                                {
                                  echo "<script> document.getElementById('loading').style.display = 'none'; </script>";
                                  echo "<link rel='icon' type='image/png' href='/images/icon.png' />";
                                  echo "<div id='dialog'>";
                                   echo "<div id='dialog-bg'>";
                                        echo "<div id='dialog-title'>¡Ups!</div>";
                                         echo "<div id='dialog-description'>Lo sentimos, no se ha podido enviar el e-mail de verificación de tu cuenta.</div>";
                                           echo "<div id='dialog-buttons'>";
                                           echo "<a href='index' class='large green button'>Aceptar</a>";
                                      echo "</div>";
                                    echo "</div>";
                                   echo "</div>";
                                   echo "<div class='pageCover'></div>";
                                }
                          }
                    }

                    // Close connection
                    mysqli_close($conn);
                }
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
