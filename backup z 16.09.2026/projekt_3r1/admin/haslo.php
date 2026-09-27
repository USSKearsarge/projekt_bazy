<?php
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$eid = $_SESSION['eid'] ?? null;
if (!$eid) {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($new === '' || $confirm === '' || $current === '') {
        $message = 'Wypełnij wszystkie pola.';
    } elseif ($new !== $confirm) {
        $message = 'Nowe hasło i potwierdzenie nie są zgodne.';
    } else {
        $stmt = $pdo->prepare('SELECT password_hash FROM pracownicy WHERE id = ?');
        $stmt->execute([$eid]);
        $u = $stmt->fetch();
        if (!$u || !password_verify($current, $u['password_hash'])) {
            $message = 'Błędne aktualne hasło.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE pracownicy SET password_hash = ? WHERE id = ?');
            $upd->execute([$hash, $eid]);
            $message = 'Hasło zostało zmienione.';
        }
    }
}

include 'szablony/naglowek.php';
?>

<h2>Zmiana hasła</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label>Aktualne hasło</label>
        <input type="password" name="current" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Nowe hasło</label>
        <input type="password" name="new" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Powtórz nowe hasło</label>
        <input type="password" name="confirm" class="form-control" required>
    </div>
    <button class="btn btn-primary">Zmień hasło</button>
</form>

<?php include 'szablony/stopka.php'; ?>
