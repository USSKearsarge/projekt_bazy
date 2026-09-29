<?php

require '../cfg.php';
require 'csrf.php';

//czy zalogowany
if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

// Pobranie ID zalogowanego pracownika
$eid = $_SESSION['user_id'] ?? $_SESSION['eid'] ?? null;

if (!$eid) {
    header('Location: index.php');
    exit;
}


// Jakub Staniec


$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($new === '' || $confirm === '' || $current === '') {

        $message = 'Wypełnij wszystkie pola.';

    } elseif (mb_strlen($new) < 8) {

        $message = 'Nowe hasło musi mieć co najmniej 8 znaków.';

    } elseif ($new !== $confirm) {

        $message = 'Nowe hasło i potwierdzenie nie są zgodne.';

    } else {

        // Pobranie aktualnego hasła pracownika
        $stmt = $pdo->prepare(
            'SELECT password_hash FROM emp WHERE id = ?'
        );

        $stmt->execute([$eid]);

        $u = $stmt->fetch();

        if (!$u || !password_verify($current, $u['password_hash'])) {

            $message = 'Błędne aktualne hasło.';

        } else {

            // Utworzenie nowego hasha hasła
            $hash = password_hash($new, PASSWORD_DEFAULT);

            // Zapis nowego hasła do tabeli emp
            $upd = $pdo->prepare(
                'UPDATE emp SET password_hash = ? WHERE id = ?'
            );

            $upd->execute([$hash, $eid]);

            $message = 'Hasło zostało zmienione.';
        }
    }
}

include 'szablony/naglowek.php';
?>

<h2>Zmiana hasła</h2>

<?php if ($message): ?>
    <div class="alert alert-info">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<form method="post">
    <?php echo csrf_field(); ?>

    <div class="mb-3">
        <label>Aktualne hasło</label>
        <input
            type="password"
            name="current"
            class="form-control"
            required
        >
    </div>

    <div class="mb-3">
        <label>Nowe hasło</label>
        <input
            type="password"
            name="new"
            class="form-control"
            required
        >
    </div>

    <div class="mb-3">
        <label>Powtórz nowe hasło</label>
        <input
            type="password"
            name="confirm"
            class="form-control"
            required
        >
    </div>

    <button class="btn btn-primary">
        Zmień hasło
    </button>

</form>

<?php include 'szablony/stopka.php'; ?>