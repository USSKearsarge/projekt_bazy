<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width,initial-scale=1'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5/dist/cosmo/bootstrap.min.css'>
    <link rel='stylesheet' href='../css/style.css'> 
</head>
<body>
<nav class='navbar navbar-expand-lg navbar-dark bg-primary'>
    <div class='container-fluid'>
        <a class='navbar-brand' href='index.php'>Panel</a>
        <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbars' aria-controls='navbars' aria-expanded='false' aria-label='Toggle navigation'>
            <span class='navbar-toggler-icon'></span>
        </button>
        <div class='collapse navbar-collapse' id='navbars'>
            <ul class='navbar-nav me-auto mb-2 mb-lg-0'>
                <li class='nav-item'><a class='nav-link' href='index.php'>Dashboard</a></li>
                <li class='nav-item'><a class='nav-link' href='../index.php'>Strona główna</a></li>
            </ul>
            <div class='d-flex'>
                <?php if(isset($_SESSION['imie']) || isset($_SESSION['klient_imie'])): ?>
                    <?php
                        //Poprawił Piotrowski
                        $imie = htmlspecialchars($_SESSION['klient_imie'] ?? $_SESSION['imie'] ?? 'Użytkownik');
                        $rola_id = $_SESSION['rola_id'] ?? 0;
                        $role_names = [1 => 'ADMIN', 2 => 'HR', 3 => 'KIEROWNIK', 4 => 'MAGAZYN', 5 => 'KLIENT'];
                        $rola_nazwa = $role_names[$rola_id] ?? '';
                    ?>
                        <div class="me-3 align-self-center">Zalogowany: <?php echo htmlspecialchars($_SESSION['imie'] ?? 'Użytkownik'); ?></div>
                        <a class='btn btn-sm btn-outline-light' href='logout.php'>Wyloguj</a>
                <?php else: ?>
                    <a class='btn btn-outline-light btn-sm' href='logowanie.php'>Zaloguj</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class='container py-4'>