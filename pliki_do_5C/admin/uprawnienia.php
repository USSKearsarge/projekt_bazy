<?php
//poprawiał Mateusz Syska
// Tabela permissions: username (FK -> emp.username), menu_id (FK -> menu.id), type (R/W)
// Klucz główny: (username, menu_id)
require '../cfg.php';
require 'csrf.php';

require 'auth.php';
require_write(['hr']);

$currentUsername = $_SESSION['username'] ?? '';
$currentTitle = '';

if ($currentUsername !== '') {
    $stmtUser = $pdo->prepare('SELECT title FROM emp WHERE username = ? LIMIT 1');
    $stmtUser->execute([$currentUsername]);
    $currentTitle = $stmtUser->fetchColumn() ?: '';
}

// Nadawać uprawnienia mogą tylko: zapis w module hr ORAZ stanowisko President / VP, Administration.
if (!in_array($currentTitle, ['President', 'VP, Administration'], true)) {
    header('Location: index.php');
    exit;
}
$canManage = true;

$err = '';
$form = null;

/* Użytkownicy z kontem w tabeli emp. */
$users = $pdo->query("
    SELECT username, first_name, last_name, title
    FROM emp
    WHERE username IS NOT NULL AND username <> ''
    ORDER BY username
")->fetchAll(PDO::FETCH_ASSOC);
$usernames = array_column($users, 'username');

/* Moduły z tabeli menu (permissions.menu_id ma klucz obcy do menu.id). */
$menus = $pdo->query("SELECT id, name FROM menu ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$menuIds = array_column($menus, 'id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $origUser = trim($_POST['orig_username'] ?? '');
            $origMenu = trim($_POST['orig_menu_id'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $menu     = trim($_POST['menu_id'] ?? '');
            $type     = strtoupper(trim($_POST['type'] ?? ''));

            if (!in_array($username, $usernames, true)
                || !in_array($menu, $menuIds, true)
                || !in_array($type, ['R', 'W'], true)) {
                $err = 'Uzupełnij użytkownika, moduł oraz poprawny typ uprawnienia.';
                $form = ['orig_username' => $origUser, 'orig_menu_id' => $origMenu,
                         'username' => $username, 'menu_id' => $menu, 'type' => $type];
            } else {
                $pdo->beginTransaction();
                // Edycja ze zmianą klucza - usuń stary wpis.
                if ($origUser !== '' && ($origUser !== $username || $origMenu !== $menu)) {
                    $d = $pdo->prepare('DELETE FROM permissions WHERE username = ? AND menu_id = ?');
                    $d->execute([$origUser, $origMenu]);
                }
                $s = $pdo->prepare('
                    INSERT INTO permissions (username, menu_id, type) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE type = VALUES(type)
                ');
                $s->execute([$username, $menu, $type]);
                $pdo->commit();
                header('Location: uprawnienia.php');
                exit;
            }
        } elseif ($action === 'delete') {
            $d = $pdo->prepare('DELETE FROM permissions WHERE username = ? AND menu_id = ?');
            $d->execute([trim($_POST['username'] ?? ''), trim($_POST['menu_id'] ?? '')]);
            header('Location: uprawnienia.php');
            exit;
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $err = 'Błąd bazy danych podczas zapisu.';
    }
}

/* Lista uprawnień. */
$rows = $pdo->query("
    SELECT p.username, p.menu_id, m.name AS menu_nazwa, p.type,
           e.first_name, e.last_name, e.title
    FROM permissions p
    LEFT JOIN emp e ON e.username = p.username
    LEFT JOIN menu m ON m.id = p.menu_id
    ORDER BY p.username, p.menu_id
")->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Uprawnienia (Tabela: permissions)</h2>
    <a class="btn btn-primary" href="uprawnienia.php?action=add">Dodaj uprawnienie</a>
</div>

<p class="lead">Lista uprawnień użytkowników systemu.</p>

<?php if ($err): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php
$act = $_GET['action'] ?? '';
$edit = null;

if ($act === 'edit' && isset($_GET['username'], $_GET['menu_id'])) {
    $editStmt = $pdo->prepare('
        SELECT username, menu_id, type FROM permissions
        WHERE username = ? AND menu_id = ? LIMIT 1
    ');
    $editStmt->execute([$_GET['username'], $_GET['menu_id']]);
    $edit = $editStmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if (in_array($act, ['add', 'edit'], true) || $form !== null):
    $origUserVal = $form['orig_username'] ?? ($edit['username'] ?? '');
    $origMenuVal = $form['orig_menu_id'] ?? ($edit['menu_id'] ?? '');
    $usernameVal = $form['username'] ?? ($edit['username'] ?? '');
    $menuVal     = $form['menu_id'] ?? ($edit['menu_id'] ?? '');
    $typeVal     = $form['type'] ?? ($edit['type'] ?? 'R');
?>
    <form method="post" class="mb-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="orig_username" value="<?php echo htmlspecialchars((string)$origUserVal); ?>">
        <input type="hidden" name="orig_menu_id" value="<?php echo htmlspecialchars((string)$origMenuVal); ?>">

        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Użytkownik</label>
                <select name="username" class="form-control" required>
                    <option value="">-- Użytkownik --</option>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                        $label = $user['username'];
                        if ($displayName !== '') { $label .= ' - ' . $displayName; }
                        if (!empty($user['title'])) { $label .= ' (' . $user['title'] . ')'; }
                        ?>
                        <option value="<?php echo htmlspecialchars($user['username']); ?>"
                            <?php echo $user['username'] === $usernameVal ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Moduł / menu</label>
                <select name="menu_id" class="form-control" required>
                    <option value="">-- Moduł --</option>
                    <?php foreach ($menus as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['id']); ?>"
                            <?php echo $m['id'] === $menuVal ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['id'] . ' - ' . $m['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Typ uprawnienia</label>
                <select name="type" class="form-control" required>
                    <option value="R" <?php echo $typeVal === 'R' ? 'selected' : ''; ?>>R - Odczyt</option>
                    <option value="W" <?php echo $typeVal === 'W' ? 'selected' : ''; ?>>W - Zapis</option>
                </select>
            </div>

            <div class="col-md-12 mt-2">
                <button class="btn btn-success" type="submit">Zapisz</button>
                <a class="btn btn-secondary" href="uprawnienia.php">Anuluj</a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php if (count($rows) === 0): ?>

    <p>Brak rekordów w tabeli permissions.</p>

<?php else: ?>

    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead class="table-dark">
                <tr>
                    <th>Użytkownik</th>
                    <th>Pracownik</th>
                    <th>Stanowisko</th>
                    <th>Moduł</th>
                    <th>Typ</th>
                    <th>Uprawnienie</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $displayName = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                    if ($displayName === '') { $displayName = '-'; }

                    $permissionName = match (strtoupper($r['type'] ?? '')) {
                        'W' => 'Zapis',
                        'R' => 'Odczyt',
                        default => 'Nieznane'
                    };
                    $menuLabel = $r['menu_id'] . ($r['menu_nazwa'] ? ' - ' . $r['menu_nazwa'] : '');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['username']); ?></td>
                        <td><?php echo htmlspecialchars($displayName); ?></td>
                        <td><?php echo htmlspecialchars($r['title'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($menuLabel); ?></td>
                        <td><?php echo htmlspecialchars($r['type'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($permissionName); ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary"
                               href="uprawnienia.php?action=edit&username=<?php echo urlencode($r['username']); ?>&menu_id=<?php echo urlencode($r['menu_id']); ?>">Edytuj</a>

                            <form method="post" class="d-inline" onsubmit="return confirm('Usunąć uprawnienie?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($r['username']); ?>">
                                <input type="hidden" name="menu_id" value="<?php echo htmlspecialchars($r['menu_id']); ?>">
                                <button class="btn btn-sm btn-outline-danger">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
