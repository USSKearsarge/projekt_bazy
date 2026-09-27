<?php
$host='localhost';
$db='baza_testowa';
$user='root';
$pass='';
$charset='utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try{
	$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}catch(PDOException $e){
	exit('Błąd PDO: '.$e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
	session_set_cookie_params(['lifetime' => 86400, 'path' => '/', 'httponly' => true]);
	session_start();
}
?>