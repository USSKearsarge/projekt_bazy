<?php
 
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
if (!in_array($rola_id, [1, 2, 3])) {
    header('Location: index.php');
    exit;
}

// Edytował to Jakub Staniec


// CRUD for cenniki: roles 1 (admin) and 3 (kierownik)
$canManage = in_array($rola_id, [1,3]);
 
$stmt = $pdo->query(
    "SELECT prices.*, product.name AS produkt
     FROM prices
     LEFT JOIN product  ON prices.product_id = product.id
     ORDER BY product_id"
);

$rows = $stmt->fetchAll();

// fetch products for dropdown
$products = $pdo->query('SELECT id,name FROM product ORDER BY id')->fetchAll();

// Handle save
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $product_id = (int)($_POST['product_id'] ?? 0);
    $price_name = trim($_POST['netto'] ?? '');
    $price = $_POST['price'] ?? '0';
    $valid_from = $_POST['valid_from'] ?: null;
    $valid_to = $_POST['valid_to'] ?: null;

    if ($id) {
        $s = $pdo->prepare('UPDATE prices SET product_id=?, netto=?, price=?, valid_from=?, valid_to=? WHERE id=?');
        $s->execute([$product_id,$price_name,$price,$valid_from,$valid_to,$id]);
    } else {
        $s = $pdo->prepare('INSERT INTO prices (product_id,netto,price,valid_from,valid_to) VALUES (?,?,?,?,?)');
        $s->execute([$product_id,$price_name,$price,$valid_from,$valid_to]);
    }
    header('Location: cenniki.php'); exit;
}

// Handle delete
if ($canManage && isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    $d = $pdo->prepare('DELETE FROM prices WHERE id = ?');
    $d->execute([$did]);
    header('Location: cenniki.php'); exit;
}

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Cenników (Tabela: CENNIKI)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="cenniki.php?action=add">Dodaj wpis cennika</a><?php endif; ?>
</div>
<p class="lead">Lista cenników i stawek.</p>

<?php
$act = $_GET['action'] ?? '';
$edit = null;
if ($canManage && $act === 'edit' && isset($_GET['id'])) {
    $eid = (int)$_GET['id']; $s = $pdo->prepare('SELECT * FROM prices WHERE id = ?'); $s->execute([$eid]); $edit = $s->fetch();
}
if ($canManage && in_array($act, ['add','edit'])):
    $idVal = $edit['id'] ?? '';
    $produktVal = $edit['product_id'] ?? '';
    $priceNameVal = $edit['price_name'] ?? '';
    $priceVal = $edit['price'] ?? '';
    $fromVal = $edit['valid_from'] ?? '';
    $toVal = $edit['valid_to'] ?? '';
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($idVal); ?>">
        <div class="row g-2">
            <div class="col-md-4">
                <select name="product_id" class="form-control">
                    <option value="">-- Wybierz produkt --</option>
                    <?php foreach($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php if($p['id']==$productVal) echo 'selected'; ?>><?php echo htmlspecialchars($p['nazwa']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><input class="form-control" name="price_name" placeholder="Nazwa cennika" value="<?php echo htmlspecialchars($priceNameVal); ?>"></div>
            <div class="col-md-2"><input class="form-control" name="price" placeholder="Price" value="<?php echo htmlspecialchars($priceVal); ?>"></div>
            <div class="col-md-3"><input type="date" class="form-control" name="valid_from" value="<?php echo htmlspecialchars($fromVal); ?>"></div>
            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="valid_to" value="<?php echo htmlspecialchars($toVal); ?>"></div>
            <div class="col-md-12 mt-2"><button class="btn btn-success">Zapisz</button> <a class="btn btn-secondary" href="cenniki.php">Anuluj</a></div>
        </div>
    </form>
<?php endif; ?>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <?php foreach (array_keys($rows[0]) as $col): ?>
                    <th><?php echo htmlspecialchars($col); ?></th>
                <?php endforeach; ?>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <?php foreach ($r as $v): ?>
                        <td><?php echo htmlspecialchars((string)$v); ?></td>
                    <?php endforeach; ?>
                    <?php if ($canManage): ?>
                        <td>
                            <td>
    <a class="btn btn-sm btn-outline-primary"
       href="cenniki.php?action=edit&id=<?php echo urlencode($r['product_id']); ?>">
        Edytuj
    </a>

    <a class="btn btn-sm btn-outline-danger"
       href="cenniki.php?action=delete&id=<?php echo urlencode($r['product_id']); ?>"
       onclick="return confirm('Usuń wpis cennika?');">
        Usuń
    </a>
</td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
