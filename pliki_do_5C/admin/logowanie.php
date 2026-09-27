<?php
// logowanie.php

session_start();
require '../cfg.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $haslo = $_POST['haslo'] ?? '';

    if ($email === '' || $haslo === '') {

        $error = 'Wpisz adres e-mail i hasło.';

    } else {

        // Tymczasowo e-mail znajduje się w kolumnie "phone"
        $stmt = $pdo->prepare(
            'SELECT id, first_name, title, username, password_hash
             FROM emp
             WHERE phone = ?
             LIMIT 1'
        );

        $stmt->execute([$email]);

        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$u) {
    $error = 'Nie znaleziono użytkownika: ' . htmlspecialchars($email);
} elseif (empty($u['password_hash'])) {
    $error = 'Użytkownik nie ma ustawionego hasła.';
} elseif (!password_verify($haslo, $u['password_hash'])) {
    $error = 'Hasło jest nieprawidłowe.';
} else {


            // Usuwamy stare dane sesji
            unset(
                $_SESSION['zalogowany'],
                $_SESSION['imie'],
                $_SESSION['perms'],
                $_SESSION['eid']
            );

            // Nowy identyfikator sesji
            session_regenerate_id(true);

            // Dane zalogowanego użytkownika
            $_SESSION['zalogowany'] = true;
            $_SESSION['user_id']    = $u['id'];
            $_SESSION['imie']       = $u['first_name'];
            $_SESSION['username']   = $u['username'];

            // Uprawnienia z roli (emp.title -> roles.nazwa -> menu_id)
            $perms = [];

            $stmtRoles = $pdo->prepare(
                'SELECT menu_id, typ_uprawnien FROM roles WHERE nazwa = ?'
            );
            $stmtRoles->execute([$u['title']]);
            foreach ($stmtRoles->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $perms[$row['menu_id']] = $row['typ_uprawnien'];
            }

            // Indywidualne nadpisania uprawnień (mają pierwszeństwo nad rolą)
            $stmtOverrides = $pdo->prepare(
                'SELECT menu_id, type FROM permissions WHERE username = ?'
            );
            $stmtOverrides->execute([$u['username']]);
            foreach ($stmtOverrides->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $perms[$row['menu_id']] = $row['type'];
            }

            $_SESSION['perms'] = $perms; // np. ['hr' => 'W', 'magazyn' => 'R']

            header('Location: index.php');
            exit;


        }

        $error = 'Nieprawidłowy e-mail lub hasło. Jeśli nie masz konta, <a href="rejestracja.php">zarejestruj się</a>.';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logowanie - System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light d-flex align-items-center vh-100">

<div class="container" style="max-width: 400px;">

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h3 class="card-title text-center mb-4">
                Logowanie
            </h3>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="logowanie.php">

                <div class="mb-3">
                    <label for="email" class="form-label">
                        E-mail
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="haslo" class="form-label">
                        Hasło
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="haslo"
                        name="haslo"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >
                    Zaloguj się
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
