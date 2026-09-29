<?php
// Zrobił Mateusz Syska (Plik Jakub Staniec)
// Tabela title: name (PK), salary_min, salary_max
require '../cfg.php';
require 'csrf.php';

require 'auth.php';
require_write(['hr']);

$canManage = true;
$err = '';
$form = null; // wartości formularza po błędzie walidacji

if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $orig = trim($_POST['orig_name'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $min  = str_replace(',', '.', trim($_POST['salary_min'] ?? ''));
            $max  = str_replace(',', '.', trim($_POST['salary_max'] ?? ''));

            if ($name === '' || mb_strlen($name) > 25) {
                $err = 'Nazwa stanowiska jest wymagana (maks. 25 znaków).';
            } elseif (!is_numeric($min) || !is_numeric($max) || $min < 0 || $max < 0) {
                $err = 'Widełki płacowe muszą być nieujemnymi liczbami.';
            } elseif ((float)$min > (float)$max) {
                $err = 'Płaca minimalna nie może być większa od maksymalnej.';
            } else {
                if ($orig !== '') {
                    $s = $pdo->prepare('UPDATE title SET name = ?, salary_min = ?, salary_max = ? WHERE name = ?');
                    $s->execute([$name, $min, $max, $orig]);
                } else {
                    $s = $pdo->prepare('INSERT INTO title (name, salary_min, salary_max) VALUES (?, ?, ?)');
                    $s->execute([$name, $min, $max]);
                }
                header('Location: title.php');
                exit;
            }
            $form = ['name' => $name, 'orig' => $orig, 'salary_min' => $min, 'salary_max' => $max];
        } elseif ($action === 'delete') {
            $d = $pdo->prepare('DELETE FROM title WHERE name = ?');
            $d->execute([trim($_POST['name'] ?? '')]);
            header('Location: title.php');
            exit;
        }
    } catch (PDOException $e) {
        $code = $e->errorInfo[1] ?? 0;
        if ($code === 1062) {
            $err = 'Stanowisko o takiej nazwie już istnieje.';
        } elseif ($code === 1451 || $code === 1452) {
            $err = 'Operacja niemożliwa: stanowisko jest używane (pracownicy lub role).';
        } else {
            $err = 'Błąd bazy danych podczas zapisu.';
        }
        $form = $form ?? null;
    }
}

$rows = $pdo->query("SELECT * FROM title ORDER BY name")->fetchAll();

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Stanowisk (Tabela: TITLE)</h2>
    <?php if ($canManage): ?><a class="btn btn-primary" href="title.php?action=add">Dodaj stanowisko</a><?php endif; ?>
</div>
<p class="lead">Lista stanowisk w firmie.</p>

<?php if ($err): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php
$act = $_GET['action'] ?? '';
$edit = null;
if ($canManage && $act === 'edit' && isset($_GET['name'])) {
    $s = $pdo->prepare('SELECT * FROM title WHERE name = ?');
    $s->execute([$_GET['name']]);
    $edit = $s->fetch() ?: null;
}
$showForm = $canManage && (in_array($act, ['add', 'edit'], true) || $form !== null);
if ($showForm):
    $origVal = $form['orig'] ?? ($edit['name'] ?? '');
    $nameVal = $form['name'] ?? ($edit['name'] ?? '');
    $minVal  = $form['salary_min'] ?? ($edit['salary_min'] ?? '');
    $maxVal  = $form['salary_max'] ?? ($edit['salary_max'] ?? '');
?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="orig_name" value="<?php echo htmlspecialchars((string)$origVal); ?>">
        <div class="row g-2">
            <div class="col-md-4"><input class="form-control" name="name" placeholder="Nazwa" maxlength="25" required value="<?php echo htmlspecialchars((string)$nameVal); ?>"></div>
            <div class="col-md-4"><input class="form-control" name="salary_min" type="number" step="0.01" min="0" placeholder="Płaca min" required value="<?php echo htmlspecialchars((string)$minVal); ?>"></div>
            <div class="col-md-4"><input class="form-control" name="salary_max" type="number" step="0.01" min="0" placeholder="Płaca max" required value="<?php echo htmlspecialchars((string)$maxVal); ?>"></div>
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
                    <a class="btn btn-sm btn-outline-primary" href="title.php?action=edit&name=<?php echo urlencode($r['name']); ?>">Edytuj</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Usunąć stanowisko?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($r['name']); ?>">
                        <button class="btn btn-sm btn-outline-danger">Usuń</button>
                    </form>
                </td><?php endif; ?>
            </tr><?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
