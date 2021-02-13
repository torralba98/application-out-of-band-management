<header>

  <?php
    include "./web_config/configuration_properties.php";
    $url = $_SERVER["REQUEST_URI"];
    if (strpos($url, "/index") !== 0 && strpos($url, "/register") !== 0 && strpos($url, "/forgot") !== 0 && strpos($url, "/inicio") !== 0 && strpos($url, "/admin") == 0)
        echo "<br><br><br>";
    if ($url == '/forbidden') {
      $username = "";
    }
   ?>

  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top" id="mainNav">
    <div style="margin-bottom: 0px; margin-top: 0px;" class="container-fluid">
      <a class="navbar-brand js-scroll-trigger">
        <img src="/images/logoUDC.png" width="330" height="48">
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item active">
            <a class="nav-link">
              
            <?php

              if (isset($conn)) {

                if (!$username == "")  {
                    echo "<strong>¡Bienvenido/a </strong>";
                    echo $username;
                    echo "<strong>!</strong>";
                    echo "</a>";
                    echo "</li>";
                }

                if (strpos($url, "admin/") !== false)
                  echo "<li class='nav-item'>
                          <a class='nav-link' href=".$web_url."/inicio>Volver al Inicio</a>
                        </li>";
                else if (!$username == "") {
                  $isAdm = mysqli_query($conn, "SELECT is_admin FROM user WHERE username = '$username'");
                  if (mysqli_num_rows($isAdm)!=0) {
                    while ($isAdmRow = mysqli_fetch_array($isAdm)) {
                          if ($isAdmRow[0] == 1)
                            echo "<li class='nav-item'>
                                    <a class='nav-link' href='admin/admin-pan'>Panel de Administración</a>
                                  </li>";
                    }
                  }
                }
              } 

          ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php $web_url?>/logout">
            
            <?php
              if (!$username == "")
                  echo "Cerrar Sesión";
            ?>
            
              </a>
            </li>
          </a>
       </li>
      </ul>
    </div>
  </div>
</nav>

</header>
