<?php
require '../cfg.php';
// Destroy session and redirect to client login
session_unset();
session_destroy();
header('Location: logowanie.php');
exit;
?>