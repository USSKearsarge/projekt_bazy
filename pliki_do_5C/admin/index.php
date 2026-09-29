<?php 
require '../cfg.php'; 

 
if(!isset($_SESSION['zalogowany'])){ 
    header('Location: logowanie.php'); 
    exit;
} 

$imie  = $_SESSION['imie'] ?? 'Użytkowniku';
$perms = $_SESSION['perms'] ?? [];

// Sprawdza, czy zalogowany użytkownik ma jakikolwiek dostęp (R lub W) do danego menu_id
function ma_dostep(string $menu_id, array $perms): bool {
    return isset($perms[$menu_id]);
}

// include header before any output
include 'szablony/naglowek.php';
?>

<!-- Logout button -->
<div class="text-end mb-3">
    <a class="btn btn-sm btn-danger" href="logout.php">Wyloguj</a>
</div>

<h2>Witaj w Panelu Pracownika, <?php echo htmlspecialchars($imie); ?>!</h2>

<div class="row">

<div class="col-md-4 mb-4">
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">⚙️ Zarządzanie Kontem</div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><a href="haslo.php">Zmień hasło</a></li>
                <li class="list-group-item"><a href="ustawienia_projektu.php">Ustawienia (ustawienia_projektu)</a></li>
                <li class="list-group-item"><a href="logout.php" class="text-danger">Wyloguj</a></li>
            </ul>
        </div>
    </div>
</div>

<?php if (ma_dostep('hr', $perms)): // Kadry ?>
    <div class="col-md-4 mb-4">
        <div class="card border-success">
            <div class="card-header bg-success text-white">👥 Moduł Kadr (HR)</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="emp.php">Lista Pracowników</a></li>
                    <li class="list-group-item"><a href="role.php">Role</a></li>
                    <li class="list-group-item"><a href="title.php">Stanowiska</a></li>
                    <li class="list-group-item"><a href="dept.php">Działy</a></li>
                    <li class="list-group-item"><a href="uprawnienia.php">Uprawnienia</a></li>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// UWAGA: w tabelach menu/roles nie ma osobnego menu_id dla sprzedaży/zamówień.
// Na razie ten moduł pokazuje się każdemu, kto ma dostęp do 'hr' lub 'magazyn'.
// Docelowo warto dodać własny menu_id (np. 'sales') w tabelach menu i roles.
if (ma_dostep('hr', $perms) || ma_dostep('magazyn', $perms)):
?>
    <div class="col-md-4 mb-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">🛒 Moduł Sprzedaży i Zamówień</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="zamowienia.php">Lista Zamówień</a></li>
                    <li class="list-group-item"><a href="klienci.php">Klienci</a></li>
                    <li class="list-group-item"><a href="cenniki.php">Cenniki</a></li>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (ma_dostep('magazyn', $perms) || ma_dostep('warehouse', $perms)): // Magazyn ?>
    <div class="col-md-4 mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">📦 Moduł Magazynu</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="produkty.php">Produkty</a></li>
                    <li class="list-group-item"><a href="magazyny.php">Magazyny</a></li>
                    <li class="list-group-item"><a href="inwentaz.php">Stan Magazynowy</a></li>
                    <li class="list-group-item"><a href="region.php">Regiony</a></li>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

<?php 
include 'szablony/stopka.php'; 
?>
