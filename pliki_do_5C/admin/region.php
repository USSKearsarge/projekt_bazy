<?php
//Załęski
require '../cfg.php';
require 'csrf.php';

require 'auth.php';
require_access(['magazyn', 'warehouse']);

$canManage = can_write(['magazyn', 'warehouse']);
$err = '';

if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
            $name = trim($_POST['name'] ?? '');

            if ($name === '' || mb_strlen($name) > 50) {
                $err = 'Nazwa jest wymagana (maks. 50 znaków).';
            } else {
                if ($id) {
                    $s = $pdo->prepare('UPDATE region SET name = ? WHERE id = ?');
                    $s->execute([$name, $id]);
                } else {
                    $s = $pdo->prepare('INSERT INTO region (name) VALUES (?)');
                    $s->execute([$name]);
                }
                header('Location: region.php');
                exit;
            }
        } elseif ($action === 'delete') {
            $did = (int)($_POST['id'] ?? 0);
            $d = $pdo->prepare('DELETE FROM region WHERE id = ?');
            $d->execute([$did]);
            header('Location: region.php');
            exit;
        }
    } catch (PDOException $e) {
        // 1451 = rekord jest używany przez inną tabelę (klucz obcy)
        $err = ($e->errorInfo[1] ?? 0) === 1451
            ? 'Nie można usunąć regionu, ponieważ jest używany (magazyny, klienci lub działy).'
            : 'Błąd bazy danych podczas zapisu.';
    }
}

$rows = $pdo->query("SELECT * FROM region ORDER BY id")->fetchAll();

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Regiony (Tabela: REGION)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="region.php?action=add">Dodaj region</a><?php endif; ?>
</div>
<p class="lead">Lista regionów przypisanych do magazynów.</p>

<?php if ($err): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php
$act = $_GET['action'] ?? '';
$edit = null;
if ($canManage && $act === 'edit' && isset($_GET['id'])) {
    $s = $pdo->prepare('SELECT * FROM region WHERE id = ?');
    $s->execute([(int)$_GET['id']]);
    $edit = $s->fetch() ?: null;
}
if ($canManage && in_array($act, ['add', 'edit'], true)):
    $idVal = $edit['id'] ?? '';
    $nameVal = $edit['name'] ?? '';
?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$idVal); ?>">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="name" placeholder="Nazwa" maxlength="50" required value="<?php echo htmlspecialchars($nameVal); ?>"></div>
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
                    <a class="btn btn-sm btn-outline-primary" href="region.php?action=edit&id=<?php echo urlencode((string)$r['id']); ?>">Edytuj</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Usunąć region?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$r['id']); ?>">
                        <button class="btn btn-sm btn-outline-danger">Usuń</button>
                    </form>
                </td><?php endif; ?>
            </tr><?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
