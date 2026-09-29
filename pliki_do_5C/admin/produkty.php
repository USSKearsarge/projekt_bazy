<?php
// produkty.php | Tabela: product (id, name, short_desc, suggested_price) | dostęp: menu 'magazyn' lub 'warehouse'
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
$rec = ['id' => '', 'name' => '', 'short_desc' => '', 'suggested_price' => ''];

// Zapis (dodanie / edycja)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['short_desc'] ?? '');
    $price = str_replace(',', '.', trim($_POST['suggested_price'] ?? ''));
    $rec = ['id' => $id ?: '', 'name' => $name, 'short_desc' => $desc, 'suggested_price' => $price];

    if ($name === '') {
        $err = 'Podaj nazwę produktu.';
    } elseif (mb_strlen($name) > 50) {
        $err = 'Nazwa może mieć maksymalnie 50 znaków.';
    } elseif (mb_strlen($desc) > 255) {
        $err = 'Opis może mieć maksymalnie 255 znaków.';
    } elseif ($price !== '' && (!is_numeric($price) || (float)$price < 0)) {
        $err = 'Cena musi być liczbą nieujemną.';
    }

    if ($err === '') {
        $priceV = $price === '' ? null : $price;
        try {
            if ($id > 0) {
                $pdo->prepare('UPDATE product SET name=?, short_desc=?, suggested_price=? WHERE id=?')->execute([$name, $desc, $priceV, $id]);
            } else {
                $pdo->prepare('INSERT INTO product (name, short_desc, suggested_price) VALUES (?,?,?)')->execute([$name, $desc, $priceV]);
            }
            header('Location: produkty.php');
            exit;
        } catch (PDOException $e) {
            $err = 'Nie udało się zapisać produktu.';
        }
    }
}

// Usuwanie (POST)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    try {
        $pdo->prepare('DELETE FROM product WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: produkty.php');
        exit;
    } catch (PDOException $e) {
        // inventory, item i prices mają klucz obcy do product
        $err = 'Nie można usunąć produktu – jest użyty w magazynie, zamówieniach lub cennikach.';
    }
}

$act = $_GET['action'] ?? '';
if ($canManage && $act === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['id'])) {
    $s = $pdo->prepare('SELECT * FROM product WHERE id = ?');
    $s->execute([(int)$_GET['id']]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) $rec = $row;
}

$rows = $pdo->query('SELECT id, name, short_desc, suggested_price FROM product ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Lista Produktów (Tabela: PRODUCT)</h2>
        <p class="lead">Lista produktów dostępnych w sklepie.</p>
    </div>
    <?php if ($canManage): ?>
        <a class="btn btn-primary" href="produkty.php?action=add">Dodaj produkt</a>
    <?php endif; ?>
</div>

<?php if ($err !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if ($canManage && in_array($act, ['add', 'edit'], true)): ?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$rec['id']); ?>">
        <div class="row g-2">
            <div class="col-md-4"><input class="form-control" name="name" maxlength="50" placeholder="Nazwa *" required value="<?php echo htmlspecialchars((string)$rec['name']); ?>"></div>
            <div class="col-md-5"><input class="form-control" name="short_desc" maxlength="255" placeholder="Opis" value="<?php echo htmlspecialchars((string)$rec['short_desc']); ?>"></div>
            <div class="col-md-3"><input class="form-control" name="suggested_price" placeholder="Sugerowana cena" value="<?php echo htmlspecialchars((string)$rec['suggested_price']); ?>"></div>
            <div class="col-md-12 mt-2">
                <button class="btn btn-success">Zapisz</button>
                <a class="btn btn-secondary" href="produkty.php">Anuluj</a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Nazwa</th><th>Opis</th><th>Sugerowana cena</th>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo (int)$r['id']; ?></td>
                    <td><?php echo htmlspecialchars($r['name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$r['short_desc']); ?></td>
                    <td><?php echo htmlspecialchars((string)$r['suggested_price']); ?></td>
                    <?php if ($canManage): ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="produkty.php?action=edit&id=<?php echo urlencode($r['id']); ?>">Edytuj</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Usunąć produkt?');">
        <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
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
