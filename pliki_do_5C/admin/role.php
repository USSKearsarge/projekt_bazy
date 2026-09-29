<?php
//Sołtysik_Załęski
// Tabela roles: nazwa (FK -> title.name), menu_id (FK -> menu.id), typ_uprawnien (R/W)
// Klucz główny: (nazwa, menu_id) - tabela NIE ma kolumny id.
require '../cfg.php';
require 'csrf.php';

require 'auth.php';
require_write(['hr']);

$canManage = true;
$err = '';
$form = null;

$titles = $pdo->query("SELECT name FROM title ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$menus  = $pdo->query("SELECT id, name FROM menu ORDER BY id")->fetchAll();
$menuIds = array_column($menus, 'id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $origNazwa = trim($_POST['orig_nazwa'] ?? '');
            $origMenu  = trim($_POST['orig_menu_id'] ?? '');
            $nazwa = trim($_POST['nazwa'] ?? '');
            $menu  = trim($_POST['menu_id'] ?? '');
            $typ   = strtoupper(trim($_POST['typ_uprawnien'] ?? ''));

            if (!in_array($nazwa, $titles, true) || !in_array($menu, $menuIds, true) || !in_array($typ, ['R', 'W'], true)) {
                $err = 'Wybierz poprawne stanowisko, moduł oraz typ uprawnienia.';
                $form = ['orig_nazwa' => $origNazwa, 'orig_menu_id' => $origMenu, 'nazwa' => $nazwa, 'menu_id' => $menu, 'typ' => $typ];
            } else {
                $pdo->beginTransaction();
                // Zmiana klucza (edycja) - usuń stary wiersz
                if ($origNazwa !== '' && ($origNazwa !== $nazwa || $origMenu !== $menu)) {
                    $d = $pdo->prepare('DELETE FROM roles WHERE nazwa = ? AND menu_id = ?');
                    $d->execute([$origNazwa, $origMenu]);
                }
                $s = $pdo->prepare(
                    'INSERT INTO roles (nazwa, menu_id, typ_uprawnien) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE typ_uprawnien = VALUES(typ_uprawnien)'
                );
                $s->execute([$nazwa, $menu, $typ]);
                $pdo->commit();
                header('Location: role.php');
                exit;
            }
        } elseif ($action === 'delete') {
            $d = $pdo->prepare('DELETE FROM roles WHERE nazwa = ? AND menu_id = ?');
            $d->execute([trim($_POST['nazwa'] ?? ''), trim($_POST['menu_id'] ?? '')]);
            header('Location: role.php');
            exit;
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $err = 'Błąd bazy danych podczas zapisu.';
    }
}

$rows = $pdo->query("
    SELECT r.nazwa, r.menu_id, m.name AS menu_nazwa, r.typ_uprawnien
    FROM roles r
    LEFT JOIN menu m ON m.id = r.menu_id
    ORDER BY r.nazwa, r.menu_id
")->fetchAll();

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Ról (Tabela: ROLES)</h2>
    <a class="btn btn-primary" href="role.php?action=add">Dodaj rolę</a>
</div>
<p class="lead">Uprawnienia stanowisk do poszczególnych modułów.</p>

<?php if ($err): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php
$act = $_GET['action'] ?? '';
$edit = null;
if ($act === 'edit' && isset($_GET['nazwa'], $_GET['menu_id'])) {
    $s = $pdo->prepare('SELECT * FROM roles WHERE nazwa = ? AND menu_id = ?');
    $s->execute([$_GET['nazwa'], $_GET['menu_id']]);
    $edit = $s->fetch() ?: null;
}
if (in_array($act, ['add', 'edit'], true) || $form !== null):
    $origNazwaVal = $form['orig_nazwa'] ?? ($edit['nazwa'] ?? '');
    $origMenuVal  = $form['orig_menu_id'] ?? ($edit['menu_id'] ?? '');
    $nazwaVal = $form['nazwa'] ?? ($edit['nazwa'] ?? '');
    $menuVal  = $form['menu_id'] ?? ($edit['menu_id'] ?? '');
    $typVal   = $form['typ'] ?? ($edit['typ_uprawnien'] ?? 'R');
?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="orig_nazwa" value="<?php echo htmlspecialchars((string)$origNazwaVal); ?>">
        <input type="hidden" name="orig_menu_id" value="<?php echo htmlspecialchars((string)$origMenuVal); ?>">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Stanowisko</label>
                <select name="nazwa" class="form-control" required>
                    <option value="">-- Stanowisko --</option>
                    <?php foreach ($titles as $t): ?>
                        <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $t === $nazwaVal ? 'selected' : ''; ?>><?php echo htmlspecialchars($t); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Moduł / menu</label>
                <select name="menu_id" class="form-control" required>
                    <option value="">-- Moduł --</option>
                    <?php foreach ($menus as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['id']); ?>" <?php echo $m['id'] === $menuVal ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['id'] . ' - ' . $m['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Typ uprawnienia</label>
                <select name="typ_uprawnien" class="form-control" required>
                    <option value="R" <?php echo $typVal === 'R' ? 'selected' : ''; ?>>R - Odczyt</option>
                    <option value="W" <?php echo $typVal === 'W' ? 'selected' : ''; ?>>W - Zapis</option>
                </select>
            </div>
            <div class="col-md-12 mt-2"><button class="btn btn-success">Zapisz</button> <a class="btn btn-secondary" href="role.php">Anuluj</a></div>
        </div>
    </form>
<?php endif; ?>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark"><tr>
            <th>Stanowisko</th><th>Moduł</th><th>Nazwa modułu</th><th>Typ</th><th>Akcje</th>
        </tr></thead>
        <tbody>
            <?php foreach ($rows as $r): ?><tr>
                <td><?php echo htmlspecialchars($r['nazwa']); ?></td>
                <td><?php echo htmlspecialchars($r['menu_id']); ?></td>
                <td><?php echo htmlspecialchars((string)$r['menu_nazwa']); ?></td>
                <td><?php echo htmlspecialchars((string)$r['typ_uprawnien']); ?></td>
                <td>
                    <a class="btn btn-sm btn-outline-primary" href="role.php?action=edit&nazwa=<?php echo urlencode($r['nazwa']); ?>&menu_id=<?php echo urlencode($r['menu_id']); ?>">Edytuj</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Usunąć rolę?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="nazwa" value="<?php echo htmlspecialchars($r['nazwa']); ?>">
                        <input type="hidden" name="menu_id" value="<?php echo htmlspecialchars($r['menu_id']); ?>">
                        <button class="btn btn-sm btn-outline-danger">Usuń</button>
                    </form>
                </td>
            </tr><?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
