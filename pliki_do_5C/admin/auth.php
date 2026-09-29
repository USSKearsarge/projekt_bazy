<?php
// Wspólne sprawdzanie uprawnień oparte o $_SESSION['perms'] (ustawiane w logowanie.php).
// perms ma postać ['hr' => 'W', 'magazyn' => 'R'] (klucz = menu_id, wartość = R lub W).
// Użycie: require '../cfg.php'; require 'auth.php';

function require_login(): void {
    if (!isset($_SESSION['zalogowany'])) {
        header('Location: logowanie.php');
        exit;
    }
}

// Czy użytkownik ma jakikolwiek dostęp (R lub W) do któregokolwiek z podanych menu?
function has_access(array $menus): bool {
    foreach ($menus as $m) {
        if (isset($_SESSION['perms'][$m])) {
            return true;
        }
    }
    return false;
}

// Czy użytkownik ma zapis (W) do któregokolwiek z podanych menu?
function can_write(array $menus): bool {
    foreach ($menus as $m) {
        if (($_SESSION['perms'][$m] ?? '') === 'W') {
            return true;
        }
    }
    return false;
}

// Wymaga zalogowania i dostępu (odczyt lub zapis) do jednego z menu.
function require_access(array $menus): void {
    require_login();
    if (!has_access($menus)) {
        header('Location: index.php');
        exit;
    }
}

// Wymaga zalogowania i zapisu (W) do jednego z menu.
function require_write(array $menus): void {
    require_login();
    if (!can_write($menus)) {
        header('Location: index.php');
        exit;
    }
}
