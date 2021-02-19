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

  $name = $port = $com = $description = $baudRate = $dataBits = $stopBits = $flowControl = $lock = "";

  $root = realpath($_SERVER["DOCUMENT_ROOT"]);
?>

<!doctype html>
<html lang="es">
 
  <head>

    <title>Panel Admin ~ Crear Dispositivo</title>
    <link rel="icon" type="image/png" href="/images/icon.png" />
    <?php include "$root/web/header.php"; ?>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
    <link rel="stylesheet" href="../admin/css/admin.css">
    <link rel="stylesheet" href="../css/alerts.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <script>

      function buttonClicked () {
        document.getElementById('pageCover').style.display = 'none';
        document.getElementById('dialog').style.display = 'none';
      }

    </script>

    <style>

        .example {
             width: 400px;
        }

        .example2 {
          margin: 0 0 -8px -1px;
             width: 150px;
        }

        .pageCover {
          position:fixed;
          z-index:1;
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

        <div><a id='volver' href='devices' class='large green button'>Volver</a></div>

	<FONT SIZE=4><i><p><a>Aquí podrás crear un <u>nuevo dispositivo</u>.</a></p></i></font>
        <?php

                if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['confirm'])) {

                  $name = $_POST['name'];
                  $port = $_POST['port'];
                  $com = $_POST['com'];
                  $description = $_POST['description'];
                  $baudRate = $_POST['baudRate'];
                  $dataBits = $_POST['dataBits'];
                  $stopBits = $_POST['stopBits'];
                  $flowControl = $_POST['flowControl'];
                  $lock = $_POST['lock'];

                  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

                  if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                  }

                  $add = mysqli_query($conn, "INSERT INTO device (in_use, server_status) VALUES ('NO', 'OFF')");
                  if (!$add)
                    die('Invalid query: ' . mysql_error());

                  $result = mysqli_query($conn, "SELECT Id FROM device
                                                     ORDER BY Id DESC LIMIT 1");

                  while ($row = mysqli_fetch_array($result)) {
                      $idDb = $row[0];
                  }

                  mysqli_close($conn);

                  $devices = simplexml_load_file("../web_config/devices_info.xml");

                  $device = $devices->addChild("device");
                  $device->addChild("name",$name);
                  $device->addChild("port",$port);
                  $device->addChild("com",$com);
                  $device->addChild("description",$description);
                  $device->addChild("baudRate",$baudRate);
                  $device->addChild("dataBits",$dataBits);
                  $device->addChild("stopBits",$stopBits);
                  $device->addChild("flowControl",$flowControl);
                  $device->addChild("lock",$lock);
                  $device->addChild("idDb",$idDb);

                  $dom = new DOMDocument("1.0");
                  $dom->preserveWhiteSpace = false;
                  $dom->formatOutput = true;
                  $dom->loadXML($devices->asXML());
                  $dom->save("../web_config/devices_info.xml");

                   echo "<div id='dialog'>";
                    echo "<div id='dialog-bg'>";
                         echo "<div id='dialog-title'>¡Listo!</div>";
                          echo "<div id='dialog-description'>¡Se ha añadido el dispositivo <b>$name</b> exitosamente!</div>";
                          echo "<div id='dialog-buttons'>";
                          echo "<a href='devices' class='large green button'>Volver al Menú</a>";
                    echo "</div>";
                   echo "</div>";
                  echo "</div>";
                  echo "<div class='pageCover'></div>";

                 }

                if($_SERVER['REQUEST_METHOD'] == "POST" and !isset($_POST['confirm'])) {

                  $name = $_POST['name'];
                  $port = $_POST['port'];
                  $com = $_POST['com'];
                  $description = $_POST['description'];
                  $baudRate = $_POST['baudRate'];
                  $dataBits = $_POST['dataBits'];
                  $stopBits = $_POST['stopBits'];
                  $flowControl = $_POST['flowControl'];
                  $lock = $_POST['lock'];

                  $devices = simplexml_load_file("../web_config/devices_info.xml");

                  foreach($devices as $device) {
                      if ($device->name == $name){
                        echo "<div id='dialog'>";
                         echo "<div id='dialog-bg'>";
                              echo "<div id='dialog-title'>¡Ups!</div>";
                               echo "<div id='dialog-description'>Ya existe un dispositivo con ese nombre, por favor, introduce otro diferente.</div>";
                                 echo "<div id='dialog-buttons'>";
                                 echo "<button onclick='buttonClicked();' class='large green button'>Aceptar</button>";
                            echo "</div>";
                          echo "</div>";
                         echo "</div>";
                         echo "<div id='pageCover' class='pageCover'></div>";
                         return;
                      }
                    }

                        if ($name == "" || $port == "" || $com == "" || $description == "" || $baudRate == "" || $dataBits == "" || $stopBits == "" || $flowControl == "" || $lock == "" ) {
                          echo "<div id='dialog'>";
                           echo "<div id='dialog-bg'>";
                                echo "<div id='dialog-title'>¡Ups!</div>";
                                 echo "<div id='dialog-description'>Por favor, completa todos los campos para poder continuar.</div>";
                                   echo "<div id='dialog-buttons'>";
                                   echo "<button onclick='buttonClicked();' class='large green button'>Aceptar</button>";
                              echo "</div>";
                            echo "</div>";
                           echo "</div>";
                           echo "<div id='pageCover' class='pageCover'></div>";
                        } else {

                              echo "<div id='dialog'>";
                               echo "<div id='dialog-bg'>";
                                    echo "<div id='dialog-title'>¿Estás seguro/a de quieres hacer esto?</div>";
                                     echo "<div id='dialog-description'>Recuerda que una vez confirmes se creará el dispositivo <b>$name</b>.</div>";
                                     echo "<div id='dialog-buttons'>";
                                     echo "<form action='' method='post'>";
                                     echo "<input style='background-color:red;' name='name' value='$name' hidden/>";
                                     echo "<input style='background-color:red;' name='port' value='$port' hidden/>";
                                     echo "<input style='background-color:red;' name='com' value='$com' hidden/>";
                                     echo "<input style='background-color:red;' name='description' value='$description' hidden/>";
                                     echo "<input style='background-color:red;' name='baudRate' value='$baudRate' hidden/>";
                                     echo "<input style='background-color:red;' name='dataBits' value='$dataBits' hidden/>";
                                     echo "<input style='background-color:red;' name='stopBits' value='$stopBits' hidden/>";
                                     echo "<input style='background-color:red;' name='flowControl' value='$flowControl' hidden/>";
                                     echo "<input style='background-color:red;' name='lock' value='$lock' hidden/>";
                                     echo "<input type='submit' name='confirm' class='large green button' value='¡Sí, quiero hacerlo!'>";
                                     echo "<a href='devices' class='large red button'>Mejor no lo hago.</a>";
                                     echo "</form>";
                                 echo "</div>";
                              echo "</div>";
                             echo "</div>";
                             echo "<div class='pageCover' id='pageCover'></div>";
                      }
                }


                ?>

                <br><p><b>¿Prefieres crear el dispositivo manualmente? Click </b></u><a href="http://10.51.1.44/admin/new-device-manual" data-toggle="collapse" aria-expanded="false" aria-controls="collapse"><u>aquí</u>.</a></p>

                <br><p style='margin-left: 2em; display:inline';> <u><b>Información del Dispositivo</b></u></p><br><br>

                <form id='updateDevice' action='' method='post'>
                  <p style='margin-left: 4em; display:inline';> <b><label> • Nombre:</label></b>
                    &nbsp;&nbsp;<textarea class='example2' cols='10' rows='1' id='name' name='name' form='updateDevice'><?php echo $name; ?></textarea></p><br><br>
                    <p style='margin-left: 4em; display:inline';> <b><label> • Puerto:</label></b>
                  &nbsp;&nbsp;<input type='number' id='port' name='port' value='<?php echo $port; ?>'></p><br><br>
                  <p style='margin-left: 4em; display:inline';> <b><label> • COM:</label></b>
                  &nbsp;&nbsp;<input type='text' id='com' name='com' value='<?php echo $com; ?>'></p><br><br>
                  <p style='margin-left: 4em; display:inline';> <b><label> • Descripción:</label></b><br>
                  <p style='margin-left: 5em; display:inline';><textarea class='example' cols='50' rows='5' id='description' name='description' form='updateDevice'><?php echo $description; ?></textarea></p><br><br>
                  <p style='margin-left: 4em; display:inline';> <b><label> • BaudRate:</label></b>
                  &nbsp;&nbsp;<input type='number' id='baudRate' name='baudRate' value='<?php echo $baudRate; ?>'></p><br><br>
                  <p style='margin-left: 4em; display:inline';> <b><label> • DataBits:</label></b>
                  &nbsp;&nbsp;<input type='number' id='dataBits' name='dataBits' value='<?php echo $dataBits; ?>'></p><br><br>
                  <p style='margin-left: 4em; display:inline';> <b><label> • StopBits:</label></b>
                  &nbsp;&nbsp;<input type='number' id='stopBits' name='stopBits' value='<?php echo $stopBits; ?>' ></p><br><br>
                  <p style='margin-left: 4em; display:inline';> <b><label> • FlowControl:</label></b>
                  &nbsp;&nbsp;<select name='flowControl' id='flowControl'>
                    <option <?php if($flowControl== 'false'){echo("selected");}?> value='false'>False</option>
                    <option <?php if($flowControl== 'true'){echo("selected");}?> value='true'>True</option>
                  </select><br><br>
                  <p style='margin-left: 4em; display:inline';> <b><label> • Lock:</label></b>
                  &nbsp;&nbsp;<select name='lock' id='lock'>
                    <option <?php if($lock == 'false'){echo("selected");}?> value='false'>False</option>
                    <option <?php if($lock == 'true'){echo("selected");}?> value='true'>True</option>
                  </select><br><br>
                  <p style='margin-left: 4em; display:inline';> <input type='submit' class='large green button' value='Crear Dispositivo'> </p>
                  <p style='margin-left: 2em; display:inline';> <input type='reset' class='large black button' value='Restablecer'> </p>
                </form><br>
      </div>
    </div>
  </body>
  
<br><br>

  <?php include "$root/web/footer.php"; ?>
  
</html>
