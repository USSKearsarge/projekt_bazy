<?php
require '../cfg.php';

// Use client-specific templates
include 'szablony/naglowek.php';
?>

<div class="container py-4 text-center">
    <h2>Panel klienta</h2>
    <p class="lead">Wybierz akcję</p>
    <div class="d-flex justify-content-center gap-2">
        <?php if (!empty($_SESSION['klient_id'])): ?>
            <a class="btn btn-primary" href="sklep.php">Produkty</a>
            <a class="btn btn-success" href="koszyk.php">Koszyk</a>
            <a class="btn btn-secondary" href="edytuj.php">Edytuj dane</a>
        <?php else: ?>
            <a class="btn btn-primary" href="logowanie.php">Zaloguj</a>
            <a class="btn btn-success" href="rejestracja.php">Zarejestruj</a>
            <a class="btn btn-secondary" href="../index.php">Powrót na stronę główną</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'szablony/stopka.php'; ?>