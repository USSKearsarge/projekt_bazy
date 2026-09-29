<?php
// cenniki.php | Tabela: prices (product_id, date, info, netto, vat) | klucz główny: product_id + date
require '../cfg.php';
require 'csrf.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

$perms = $_SESSION['perms'] ?? [];
if (!isset($perms['hr']) && !isset($perms['magazyn'])) {
    header('Location: index.php');
    exit;
}
$canManage = ($perms['hr'] ?? '') === 'W' || ($perms['magazyn'] ?? '') === 'W';

// Ochrona CSRF dla wszystkich żądań POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$err = '';
$products = $pdo->query('SELECT id, name FROM product ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$productIds = array_map('intval', array_column($products, 'id'));

// "2026-05-01T12:00" -> "2026-05-01 12:00:00"; zwraca null gdy pusto
function norm_dt($v) {
    $v = str_replace('T', ' ', trim((string)$v));
    if ($v === '') return null;
    if (strlen($v) === 16) $v .= ':00';
    return $v;
}
function valid_dt($v) {
    $d = DateTime::createFromFormat('Y-m-d H:i:s', (string)$v);
    return $d && $d->format('Y-m-d H:i:s') === $v;
}

$origP = null;
$origD = null;
$rec = ['product_id' => '', 'date' => '', 'info' => '', 'netto' => '', 'vat' => '23'];

// Zapis (dodanie / edycja)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $date  = norm_dt($_POST['date'] ?? '');
    $info  = trim($_POST['info'] ?? '');
    $netto = str_replace(',', '.', trim($_POST['netto'] ?? ''));
    $vat   = str_replace(',', '.', trim($_POST['vat'] ?? ''));
    $origP = ($_POST['orig_product_id'] ?? '') !== '' ? (int)$_POST['orig_product_id'] : null;
    $origD = norm_dt($_POST['orig_date'] ?? '');

    $rec = ['product_id' => $product_id, 'date' => $date ?? '', 'info' => $info, 'netto' => $netto, 'vat' => $vat];

    if (!in_array($product_id, $productIds, true)) {
        $err = 'Wybierz produkt.';
    } elseif ($date === null || !valid_dt($date)) {
        $err = 'Podaj poprawną datę i godzinę.';
    } elseif (mb_strlen($info) > 25) {
        $err = 'Opis może mieć maksymalnie 25 znaków.';
    } elseif (!is_numeric($netto) || (float)$netto < 0) {
        $err = 'Cena netto musi być liczbą nieujemną.';
    } elseif (!is_numeric($vat) || (float)$vat < 0 || (float)$vat > 100) {
        $err = 'VAT musi być liczbą od 0 do 100.';
    }

    if ($err === '') {
        try {
            if ($origP !== null && $origD !== null) {
                $s = $pdo->prepare('UPDATE prices SET product_id=?, date=?, info=?, netto=?, vat=? WHERE product_id=? AND date=?');
                $s->execute([$product_id, $date, $info, $netto, $vat, $origP, $origD]);
            } else {
                $s = $pdo->prepare('INSERT INTO prices (product_id, date, info, netto, vat) VALUES (?,?,?,?,?)');
                $s->execute([$product_id, $date, $info, $netto, $vat]);
            }
            header('Location: cenniki.php');
            exit;
        } catch (PDOException $e) {
            $err = ($e->getCode() === '23000')
                ? 'Cena tego produktu z taką datą już istnieje.'
                : 'Nie udało się zapisać ceny.';
        }
    }
}

// Usuwanie (POST)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $dp = (int)($_POST['product_id'] ?? 0);
    $dd = norm_dt($_POST['date'] ?? '');
    if ($dp > 0 && $dd !== null) {
        $s = $pdo->prepare('DELETE FROM prices WHERE product_id = ? AND date = ?');
        $s->execute([$dp, $dd]);
    }
    header('Location: cenniki.php');
    exit;
}

// Ładowanie rekordu do edycji
$act = $_GET['action'] ?? '';
if ($canManage && $act === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['product_id'], $_GET['date'])) {
    $s = $pdo->prepare('SELECT * FROM prices WHERE product_id = ? AND date = ?');
    $s->execute([(int)$_GET['product_id'], norm_dt($_GET['date'])]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $rec = $row;
        $origP = (int)$row['product_id'];
        $origD = $row['date'];
    }
}

$rows = $pdo->query(
    "SELECT p.product_id, pr.name AS product_name, p.date, p.info, p.netto, p.vat,
            ROUND(p.netto * (1 + p.vat / 100), 2) AS brutto
     FROM prices p
     LEFT JOIN product pr ON p.product_id = pr.id
     ORDER BY p.product_id, p.date DESC"
)->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Cenników (Tabela: PRICES)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="cenniki.php?action=add">Dodaj cenę</a><?php endif; ?>
</div>
<p class="lead">Historia cen produktów.</p>

<?php if ($err !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if ($canManage && in_array($act, ['add', 'edit'], true)):
    $dateVal = $rec['date'] !== '' ? date('Y-m-d\TH:i:s', strtotime($rec['date'])) : '';
?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <?php if ($origP !== null && $origD !== null): ?>
            <input type="hidden" name="orig_product_id" value="<?php echo (int)$origP; ?>">
            <input type="hidden" name="orig_date" value="<?php echo htmlspecialchars($origD); ?>">
        <?php endif; ?>
        <div class="row g-2">
            <div class="col-md-4">
                <select name="product_id" class="form-control" required>
                    <option value="">-- Wybierz produkt --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?php echo (int)$p['id']; ?>" <?php echo ((int)$p['id'] === (int)$rec['product_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="datetime-local" step="1" class="form-control" name="date" value="<?php echo htmlspecialchars($dateVal); ?>" required>
            </div>
            <div class="col-md-2"><input class="form-control" name="info" maxlength="25" placeholder="Opis" value="<?php echo htmlspecialchars($rec['info']); ?>"></div>
            <div class="col-md-2"><input class="form-control" name="netto" placeholder="Netto" required value="<?php echo htmlspecialchars($rec['netto']); ?>"></div>
            <div class="col-md-1"><input class="form-control" name="vat" placeholder="VAT %" required value="<?php echo htmlspecialchars($rec['vat']); ?>"></div>
            <div class="col-md-12 mt-2">
                <button class="btn btn-success">Zapisz</button>
                <a class="btn btn-secondary" href="cenniki.php">Anuluj</a>
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
                <th>Produkt ID</th>
                <th>Produkt</th>
                <th>Data</th>
                <th>Opis</th>
                <th>Netto</th>
                <th>VAT %</th>
                <th>Brutto</th>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo (int)$r['product_id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$r['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['date']); ?></td>
                    <td><?php echo htmlspecialchars((string)$r['info']); ?></td>
                    <td><?php echo htmlspecialchars($r['netto']); ?></td>
                    <td><?php echo htmlspecialchars($r['vat']); ?></td>
                    <td><?php echo htmlspecialchars($r['brutto']); ?></td>
                    <?php if ($canManage): ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary"
                               href="cenniki.php?action=edit&product_id=<?php echo urlencode($r['product_id']); ?>&date=<?php echo urlencode($r['date']); ?>">Edytuj</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Usunąć wpis cennika?');">
        <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo (int)$r['product_id']; ?>">
                                <input type="hidden" name="date" value="<?php echo htmlspecialchars($r['date']); ?>">
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
