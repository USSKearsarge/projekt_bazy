<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width,initial-scale=1'>
    <title>Panel Klienta</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5/dist/cosmo/bootstrap.min.css'>
    <link rel='stylesheet' href='../css/style.css'>
    <style>.nav-link.active{font-weight:600;}</style>
</head>
<body>
<nav class='navbar navbar-expand-lg navbar-light bg-light'>
    <div class='container-fluid'>
        <a class='navbar-brand' href='index.php'>Sklep</a>
        <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbars' aria-controls='navbars' aria-expanded='false' aria-label='Toggle navigation'>
            <span class='navbar-toggler-icon'></span>
        </button>
        <div class='collapse navbar-collapse' id='navbars'>
            <ul class='navbar-nav me-auto mb-2 mb-lg-0'>
                <li class='nav-item'><a class='nav-link' href='sklep.php'>Produkty</a></li>
                <li class='nav-item'><a class='nav-link' href='koszyk.php'>Koszyk</a></li>
                <li class='nav-item'><a class='nav-link' href='edytuj.php'>Edytuj dane</a></li>
            </ul>
            <div class='d-flex'>
<?php
    //Jakub Piotrowski
if(!empty(
    
    $_SESSION['klient_id']
)): ?>
                <div class="me-3 align-self-center">Witaj, <?php echo htmlspecialchars($_SESSION['klient_imie'] ?? 'Klient'); ?></div>
                <a class='btn btn-sm btn-outline-danger' href='logout.php'>Wyloguj</a>
<?php else: ?>
                <a class='btn btn-sm btn-primary me-2' href='logowanie.php'>Zaloguj</a>
                <a class='btn btn-sm btn-success' href='rejestracja.php'>Zarejestruj</a>
<?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class='container py-4'>
