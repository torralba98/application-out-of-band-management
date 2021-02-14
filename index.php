<?php
  header('Content-Type: text/html; charset=UTF-8');
  session_start();
  
  if (isset($_SESSION['username'])){
    if (time() - $_SESSION['start'] < 3600)
          header('Location: inicio');
    else {
      session_unset($_SESSION['username']);
      session_destroy;
    }
  }

  $root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>

<!doctype html>
<html lang="es">

  <head>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <title>Servicio de Autenticación</title>
    
    <?php 
      $username = ""; 
      include "$root/web/header.php"; 
    ?>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="../css/alerts.css">
    
  </head>

  <body background="/images/background.jpg">
    
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<div class="card">
						<div class="loginBox">
							<img src="images/udc-logo.png" class="img-responsive" width="190" height="110">
              <h2>Inicio de sesión</h2>
              <br>
							<form action="inicio" method="post">
								<div class="form-group">
									<input type="username" class="form-control input-lg" name="username" placeholder="usuario (sin la parte @*.udc.es)" required>
								</div>
								<div class="form-group">
									<input type="password" class="form-control input-lg" autocomplete="on" name="password" placeholder="contraseña" required>
                  <FONT SIZE=2><a href="http://10.51.1.44/forgot" data-toggle="collapse" aria-expanded="false" aria-controls="collapse">¿Olvidaste tu contraseña?</a></FONT>
								</div>
                <br>
									<button type="submit" class="btn btn-success btn-block">Iniciar sesión</button>
              </form>
							<p>¿No tienes cuenta? <a href="http://10.51.1.44/register" data-toggle="collapse" aria-expanded="false" aria-controls="collapse"><u>Regístrate</u> ahora.</a></p>

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
