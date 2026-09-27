<?php
// Zrobił Mateusz Syska (Plik Jakub Staniec)
require '../cfg.php';

// if(!isset($_SESSION['zalogowany'])){
//     header('Location: logowanie.php');
//     exit;
// }

// $rola_id = $_SESSION['rola_id'] ?? 0;
// if (!in_array($rola_id, [1, 2])) {
//     header('Location: index.php');
//     exit;
// }


$canManage = in_array($rola_id, [1,2]);

if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $nazwa = trim($_POST['nazwa'] ?? '');
    if ($id) {
        $s = $pdo->prepare('UPDATE title SET name=? WHERE id=?');
        $s->execute([$nazwa, $id]);
    } else {
        $s = $pdo->prepare('INSERT INTO title (name) VALUES (?)');
        $s->execute([$nazwa]);
    }
    header('Location: title.php'); exit;
}
if ($canManage && isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    $d = $pdo->prepare('DELETE FROM title WHERE id = ?');
    $d->execute([$did]);
    header('Location: title.php'); exit;
}

$stmt = $pdo->query("SELECT * FROM title ORDER BY name");
$rows = $stmt->fetchAll();

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Stanowisk (Tabela: TITLE)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="title.php?action=add">Dodaj stanowisko</a><?php endif; ?>
</div>
<p class="lead">Lista stanowisk w firmie.</p>

<?php
$act = $_GET['action'] ?? '';
$edit = null;
if ($canManage && $act === 'edit' && isset($_GET['id'])) {
    $eid = (int)$_GET['id']; $s = $pdo->prepare('SELECT * FROM title WHERE id = ?'); $s->execute([$eid]); $edit = $s->fetch();
}
if ($canManage && in_array($act, ['add','edit'])):
    $idVal = $edit['id'] ?? '';
    $nazwaVal = $edit['nazwa'] ?? '';
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($idVal); ?>">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="name" placeholder="Name" value="<?php echo htmlspecialchars($nazwaVal); ?>"></div>
            <div class="col-md-12 mt-2"><button class="btn btn-success">Zapisz</button> <a class="btn btn-secondary" href="title.php">Anuluj</a></div>
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
                    <a class="btn btn-sm btn-outline-primary" href="title.php?action=edit&id=<?php echo urlencode($r['name']); ?>">Edytuj</a>
                    <a class="btn btn-sm btn-outline-danger" href="title.php?action=delete&id=<?php echo urlencode($r['name']); ?>" onclick="return confirm('Usunąć stanowisko?');">Usuń</a>
                </td><?php endif; ?>
            </tr><?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
