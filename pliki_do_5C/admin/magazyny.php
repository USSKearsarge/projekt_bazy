<?php
 
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

// CRUD for magazyny: roles 1 (admin), 2 (HR), 3 (kierownik) can manage; role 4 (magazynier) is read-only
$canManage = in_array($rola_id, [1, 2, 3]);

// Handle save
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    
    // Nowe kolumny z bazy_testowa.sql
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $region_id = $_POST['region_id'] !== '' ? (int)$_POST['region_id'] : null;
    $manager_id = $_POST['manager_id'] !== '' ? (int)$_POST['manager_id'] : null;

    if ($id) {
        $stmt = $pdo->prepare('UPDATE warehouse SET address=?, city=?, state=?, country=?, zip_code=?, phone=?, region_id=?, manager_id=? WHERE id=?');
        $stmt->execute([$address, $city, $state, $country, $zip_code, $phone, $region_id, $manager_id, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO warehouse (address, city, state, country, zip_code, phone, region_id, manager_id) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$address, $city, $state, $country, $zip_code, $phone, $region_id, $manager_id]);
    }
    header('Location: magazyny.php'); exit;
}

// Handle delete
if ($canManage && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    $d = $pdo->prepare('DELETE FROM warehouse WHERE id = ?');
    $d->execute([$did]);
    header('Location: magazyny.php'); exit;
}

// Pobieranie pełnych danych magazynu z nazwami regionów i nazwiskami
$sql = "SELECT 
            w.id AS 'ID', 
            r.name AS 'Region', 
            w.address AS 'Adres', 
            w.city AS 'Miasto', 
            w.state AS 'Stan/Województwo', 
            w.country AS 'Kraj', 
            w.zip_code AS 'Kod pocztowy', 
            w.phone AS 'Telefon', 
            CONCAT(e.first_name, ' ', e.last_name) AS 'Kierownik'
        FROM warehouse w
        LEFT JOIN region r ON w.region_id = r.id
        LEFT JOIN emp e ON w.manager_id = e.id
        ORDER BY w.id";
$stmt = $pdo->query($sql);
// Zabezpieczenie przed zdublowaniem kolumn
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Słowniki do rozwijanych list
$regions = $pdo->query('SELECT id, name FROM region ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$managers = $pdo->query('SELECT id, CONCAT(first_name, " ", last_name) AS name FROM emp ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Zarządzanie Magazynami (Tabela: WAREHOUSE)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="magazyny.php?action=add">Dodaj magazyn</a><?php endif; ?>
</div>
<p class="lead">Lista magazynów i powiązanych lokalizacji.</p>

<?php
$act = $_GET['action'] ?? '';
$edit = null;
if ($canManage && $act === 'edit' && isset($_GET['id'])) {
    $eid = (int)$_GET['id']; 
    $s = $pdo->prepare('SELECT * FROM warehouse WHERE id = ?'); 
    $s->execute([$eid]); 
    $edit = $s->fetch(PDO::FETCH_ASSOC);
}
if ($canManage && in_array($act, ['add','edit'])):
    $idVal = $edit['id'] ?? '';
    $addressVal = $edit['address'] ?? '';
    $cityVal = $edit['city'] ?? '';
    $stateVal = $edit['state'] ?? '';
    $countryVal = $edit['country'] ?? '';
    $zipVal = $edit['zip_code'] ?? '';
    $phoneVal = $edit['phone'] ?? '';
    $regionVal = $edit['region_id'] ?? '';
    $mgrVal = $edit['manager_id'] ?? '';
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($idVal); ?>">
        <div class="row g-2">
            <div class="col-md-3"><input class="form-control" name="address" placeholder="Adres" value="<?php echo htmlspecialchars($addressVal); ?>"></div>
            <div class="col-md-3"><input class="form-control" name="city" placeholder="Miasto" value="<?php echo htmlspecialchars($cityVal); ?>"></div>
            <div class="col-md-3"><input class="form-control" name="state" placeholder="Stan/Województwo" value="<?php echo htmlspecialchars($stateVal); ?>"></div>
            <div class="col-md-3"><input class="form-control" name="country" placeholder="Kraj" value="<?php echo htmlspecialchars($countryVal); ?>"></div>
            
            <div class="col-md-3 mt-2"><input class="form-control" name="zip_code" placeholder="Kod pocztowy" value="<?php echo htmlspecialchars($zipVal); ?>"></div>
            <div class="col-md-3 mt-2"><input class="form-control" name="phone" placeholder="Telefon" value="<?php echo htmlspecialchars($phoneVal); ?>"></div>
            
            <div class="col-md-3 mt-2">
                <select name="region_id" class="form-control">
                    <option value="">-- Wybierz region --</option>
                    <?php foreach($regions as $rg): ?>
                        <option value="<?php echo $rg['id']; ?>" <?php if($rg['id']==$regionVal) echo 'selected'; ?>><?php echo htmlspecialchars($rg['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <select name="manager_id" class="form-control">
                    <option value="">-- Wybierz kierownika --</option>
                    <?php foreach($managers as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php if($m['id']==$mgrVal) echo 'selected'; ?>><?php echo htmlspecialchars($m['name']); ?></option>
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
        <thead class="table-dark"><tr>
            <?php foreach (array_keys($rows[0]) as $col): ?><th><?php echo htmlspecialchars($col); ?></th><?php endforeach; ?>
            <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
        </tr></thead>
        <tbody>
            <?php foreach ($rows as $r): ?><tr>
                <?php foreach ($r as $v): ?><td><?php echo htmlspecialchars((string)$v); ?></td><?php endforeach; ?>
                <?php if ($canManage): ?><td>
                    <a class="btn btn-sm btn-outline-primary" href="magazyny.php?action=edit&id=<?php echo urlencode($r['ID']); ?>">Edytuj</a>
                    <a class="btn btn-sm btn-outline-danger" href="magazyny.php?action=delete&id=<?php echo urlencode($r['ID']); ?>" onclick="return confirm('Usuń magazyn?');">Usuń</a>
                </td><?php endif; ?>
            </tr><?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>