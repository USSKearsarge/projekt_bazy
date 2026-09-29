<?php
// inwentaz.php | Tabela: inventory (klucz główny: product_id + warehouse_id)
require '../cfg.php';
require 'csrf.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

$perms = $_SESSION['perms'] ?? [];
if (!isset($perms['magazyn']) && !isset($perms['warehouse'])) {
    header('Location: index.php');
    exit;
}
$canManage = ($perms['magazyn'] ?? '') === 'W' || ($perms['warehouse'] ?? '') === 'W';

// Ochrona CSRF dla wszystkich żądań POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$err = '';
$products = $pdo->query('SELECT id, name FROM product ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$warehouses = $pdo->query('SELECT id, city, address FROM warehouse ORDER BY city, address')->fetchAll(PDO::FETCH_ASSOC);
$productIds = array_map('intval', array_column($products, 'id'));
$warehouseIds = array_map('intval', array_column($warehouses, 'id'));

$origP = null;
$origW = null;
$rec = ['product_id' => '', 'warehouse_id' => '', 'amount_in_stock' => '', 'reorder_point' => '', 'max_in_stock' => ''];

// Zapis (dodanie / edycja)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $product_id   = (int)($_POST['product_id'] ?? 0);
    $warehouse_id = (int)($_POST['warehouse_id'] ?? 0);
    $amount  = trim($_POST['amount_in_stock'] ?? '');
    $reorder = trim($_POST['reorder_point'] ?? '');
    $max     = trim($_POST['max_in_stock'] ?? '');
    $origP = ($_POST['orig_product_id'] ?? '') !== '' ? (int)$_POST['orig_product_id'] : null;
    $origW = ($_POST['orig_warehouse_id'] ?? '') !== '' ? (int)$_POST['orig_warehouse_id'] : null;

    $rec = ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'amount_in_stock' => $amount,
            'reorder_point' => $reorder, 'max_in_stock' => $max];

    if (!in_array($product_id, $productIds, true)) {
        $err = 'Wybierz produkt.';
    } elseif (!in_array($warehouse_id, $warehouseIds, true)) {
        $err = 'Wybierz magazyn.';
    } elseif (!ctype_digit($amount)) {
        $err = 'Ilość musi być liczbą całkowitą nieujemną.';
    } elseif (($reorder !== '' && !ctype_digit($reorder)) || ($max !== '' && !ctype_digit($max))) {
        $err = 'Punkt zamówienia i maksimum muszą być liczbami całkowitymi nieujemnymi.';
    }

    if ($err === '') {
        $reorderV = $reorder === '' ? null : (int)$reorder;
        $maxV     = $max === '' ? null : (int)$max;
        try {
            if ($origP !== null && $origW !== null) {
                $s = $pdo->prepare('UPDATE inventory SET product_id=?, warehouse_id=?, amount_in_stock=?, reorder_point=?, max_in_stock=? WHERE product_id=? AND warehouse_id=?');
                $s->execute([$product_id, $warehouse_id, (int)$amount, $reorderV, $maxV, $origP, $origW]);
            } else {
                $s = $pdo->prepare('INSERT INTO inventory (product_id, warehouse_id, amount_in_stock, reorder_point, max_in_stock) VALUES (?,?,?,?,?)');
                $s->execute([$product_id, $warehouse_id, (int)$amount, $reorderV, $maxV]);
            }
            header('Location: inwentaz.php');
            exit;
        } catch (PDOException $e) {
            $err = ($e->getCode() === '23000')
                ? 'Ten produkt ma już wpis w tym magazynie.'
                : 'Nie udało się zapisać wpisu.';
        }
    }
}

// Usuwanie (POST)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $s = $pdo->prepare('DELETE FROM inventory WHERE product_id = ? AND warehouse_id = ?');
    $s->execute([(int)($_POST['product_id'] ?? 0), (int)($_POST['warehouse_id'] ?? 0)]);
    header('Location: inwentaz.php');
    exit;
}

// Ładowanie rekordu do edycji
$act = $_GET['action'] ?? '';
if ($canManage && $act === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['product_id'], $_GET['warehouse_id'])) {
    $s = $pdo->prepare('SELECT * FROM inventory WHERE product_id = ? AND warehouse_id = ?');
    $s->execute([(int)$_GET['product_id'], (int)$_GET['warehouse_id']]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $rec = $row;
        $origP = (int)$row['product_id'];
        $origW = (int)$row['warehouse_id'];
    }
}

$rows = $pdo->query(
    "SELECT i.product_id, i.warehouse_id, p.name AS product_name,
            CONCAT(w.city, ' ', w.address) AS warehouse_name,
            i.amount_in_stock, i.reorder_point, i.max_in_stock
     FROM inventory i
     LEFT JOIN product p ON i.product_id = p.id
     LEFT JOIN warehouse w ON i.warehouse_id = w.id
     ORDER BY p.name, w.city, w.address"
)->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Stan Magazynowy (Tabela: INVENTORY)</h2>
    <?php if ($canManage): ?>
        <a class="btn btn-sm btn-success" href="inwentaz.php?action=add">Dodaj wpis</a>
    <?php endif; ?>
</div>
<p class="lead">Aktualny stan zapasów. Wiersze na żółto: stan poniżej lub równy punktowi zamówienia.</p>

<?php if ($err !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if ($canManage && in_array($act, ['add', 'edit'], true)): ?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <?php if ($origP !== null && $origW !== null): ?>
            <input type="hidden" name="orig_product_id" value="<?php echo (int)$origP; ?>">
            <input type="hidden" name="orig_warehouse_id" value="<?php echo (int)$origW; ?>">
        <?php endif; ?>
        <div class="row g-2">
            <div class="col-md-3">
                <select name="product_id" class="form-select" required>
                    <option value="">-- Produkt --</option>
                    <?php foreach ($products as $prod): ?>
                        <option value="<?php echo (int)$prod['id']; ?>" <?php echo ((int)$prod['id'] === (int)$rec['product_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prod['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- Magazyn --</option>
                    <?php foreach ($warehouses as $ware): ?>
                        <option value="<?php echo (int)$ware['id']; ?>" <?php echo ((int)$ware['id'] === (int)$rec['warehouse_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ware['city'] . ' ' . $ware['address']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input class="form-control" name="amount_in_stock" placeholder="Ilość" required value="<?php echo htmlspecialchars($rec['amount_in_stock']); ?>"></div>
            <div class="col-md-2"><input class="form-control" name="reorder_point" placeholder="Punkt zamówienia" value="<?php echo htmlspecialchars((string)$rec['reorder_point']); ?>"></div>
            <div class="col-md-2"><input class="form-control" name="max_in_stock" placeholder="Maksimum" value="<?php echo htmlspecialchars((string)$rec['max_in_stock']); ?>"></div>
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
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <th>Produkt ID</th>
                <th>Magazyn ID</th>
                <th>Produkt</th>
                <th>Magazyn</th>
                <th>Ilość</th>
                <th>Punkt zamówienia</th>
                <th>Maksimum</th>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row):
                $low = $row['reorder_point'] !== null && (int)$row['amount_in_stock'] <= (int)$row['reorder_point'];
            ?>
                <tr<?php echo $low ? ' class="table-warning"' : ''; ?>>
                    <td><?php echo (int)$row['product_id']; ?></td>
                    <td><?php echo (int)$row['warehouse_id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['warehouse_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['amount_in_stock']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['reorder_point']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['max_in_stock']); ?></td>
                    <?php if ($canManage): ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="inwentaz.php?action=edit&product_id=<?php echo urlencode($row['product_id']); ?>&warehouse_id=<?php echo urlencode($row['warehouse_id']); ?>">Edytuj</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Na pewno usunąć wpis?');">
        <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo (int)$row['product_id']; ?>">
                                <input type="hidden" name="warehouse_id" value="<?php echo (int)$row['warehouse_id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Usuń</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
