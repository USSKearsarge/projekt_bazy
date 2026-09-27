<?php
// Plik: permission.php | Tabela: permissions | Link: Uprawnienia | Kto widzi: ADMIN (1), HR (2)
require '../cfg.php';
// poprawił Piotrowski, Jakub Staniec
// if(!isset($_SESSION['zalogowany'])){
//     header('Location: logowanie.php');
//     exit;
// }

// $rola_id = $_SESSION['rola_id'] ?? 0;
// if (!in_array($rola_id, [1, 2])) {
//     header('Location: index.php');
//     exit;
// }

$stmt = $pdo->query("SELECT * FROM permissions ORDER BY username, menu");
$rows = $stmt->fetchAll();

include 'szablony/naglowek.php';
?>

<h2>Uprawnienia (Tabela: PERMISSIONS)</h2>
<p class="lead">Lista uprawnień systemowych.</p>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <?php foreach (array_keys($rows[0]) as $col): ?>
                    <th><?php echo htmlspecialchars($col); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <?php foreach ($r as $v): ?>
                        <td><?php echo htmlspecialchars((string)$v); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
