<?php
// permission.php | Tabela: permissions (username, menu_id, type) | dostęp: menu 'hr'
require '../cfg.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

$perms = $_SESSION['perms'] ?? [];
if (!isset($perms['hr'])) {
    header('Location: index.php');
    exit;
}

// W tabeli nie ma kolumny "menu" – jest menu_id (nazwę menu dobieramy z tabeli menu)
$rows = $pdo->query(
    'SELECT p.username, p.menu_id, m.name AS menu_name, p.type
     FROM permissions p
     LEFT JOIN menu m ON p.menu_id = m.id
     ORDER BY p.username, p.menu_id'
)->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<h2>Uprawnienia (Tabela: PERMISSIONS)</h2>
<p class="lead">Lista uprawnień systemowych (R = odczyt, W = zapis).</p>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr><th>Użytkownik</th><th>Menu ID</th><th>Menu</th><th>Typ</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                    <td><?php echo htmlspecialchars($r['menu_id']); ?></td>
                    <td><?php echo htmlspecialchars((string)$r['menu_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$r['type']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
