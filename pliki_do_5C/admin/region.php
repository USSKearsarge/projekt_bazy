<?php
 //Załęski
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

$canManage = in_array($rola_id, [1, 2, 3]);

if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $nazwa = trim($_POST['nazwa'] ?? '');
    if ($id) {
        $s = $pdo->prepare('UPDATE region SET nazwa=? WHERE id=?');
        $s->execute([$nazwa, $id]);
    } else {
        $s = $pdo->prepare('INSERT INTO region (nazwa) VALUES (?)');
        $s->execute([$nazwa]);
    }
    header('Location: region.php'); exit;
}
if ($canManage && isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    $d = $pdo->prepare('DELETE FROM region WHERE id = ?');
    $d->execute([$did]);
    header('Location: region.php'); exit;
}

$stmt = $pdo->query("SELECT * FROM region ORDER BY id");
$rows = $stmt->fetchAll();

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Regiony (Tabela: REGION)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="region.php?action=add">Dodaj region</a><?php endif; ?>
</div>
<p class="lead">Lista regionów przypisanych do magazynów.</p>

<?php
$act = $_GET['action'] ?? '';
$edit = null;
if ($canManage && $act === 'edit' && isset($_GET['id'])) {
    $eid = (int)$_GET['id']; $s = $pdo->prepare('SELECT * FROM region WHERE id = ?'); $s->execute([$eid]); $edit = $s->fetch();
}
if ($canManage && in_array($act, ['add','edit'])):
    $idVal = $edit['id'] ?? '';
    $nazwaVal = $edit['nazwa'] ?? '';
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($idVal); ?>">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="nazwa" placeholder="Nazwa" value="<?php echo htmlspecialchars($nazwaVal); ?>"></div>
            <div class="col-md-12 mt-2"><button class="btn btn-success">Zapisz</button> <a class="btn btn-secondary" href="region.php">Anuluj</a></div>
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
                    <a class="btn btn-sm btn-outline-primary" href="region.php?action=edit&id=<?php echo urlencode($r['id']); ?>">Edytuj</a>
                    <a class="btn btn-sm btn-outline-danger" href="region.php?action=delete&id=<?php echo urlencode($r['id']); ?>" onclick="return confirm('Usunąć region?');">Usuń</a>
                </td><?php endif; ?>
            </tr><?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
