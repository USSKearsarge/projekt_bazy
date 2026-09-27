<?php 
require '../cfg.php'; 

if(!isset($_SESSION['zalogowany'])){ 
    header('Location: logowanie.php'); 
    exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
 
// Allow ADMIN(1), HR(2) and KIEROWNIK(3) to access this page (with different permissions)
if (!in_array($rola_id, [1, 2, 3])) {
    header('Location: index.php'); // Przekieruj, jeśli rola nie ma dostępu
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = $_POST['id'] ?? null;
    $imie = $_POST['imie'] ?? '';
    $nazwisko = $_POST['nazwisko'] ?? '';
    $email = $_POST['email'] ?? '';
    $rola = $_POST['rola_id'] ?? null;
    $dzial = $_POST['dzial_id'] ?? null;
    $stanowisko = $_POST['stanowisko_id'] ?? null;
    $region = $_POST['region_id'] ?? null;
    $telefon = $_POST['telefon'] ?? '';
    $adres = $_POST['adres'] ?? '';
    $active = isset($_POST['active']) ? 1 : 0;
    $data_zatrudnienia = $_POST['data_zatrudnienia'] ?? null;
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // permission checks before save
    $currentRole = (int)$rola_id;
    $targetRole = (int)$rola;
    if ($currentRole === 3) {
        // Kierownik: cannot edit or manage ADMIN(1) or HR(2); can only manage MAGAZYN(4)
        if ($id) {
            $tstmt = $pdo->prepare('SELECT rola_id FROM pracownicy WHERE id = ?');
            $tstmt->execute([$id]);
            $existingRole = (int)$tstmt->fetchColumn();
            if (in_array($existingRole, [1,2])) {
                $err = 'Brak uprawnień do edycji tego pracownika.';
            }
        }
        if (in_array($targetRole, [1,2])) {
            $err = 'Brak uprawnień do nadawania tej roli.';
        }
    } elseif ($currentRole === 2) {
        // HR: cannot create or edit ADMIN(1)
        if ($id) {
            $tstmt = $pdo->prepare('SELECT rola_id FROM pracownicy WHERE id = ?');
            $tstmt->execute([$id]);
            $existingRole = (int)$tstmt->fetchColumn();
            if ($existingRole === 1) {
                $err = 'Brak uprawnień do edycji administratora.';
            }
        }
        if ($targetRole === 1) {
            $err = 'Brak uprawnień do nadawania roli administratora.';
        }
    }

    if ($err === '') {
        // If a password was provided, ensure confirmation matches
        if ($password !== '' && $password !== $password_confirm) {
            $err = 'Hasła nie są zgodne.';
        }
        
        if ($id) {
            $parts = ["imie = ?","nazwisko = ?","email = ?","rola_id = ?","dzial_id = ?","stanowisko_id = ?","region_id = ?","telefon = ?","adres = ?","aktywny = ?","data_zatrudnienia = ?"];
            $params = [$imie,$nazwisko,$email,$rola,$dzial,$stanowisko,$region,$telefon,$adres,$active,$data_zatrudnienia,$id];
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $parts[] = "password_hash = ?";
                array_splice($params, count($params)-1, 0, [$hash]);
            }
            $sql = "UPDATE pracownicy SET " . implode(', ', $parts) . " WHERE id = ?";
            $upd = $pdo->prepare($sql);
            $upd->execute($params);
        } else {
            $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '';
            $ins = $pdo->prepare('INSERT INTO pracownicy (imie,nazwisko,email,rola_id,dzial_id,stanowisko_id,region_id,telefon,adres,aktywny,data_zatrudnienia,password_hash) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $ins->execute([$imie,$nazwisko,$email,$rola,$dzial,$stanowisko,$region,$telefon,$adres,$active,$data_zatrudnienia,$hash]);
        }
        header('Location: pracownicy.php');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    // permission check for delete
    $allowedToDelete = false;
    if ($rola_id === 1) $allowedToDelete = true; // admin
    elseif ($rola_id === 2) {
        // HR can't delete admin
        $tstmt = $pdo->prepare('SELECT rola_id FROM pracownicy WHERE id = ?');
        $tstmt->execute([$did]); $er = (int)$tstmt->fetchColumn(); if ($er !== 1) $allowedToDelete = true;
    } elseif ($rola_id === 3) {
        // kierownik can delete only magazynier (role 4)
        $tstmt = $pdo->prepare('SELECT rola_id FROM pracownicy WHERE id = ?');
        $tstmt->execute([$did]); $er = (int)$tstmt->fetchColumn(); if ($er === 4) $allowedToDelete = true;
    }
    if ($allowedToDelete) {
        $del = $pdo->prepare('DELETE FROM pracownicy WHERE id = ?');
        $del->execute([$did]);
        header('Location: pracownicy.php');
        exit;
    } else {
        $err = 'Brak uprawnień do usunięcia tego pracownika.';
    }
}

 
$stmt = $pdo->query("SELECT e.*, r.nazwa AS role_name FROM pracownicy e JOIN role r ON e.rola_id = r.id ORDER BY e.id");
$pracownicy = $stmt->fetchAll();

 
$roles = $pdo->query('SELECT id, nazwa FROM role ORDER BY id')->fetchAll();
$depts = $pdo->query('SELECT id, nazwa FROM dept ORDER BY id')->fetchAll();
$titles = $pdo->query('SELECT id, nazwa FROM title ORDER BY id')->fetchAll();
$regions = $pdo->query('SELECT id, nazwa FROM region ORDER BY id')->fetchAll();

include 'szablony/naglowek.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Pracowników (Tabela: PRACOWNICY)</h2>
    <div>
        <?php if (in_array($rola_id, [1,2])): ?>
            <a href="pracownicy.php?action=add" class="btn btn-success btn-sm">Dodaj pracownika</a>
        <?php endif; ?>
    </div>
</div>
<p class="lead">Zarządzanie kontami pracowników oraz ich danymi kadrowymi.</p>

<?php if (!empty(
    $err
)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if (isset($_GET['action']) && in_array($_GET['action'], ['add','edit'])):
    $act = $_GET['action'];
    $rec = ['id'=>'','imie'=>'','nazwisko'=>'','email'=>'','rola_id'=>'','dzial_id'=>'','stanowisko_id'=>'','region_id'=>'','telefon'=>'','adres'=>'','aktywny'=>1,'data_zatrudnienia'=>''];
    if ($act === 'edit' && isset($_GET['id'])) {
        $eid = (int)$_GET['id'];
        $s = $pdo->prepare('SELECT * FROM pracownicy WHERE id = ?');
        $s->execute([$eid]);
        $r = $s->fetch();
        if ($r) $rec = $r;
        // check if current user is allowed to edit this record
        $allowedEditRec = false;
        if ($rola_id === 1) $allowedEditRec = true;
        elseif ($rola_id === 2) { if ((int)$rec['rola_id'] !== 1) $allowedEditRec = true; }
        elseif ($rola_id === 3) { if ((int)$rec['rola_id'] === 4) $allowedEditRec = true; }
        if (!$allowedEditRec) {
            $err = 'Brak uprawnień do edycji tego pracownika.';
            // prevent edit form showing
            unset($_GET['action']);
        }
    }
    ?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($rec['id']); ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Imię</label>
                <input name="imie" class="form-control" value="<?php echo htmlspecialchars($rec['imie']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Nazwisko</label>
                <input name="nazwisko" class="form-control" value="<?php echo htmlspecialchars($rec['nazwisko']); ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input name="email" class="form-control" value="<?php echo htmlspecialchars($rec['email']); ?>" required>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Rola</label>
                <select name="rola_id" class="form-control">
                    <?php foreach ($roles as $ro): ?>
                        <?php
                            // filter which roles current user may assign
                            $showRoleOption = true;
                            if ($rola_id === 2 && (int)$ro['id'] === 1) $showRoleOption = false; // HR cannot assign ADMIN
                            if ($rola_id === 3 && in_array((int)$ro['id'], [1,2])) $showRoleOption = false; // Kierownik cannot assign ADMIN or HR
                            if (!$showRoleOption) continue;
                        ?>
                        <option value="<?php echo $ro['id']; ?>" <?php if ($ro['id']==$rec['rola_id']) echo 'selected'; ?>><?php echo htmlspecialchars($ro['nazwa']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>Dział</label>
                <select name="dzial_id" class="form-control">
                    <option value="">--</option>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php if ($d['id']==$rec['dzial_id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['nazwa']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>Tytuł</label>
                <select name="stanowisko_id" class="form-control">
                    <option value="">--</option>
                    <?php foreach ($titles as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php if ($t['id']==$rec['stanowisko_id']) echo 'selected'; ?>><?php echo htmlspecialchars($t['nazwa']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Region</label>
                <select name="region_id" class="form-control">
                    <option value="">--</option>
                    <?php foreach ($regions as $rg): ?>
                        <option value="<?php echo $rg['id']; ?>" <?php if ($rg['id']==$rec['region_id']) echo 'selected'; ?>><?php echo htmlspecialchars($rg['nazwa']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>Telefon</label>
                <input name="telefon" class="form-control" value="<?php echo htmlspecialchars($rec['telefon']); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Data zatrudnienia</label>
                <input type="date" name="data_zatrudnienia" class="form-control" value="<?php echo htmlspecialchars($rec['data_zatrudnienia']); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label>Adres</label>
            <input name="adres" class="form-control" value="<?php echo htmlspecialchars($rec['adres']); ?>">
        </div>
        <div class="mb-3">
            <label>Hasło</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label>Powtórz hasło</label>
            <input type="password" name="password_confirm" class="form-control">
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="active" class="form-check-input" id="active" <?php if ($rec['aktywny']) echo 'checked'; ?>><label class="form-check-label" for="active">Aktywny</label>
        </div>
        <button class="btn btn-primary">Zapisz</button>
        <a href="pracownicy.php" class="btn btn-secondary">Anuluj</a>
    </form>
<?php else: ?>

<table class="table table-hover table-sm">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Imię Nazwisko</th>
            <th>Email</th>
            <th>Rola</th>
            <th>Data Zatrudnienia</th>
            <th>Aktywny</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pracownicy as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['id']); ?></td>
                <td><?php echo htmlspecialchars($p['imie'] . ' ' . $p['nazwisko']); ?></td>
                <td><?php echo htmlspecialchars($p['email']); ?></td>
                <td><?php echo htmlspecialchars($p['role_name']); ?></td>
                <td><?php echo htmlspecialchars($p['data_zatrudnienia']); ?></td>
                <td><?php echo $p['aktywny'] ? 'Tak' : 'Nie'; ?></td>
                <td>
                    <?php
                        $canEditRow = false; $canDeleteRow = false;
                        if ($rola_id === 1) { $canEditRow = $canDeleteRow = true; }
                        elseif ($rola_id === 2) { if ($p['rola_id'] != 1) { $canEditRow = $canDeleteRow = true; } }
                        elseif ($rola_id === 3) { if ($p['rola_id'] == 4) { $canEditRow = $canDeleteRow = true; } }
                    ?>
                    <?php if ($canEditRow): ?>
                        <a href="pracownicy.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Edytuj</a>
                    <?php endif; ?>
                    <?php if ($canDeleteRow): ?>
                        <a href="pracownicy.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć tego pracownika?');">Usuń</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<?php 
include 'szablony/stopka.php'; 
?>
