<?php
//Zrobił Mateusz Syska 
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
if (!in_array($rola_id, [1, 2, 3, 4])) {
    header('Location: index.php');
    exit;
}

// CRUD support: roles 1 (admin), 2 (HR), 3 (kierownik) can manage; role 4 (magazynier) is read-only
$stmt = $pdo->query("SELECT * FROM product ORDER BY id");
$rows = $stmt->fetchAll();

$canManage = in_array($rola_id, [1, 2, 3]);

// Handle POST save
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $name = trim($_POST['nazwa'] ?? '');
    $short_desc = trim($_POST['opis'] ?? '');
    $suggested_price = $_POST['cena'] ?? '0';
    $code = trim($_POST['sku'] ?? '');
    $category = trim($_POST['kategoria'] ?? '');
    $active = isset($_POST['aktywny']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare('UPDATE product SET name=?, short_desc=?, suggested_price=?, code=?, category=?, active=? WHERE id=?');
        $stmt->execute([$name, $short_desc, $suggested_price, $code, $category, $active, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO product (name, short_desc, suggested_price, code, category, active) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$name, $short_desc, $suggested_price, $code, $category, $active]);
    }
    header('Location: product.php');
    exit;
}

// Handle delete
if ($canManage && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM product WHERE id = ?');
    $stmt->execute([$delId]);
    header('Location: product.php');
    exit;
}

include 'szablony/naglowek.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Lista Produktów (Tabela: PRODUKTY)</h2>
        <p class="lead">Lista produktów dostępnych w sklepie.</p>
    </div>
    <?php if ($canManage): ?>
        <div>
            <a class="btn btn-primary" href="product.php?action=add">Dodaj produkt</a>
        </div>
    <?php endif; ?>
</div>

<?php
$act = $_GET['action'] ?? '';
$editData = null;
if ($canManage && $act === 'edit' && isset($_GET['id'])) {
    $eid = (int)$_GET['id'];
    $s = $pdo->prepare('SELECT * FROM product WHERE id = ?');
    $s->execute([$eid]);
    $editData = $s->fetch();
}
if ($canManage && in_array($act, ['add','edit'])):
    $idVal = $editData['id'] ?? '';
    $nameVal = $editData['nazwa'] ?? '';
    $short_descVal = $editData['opis'] ?? '';
    $suggested_priceVal = $editData['cena'] ?? '';
    $codeVal = $editData['sku'] ?? '';
    $codeVal = $editData['kategoria'] ?? '';
    $activeVal = isset($editData['aktywny']) && $editData['aktywny'] ? 'checked' : '';
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($idVal); ?>">
        <div class="row g-2">
            <div class="col-md-4">
                <input class="form-control" name="name" placeholder="Name" value="<?php echo htmlspecialchars($nameVal); ?>">
            </div>
            <div class="col-md-4">
                <input class="form-control" name="suggested_price" placeholder="suggested_price" value="<?php echo htmlspecialchars($suggested_priceVal); ?>">
            </div>
            <div class="col-md-4">
                <input class="form-control" name="code" placeholder="code" value="<?php echo htmlspecialchars($codeVal); ?>">
            </div>
            <div class="col-md-6 mt-2">
                <input class="form-control" name="code" placeholder="code" value="<?php echo htmlspecialchars($codeVal); ?>">
            </div>
            <div class="col-md-6 mt-2">
                <input class="form-control" name="short_desc" placeholder="short_desc" value="<?php echo htmlspecialchars($short_descVal); ?>">
            </div>
            <div class="col-md-12 mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="active" id="active" <?php echo $activeVal; ?>>
                    <label class="form-check-label" for="active">Aktywny</label>
                </div>
            </div>
            <div class="col-md-12 mt-2">
                <button class="btn btn-success">Zapisz</button>
                <a class="btn btn-secondary" href="product.php">Anuluj</a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <?php
    $labelMap = [
        'id' => 'ID',
        'name' => 'Name',
        'short_description' => 'Short_description',
        'suggested_price' => 'Suggested_price',
        'short_desc' => 'Short_desc',
        'Category' => 'Category',
        'active' => 'Active',
    ];
    $firstKeys = array_keys($rows[0]);
    ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <?php foreach ($firstKeys as $col): ?>
                    <th><?php echo htmlspecialchars($labelMap[$col] ?? ucfirst($col)); ?></th>
                <?php endforeach; ?>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <?php foreach ($firstKeys as $k): ?>
                        <td>
                            <?php
                            $v = $r[$k];
                            // Format aktywny as tak/nie
                            if ($k === 'active') {
                                echo ($v == 1) ? 'tak' : 'nie';
                            } else {
                                echo htmlspecialchars((string)$v);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                    <?php if ($canManage): ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="produkty.php?action=edit&id=<?php echo urlencode($r['id']); ?>">Edytuj</a>
                            <a class="btn btn-sm btn-outline-danger" href="produkty.php?action=delete&id=<?php echo urlencode($r['id']); ?>" onclick="return confirm('Usuń produkt?');">Usuń</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
