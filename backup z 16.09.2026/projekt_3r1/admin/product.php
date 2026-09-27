<?php
// Plik: product.php | Tabela: product | Link: Zarządzanie Produktami | Kto widzi: ADMIN (1), MAGAZYN (4)
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
if (!in_array($rola_id, [1, 4])) {
    header('Location: index.php');
    exit;
}
// $rola_id = 1;
$stmt = $pdo->query("SELECT * FROM product ORDER BY id");
$rows = $stmt->fetchAll();

include 'szablony/naglowek.php';
?>

<h2>Zarządzanie Produktami (Tabela: PRODUCT)</h2>
<p class="lead">Lista produktów dostępnych w sklepie.</p>

<?php if (count($rows) === 0): //poprawił Piotrowski?>
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
