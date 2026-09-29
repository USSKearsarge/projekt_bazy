<?php
// Plik: inventory.php | Tabela: inventory | Link: Stan Magazynowy | Podgląd (tylko odczyt)
require '../cfg.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

// Uprawnienia jak w index.php (moduł magazynu: 'magazyn' lub 'warehouse')
$perms = $_SESSION['perms'] ?? [];
if (!isset($perms['magazyn']) && !isset($perms['warehouse'])) {
    header('Location: index.php');
    exit;
}

// Tabela warehouse nie ma kolumny name – nazwę składamy z miasta i adresu
$stmt = $pdo->query(
    "SELECT i.product_id, i.warehouse_id, i.amount_in_stock AS quantity,
            p.name AS product_name,
            CONCAT(w.city, ' ', w.address) AS warehouse_name
     FROM inventory i
     LEFT JOIN product p ON i.product_id = p.id
     LEFT JOIN warehouse w ON i.warehouse_id = w.id
     ORDER BY p.name, w.city, w.address"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<h2>Stan Magazynowy (Tabela: INVENTORY)</h2>
<p class="lead">Aktualny stan zapasów.</p>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <?php
    $labelMap = [
        'product_id'     => 'Produkt ID',
        'warehouse_id'   => 'Magazyn ID',
        'quantity'       => 'Ilość',
        'product_name'   => 'Produkt',
        'warehouse_name' => 'Magazyn',
    ];
    ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <?php foreach (array_keys($rows[0]) as $col): ?>
                    <th><?php echo htmlspecialchars($labelMap[$col] ?? $col); ?></th>
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
