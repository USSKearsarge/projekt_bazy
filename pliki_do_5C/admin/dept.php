<?php
// dept.php | Tabela: dept (id, name, region_id) | dostęp: menu 'hr'
require '../cfg.php';
require 'csrf.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

$perms = $_SESSION['perms'] ?? [];
if (!isset($perms['hr'])) {
    header('Location: index.php');
    exit;
}
$canManage = ($perms['hr'] ?? '') === 'W';

// Ochrona CSRF dla wszystkich żądań POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$err = '';
$regions = $pdo->query('SELECT id, name FROM region ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$regionIds = array_map('intval', array_column($regions, 'id'));

$rec = ['id' => '', 'name' => '', 'region_id' => ''];

if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $nazwa = trim($_POST['nazwa'] ?? '');
    $region_id = ($_POST['region_id'] ?? '') !== '' ? (int)$_POST['region_id'] : null;
    $rec = ['id' => $id ?: '', 'name' => $nazwa, 'region_id' => $region_id ?? ''];

    if ($nazwa === '') {
        $err = 'Podaj nazwę działu.';
    } elseif (mb_strlen($nazwa) > 25) {
        $err = 'Nazwa może mieć maksymalnie 25 znaków.';
    } elseif ($region_id !== null && !in_array($region_id, $regionIds, true)) {
        $err = 'Nieprawidłowy region.';
    }

    if ($err === '') {
        try {
            if ($id > 0) {
                $s = $pdo->prepare('UPDATE dept SET name=?, region_id=? WHERE id=?');
                $s->execute([$nazwa, $region_id, $id]);
            } else {
                $s = $pdo->prepare('INSERT INTO dept (name, region_id) VALUES (?,?)');
                $s->execute([$nazwa, $region_id]);
            }
            header('Location: dept.php');
            exit;
        } catch (PDOException $e) {
            $err = 'Nie udało się zapisać działu.';
        }
    }
}

if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    try {
        $d = $pdo->prepare('DELETE FROM dept WHERE id = ?');
        $d->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: dept.php');
        exit;
    } catch (PDOException $e) {
        // emp.dept_id ma klucz obcy do dept
        $err = 'Nie można usunąć działu – są do niego przypisani pracownicy.';
    }
}

$act = $_GET['action'] ?? '';
if ($canManage && $act === 'edit' && $_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['id'])) {
    $s = $pdo->prepare('SELECT * FROM dept WHERE id = ?');
    $s->execute([(int)$_GET['id']]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) $rec = $row;
}

$rows = $pdo->query(
    'SELECT d.id, d.name, d.region_id, r.name AS region_name
     FROM dept d LEFT JOIN region r ON d.region_id = r.id
     ORDER BY d.id'
)->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Działów (Tabela: DEPT)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="dept.php?action=add">Dodaj dział</a><?php endif; ?>
</div>
<p class="lead">Lista działów organizacyjnych.</p>

<?php if ($err !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if ($canManage && in_array($act, ['add', 'edit'], true)): ?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$rec['id']); ?>">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="nazwa" maxlength="25" placeholder="Nazwa" required value="<?php echo htmlspecialchars($rec['name']); ?>"></div>
            <div class="col-md-6">
                <select name="region_id" class="form-control">
                    <option value="">-- Region --</option>
                    <?php foreach ($regions as $rg): ?>
                        <option value="<?php echo (int)$rg['id']; ?>" <?php echo ((string)$rg['id'] === (string)$rec['region_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($rg['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 mt-2"><button class="btn btn-success">Zapisz</button> <a class="btn btn-secondary" href="dept.php">Anuluj</a></div>
        </div>
    </form>
<?php endif; ?>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Nazwa</th><th>Region ID</th><th>Region</th>
                <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo (int)$r['id']; ?></td>
                    <td><?php echo htmlspecialchars($r['name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$r['region_id']); ?></td>
                    <td><?php echo htmlspecialchars((string)$r['region_name']); ?></td>
                    <?php if ($canManage): ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="dept.php?action=edit&id=<?php echo urlencode($r['id']); ?>">Edytuj</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Usunąć dział?');">
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
