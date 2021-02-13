/* Destroy current user session */

<?php
session_start();
session_unset($_SESSION['username']);
session_unset($_SESSION['pop-up']);
session_destroy();

header('location: index');
?>
