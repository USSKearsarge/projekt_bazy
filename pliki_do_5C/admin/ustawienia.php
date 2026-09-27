<?php
 
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

header('Location: ustawienia_projektu.php');
exit;
