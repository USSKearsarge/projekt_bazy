<?php
$hash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['haslo'] ?? '') !== '') {
    $hash = password_hash($_POST['haslo'], PASSWORD_BCRYPT);
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Generator hasha</title>
</head>
<body>
    <form method="post">
        <label>Hasło: <input type="text" name="haslo" required></label>
        <button type="submit">Generuj hash</button>
    </form>
    <?php if ($hash): ?>
        <p>Hash:</p>
        <pre><?php echo htmlspecialchars($hash); ?></pre>
    <?php endif; ?>
</body>
</html>