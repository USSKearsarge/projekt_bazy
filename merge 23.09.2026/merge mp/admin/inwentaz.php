<?php
//Michał Pałyga 
require '../cfg.php';

if (!isset($_SESSION['zalogowany'])) {
   header('Location: logowanie.php');
   exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
if (!in_array($rola_id, [1, 2, 3, 4])) {
    header('Location: index.php');
    exit;
}
$rola_id = 1;

$stmt = $pdo->query(
    "SELECT inventory.product_id, inventory.warehouse_id, inventory.amount_in_stock AS ilosc, product.name AS 'nazwa produktu', CONCAT(warehouse.city, ' ', warehouse.address) AS 'nazwa magazynu'
     FROM inventory
     LEFT JOIN product    ON inventory.product_id   = product.id
     LEFT JOIN warehouse  ON inventory.warehouse_id = warehouse.id
     ORDER BY product.name, 'nazwa magazynu'"
);
$rows = $stmt->fetchAll();

$canManage = in_array($rola_id, [1, 2, 3, 4]);

// fetch products and warehouses for form dropdowns
$products = $pdo->query('SELECT id, name FROM product ORDER BY name')->fetchAll();
$warehouses = $pdo->query('SELECT id, city, address FROM warehouse ORDER BY city')->fetchAll();

// Handle save (create or update)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $warehouse_id = (int)($_POST['warehouse_id'] ?? 0);
    $ilosc = (int)($_POST['amount_in_stock'] ?? 0);
    $code = trim($_POST['code'] ?? '');

    // determine if this is an edit (original keys provided)
    $orig_prod = isset($_POST['orig_product_id']) ? (int)$_POST['orig_product_id'] : null;
    $orig_mag = isset($_POST['orig_warehouse_id']) ? (int)$_POST['orig_warehouse_id'] : null;

    if ($orig_prod !== null && $orig_mag !== null) {
        // update existing row (possibly changing keys)
        $stmt = $pdo->prepare('UPDATE inventory SET product_id=?, warehouse_id=?, code=?, amount_in_stock=? WHERE product_id=? AND warehouse_id=?');
        $stmt->execute([$product_id, $warehouse_id, $code, $ilosc, $orig_prod, $orig_mag]);
    } else {
        // insert (ignore duplicates)
        $stmt = $pdo->prepare('REPLACE INTO inventory (product_id, warehouse_id, code, amount_in_stock) VALUES (?,?,?,?)');
        $stmt->execute([$product_id, $warehouse_id, $code, $ilosc]);
    }

    header('Location: inwentaz.php');
    exit;
}

// Handle delete
if ($canManage && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id']) && isset($_GET['warehouse_id'])) {
    $prod_id = (int)$_GET['product_id'];
    $ware_id = (int)$_GET['warehouse_id'];
    $stmt = $pdo->prepare('DELETE FROM inventory WHERE product_id=? AND warehouse_id=?');
    $stmt->execute([$prod_id, $ware_id]);
    header('Location: inwentaz.php');
    exit;
}

// If editing, load the row
$edit = null;
if ($canManage && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['product_id']) && isset($_GET['warehouse_id'])) {
    $prod_id = (int)$_GET['product_id'];
    $ware_id = (int)$_GET['warehouse_id'];
    $stmt = $pdo->prepare('SELECT * FROM inventory WHERE product_id=? AND warehouse_id=?');
    $stmt->execute([$prod_id, $ware_id]);
    $edit = $stmt->fetch();
}

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Stan Magazynowy (Tabela: INVENTORY)</h2>
    <?php if ($canManage): ?>
        <div>
            <a class="btn btn-sm btn-success" href="inwentaz.php?action=add">Dodaj wpis</a>
        </div>
    <?php endif; ?>
</div>
<p class="lead">Aktualny stan zapasów.</p>

<?php
$act = $_GET['action'] ?? '';
if ($canManage && in_array($act, ['add', 'edit'])):
    $prodVal = $edit['product_id'] ?? '';
    $magVal = $edit['warehouse_id'] ?? '';
    $codeVal = $edit['code'] ?? '';
    $iloscVal = $edit['amount_in_stock'] ?? '';
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <?php if ($act === 'edit' && $edit): ?>
            <input type="hidden" name="orig_product_id" value="<?php echo htmlspecialchars($edit['product_id']); ?>">
            <input type="hidden" name="orig_warehouse_id" value="<?php echo htmlspecialchars($edit['warehouse_id']); ?>">
        <?php endif; ?>
        <div class="row g-2">
            <div class="col-md-4">
                <select name="produkt_id" class="form-select">
                    <?php foreach ($products as $prod): ?>
                        <option value="<?php echo $prod['id']; ?>" <?php echo ($prod['id'] == $prodVal) ? 'selected' : ''; ?>><?php echo htmlspecialchars($prod['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="magazyn_id" class="form-select">
                    <?php foreach ($warehouses as $ware): ?>
                        <option value="<?php echo $ware['id']; ?>" <?php echo ($ware['id'] == $magVal) ? 'selected' : ''; ?>><?php echo htmlspecialchars($ware['address']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input class="form-control" name="ilosc" placeholder="Ilość" value="<?php echo htmlspecialchars($iloscVal); ?>"></div>
            <div class="col-md-2"><input class="form-control" name="code" placeholder="code" value="<?php echo htmlspecialchars($codeVal); ?>"></div>
        </div>
        <div class="mt-2">
            <button class="btn btn-primary btn-sm">Zapisz</button>
            <a class="btn btn-secondary btn-sm" href="inwentaz.php">Anuluj</a>
        </div>
    </form>
<?php endif; ?>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <?php
    $labelMap = [
        'product_id' => 'Produkt ID',
        'warehouse_id' => 'Magazyn ID',
        'amount_in_stock' => 'Ilość',
        'product_name' => 'Produkt',
        'warehouse_address' => 'Magazyn',
    ];
    ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <?php foreach (array_keys($rows[0]) as $col): ?>
                    <th><?php echo htmlspecialchars($labelMap[$col] ?? ucfirst($col)); ?></th>
                <?php endforeach; ?>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $k => $v): ?>
                        <td><?php echo htmlspecialchars((string)$v); ?></td>
                    <?php endforeach; ?>
                    <?php if ($canManage): ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="inwentaz.php?action=edit&product_id=<?php echo urlencode($row['product_id']); ?>&warehouse_id=<?php echo urlencode($row['warehouse_id']); ?>">Edytuj</a>
                            <a class="btn btn-sm btn-outline-danger" href="inwentaz.php?action=delete&product_id=<?php echo urlencode($row['product_id']); ?>&warehouse_id=<?php echo urlencode($row['warehouse_id']); ?>" onclick="return confirm('Na pewno usunąć wpis?');">Usuń</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>