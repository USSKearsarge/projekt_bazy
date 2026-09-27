<?php
 
require '../cfg.php';
//PLIK BEZUŻYTECZNY
if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

header('Location: ustawienia_projektu.php');
exit;
