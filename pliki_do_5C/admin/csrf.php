<?php
// Pomocnicze funkcje CSRF. Użycie: require 'csrf.php'; (po require '../cfg.php', bo potrzebna sesja)

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

// Przerywa żądanie, jeśli token z POST jest nieprawidłowy.
function csrf_check(): void {
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        exit('Nieprawidłowy token CSRF.');
    }
}
