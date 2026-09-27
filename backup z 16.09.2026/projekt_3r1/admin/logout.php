<?php
require '../cfg.php';
 
session_unset();
session_destroy();
header('Location: logowanie.php');
exit;
?>
