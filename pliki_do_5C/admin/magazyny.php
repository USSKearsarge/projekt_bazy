<?php
// magazyny.php | Tabela: warehouse | dostęp: menu 'magazyn' lub 'warehouse'
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
$regions  = $pdo->query('SELECT id, name FROM region ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$managers = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM emp ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
$regionIds  = array_map('intval', array_column($regions, 'id'));
$managerIds = array_map('intval', array_column($managers, 'id'));

$rec = ['id' => '', 'address' => '', 'city' => '', 'state' => '', 'country' => '', 'zip_code' => '', 'phone' => '', 'region_id' => '', 'manager_id' => ''];

// Zapis (dodanie / edycja)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $rec = [
        'id'         => $id ?: '',
        'address'    => trim($_POST['address'] ?? ''),
        'city'       => trim($_POST['city'] ?? ''),
        'state'      => trim($_POST['state'] ?? ''),
        'country'    => trim($_POST['country'] ?? ''),
        'zip_code'   => trim($_POST['zip_code'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'region_id'  => (int)($_POST['region_id'] ?? 0),
        'manager_id' => (int)($_POST['manager_id'] ?? 0),
    ];

    // city, country, region_id i manager_id są NOT NULL w tabeli warehouse
    if ($rec['city'] === '' || $rec['country'] === '') {
        $err = 'Miasto i kraj są wymagane.';
    } elseif (!in_array($rec['region_id'], $regionIds, true)) {
        $err = 'Wybierz region.';
    } elseif (!in_array($rec['manager_id'], $managerIds, true)) {
        $err = 'Wybierz kierownika.';
    } elseif (mb_strlen($rec['city']) > 30 || mb_strlen($rec['country']) > 30 || mb_strlen($rec['state']) > 20
           || mb_strlen($rec['zip_code']) > 75 || mb_strlen($rec['phone']) > 25) {
        $err = 'Któreś z pól jest za długie (miasto/kraj 30, stan 20, telefon 25 znaków).';
    }

    if ($err === '') {
        $params = [
            $rec['address'] ?: null, $rec['city'], $rec['state'] ?: null, $rec['country'],
            $rec['zip_code'] ?: null, $rec['phone'] ?: null, $rec['region_id'], $rec['manager_id'],
        ];
        try {
            if ($id > 0) {
                $params[] = $id;
                $pdo->prepare('UPDATE warehouse SET address=?, city=?, state=?, country=?, zip_code=?, phone=?, region_id=?, manager_id=? WHERE id=?')->execute($params);
            } else {
                $pdo->prepare('INSERT INTO warehouse (address, city, state, country, zip_code, phone, region_id, manager_id) VALUES (?,?,?,?,?,?,?,?)')->execute($params);
            }
            header('Location: magazyny.php');
            exit;
        } catch (PDOException $e) {
            $err = 'Nie udało się zapisać magazynu.';
        }
    }
}

// Usuwanie (POST)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    try {
        $pdo->prepare('DELETE FROM warehouse WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: magazyny.php');
        exit;
    } catch (PDOException $e) {
        // inventory.warehouse_id ma klucz obcy do warehouse
        $err = 'Nie można usunąć magazynu – ma wpisy w stanie magazynowym.';
    }
}

$act = $_GET['action'] ?? '';
if ($canManage && $act === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['id'])) {
    $s = $pdo->prepare('SELECT * FROM warehouse WHERE id = ?');
    $s->execute([(int)$_GET['id']]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) $rec = $row;
}

$rows = $pdo->query(
    "SELECT w.id, r.name AS region, w.address, w.city, w.state, w.country, w.zip_code, w.phone,
            CONCAT(e.first_name, ' ', e.last_name) AS manager
     FROM warehouse w
     LEFT JOIN region r ON w.region_id = r.id
     LEFT JOIN emp e ON w.manager_id = e.id
     ORDER BY w.id"
)->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Zarządzanie Magazynami (Tabela: WAREHOUSE)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="magazyny.php?action=add">Dodaj magazyn</a><?php endif; ?>
</div>
<p class="lead">Lista magazynów i powiązanych lokalizacji.</p>

<?php if ($err !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if ($canManage && in_array($act, ['add', 'edit'], true)): ?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$rec['id']); ?>">
        <div class="row g-2">
            <div class="col-md-3"><input class="form-control" name="address" placeholder="Adres" value="<?php echo htmlspecialchars((string)$rec['address']); ?>"></div>
            <div class="col-md-3"><input class="form-control" name="city" maxlength="30" placeholder="Miasto *" required value="<?php echo htmlspecialchars((string)$rec['city']); ?>"></div>
            <div class="col-md-3"><input class="form-control" name="state" maxlength="20" placeholder="Stan/Województwo" value="<?php echo htmlspecialchars((string)$rec['state']); ?>"></div>
            <div class="col-md-3"><input class="form-control" name="country" maxlength="30" placeholder="Kraj *" required value="<?php echo htmlspecialchars((string)$rec['country']); ?>"></div>
            <div class="col-md-3 mt-2"><input class="form-control" name="zip_code" maxlength="75" placeholder="Kod pocztowy" value="<?php echo htmlspecialchars((string)$rec['zip_code']); ?>"></div>
            <div class="col-md-3 mt-2"><input class="form-control" name="phone" maxlength="25" placeholder="Telefon" value="<?php echo htmlspecialchars((string)$rec['phone']); ?>"></div>
            <div class="col-md-3 mt-2">
                <select name="region_id" class="form-control" required>
                    <option value="">-- Wybierz region --</option>
                    <?php foreach ($regions as $rg): ?>
                        <option value="<?php echo (int)$rg['id']; ?>" <?php echo ((int)$rg['id'] === (int)$rec['region_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($rg['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <select name="manager_id" class="form-control" required>
                    <option value="">-- Wybierz kierownika --</option>
                    <?php foreach ($managers as $m): ?>
                        <option value="<?php echo (int)$m['id']; ?>" <?php echo ((int)$m['id'] === (int)$rec['manager_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['name'] . ' (#' . $m['id'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 mt-3">
                <button class="btn btn-success">Zapisz</button>
                <a class="btn btn-secondary" href="magazyny.php">Anuluj</a>
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
                <th>ID</th><th>Region</th><th>Adres</th><th>Miasto</th><th>Stan/Województwo</th>
                <th>Kraj</th><th>Kod pocztowy</th><th>Telefon</th><th>Kierownik</th>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo (int)$r['id']; ?></td>
                    <?php foreach (['region', 'address', 'city', 'state', 'country', 'zip_code', 'phone', 'manager'] as $k): ?>
                        <td><?php echo htmlspecialchars((string)$r[$k]); ?></td>
                    <?php endforeach; ?>
                    <?php if ($canManage): ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="magazyny.php?action=edit&id=<?php echo urlencode($r['id']); ?>">Edytuj</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Usunąć magazyn?');">
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
