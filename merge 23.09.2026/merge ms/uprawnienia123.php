<?php
//poprawiał Mateusz Syska
require '../cfg.php';

// if (!isset($_SESSION['zalogowany'])) {
//     header('Location: logowanie.php');
//     exit;
// }

/*
 * Baza używa tabeli `permissions`: NOGAJ
 * username | menu | type
 *
 * Tabela `roles` nie posiada pola ID, więc nie używamy już
 * role_id / uprawnienia / can_read / can_write.
 */

$currentUsername = $_SESSION['username'] ?? $_SESSION['login'] ?? '';
$currentTitle = '';

if ($currentUsername !== '') {
    $stmtUser = $pdo->prepare('SELECT title FROM emp WHERE username = ? LIMIT 1');
    $stmtUser->execute([$currentUsername]);
    $currentTitle = $stmtUser->fetchColumn() ?: '';
}

/*
 * Zachowujemy obsługę starego $_SESSION['rola_id'], jeśli login.php
 * nadal go ustawia. Dodatkowo President i VP, Administration mogą
 * zarządzać uprawnieniami na podstawie tabeli emp.
 */
$rola_id = (int)($_SESSION['rola_id'] ?? 0);
$canManage = in_array($rola_id, [1, 2], true)
    || in_array($currentTitle, ['President', 'VP, Administration'], true);

// if (!$canManage) {
//     header('Location: index.php');
//     exit;
// }

/* Lista użytkowników posiadających konto w tabeli emp. */
$users = $pdo->query("
    SELECT username, first_name, last_name, title
    FROM emp
    WHERE username IS NOT NULL AND username <> ''
    ORDER BY username
")->fetchAll(PDO::FETCH_ASSOC);

/* Menu z tabeli roles + istniejące menu z permissions. */
$menus = $pdo->query("
    SELECT menu_id
    FROM roles
    WHERE menu_id IS NOT NULL AND menu_id <> ''
    UNION
    SELECT menu_id
    FROM permissions
    WHERE menu_id IS NOT NULL AND menu_id <> ''
    ORDER BY menu_id
")->fetchAll(PDO::FETCH_COLUMN);

/* Zapis uprawnień. */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'save') {

    $username = trim($_POST['username'] ?? '');
    $menu = trim($_POST['menu'] ?? '');
    $canRead = isset($_POST['read']);
    $canWrite = isset($_POST['write']);

    // Zapis (W) jest możliwy tylko razem z Odczytem (R).
    if ($canWrite) {
        $canRead = true;
    }

    if ($username === '' || $menu === '' || (!$canRead && !$canWrite)) {
        header('Location: uprawnienia.php?action=add&error=1');
        exit;
    }

    // Usuwamy dotychczasowe uprawnienia dla użytkownika i menu.
    $delete = $pdo->prepare('DELETE FROM permissions WHERE username = ? AND menu = ?');
    $delete->execute([$username, $menu]);

    // Odczyt zapisujemy jako R.
    if ($canRead) {
        $insert = $pdo->prepare('INSERT INTO permissions (username, menu, type) VALUES (?, ?, ?)');
        $insert->execute([$username, $menu, 'R']);
    }

    // Zapis zapisujemy jako W. Dzięki temu R + W mogą istnieć jednocześnie.
    if ($canWrite) {
        $insert = $pdo->prepare('INSERT INTO permissions (username, menu, type) VALUES (?, ?, ?)');
        $insert->execute([$username, $menu, 'W']);
    }

    header('Location: uprawnienia.php');
    exit;
}

/* Usuwanie uprawnienia. */
if (isset($_GET['action'])
    && $_GET['action'] === 'delete'
    && isset($_GET['username'])
    && isset($_GET['menu'])) {

    $username = $_GET['username'];
    $menu = $_GET['menu'];

    $delete = $pdo->prepare('DELETE FROM permissions WHERE username = ? AND menu = ?');
    $delete->execute([$username, $menu]);

    header('Location: uprawnienia.php');
    exit;
}

/* Lista uprawnień. */
$stmt = $pdo->query("
    SELECT
        p.username,
        p.menu_id,
        MAX(CASE WHEN p.type = 'R' THEN 1 ELSE 0 END) AS can_read,
        MAX(CASE WHEN p.type = 'W' THEN 1 ELSE 0 END) AS can_write,
        e.first_name,
        e.last_name,
        e.title
    FROM permissions p
    LEFT JOIN emp e ON e.username = p.username
    GROUP BY p.username, p.menu_id, e.first_name, e.last_name, e.title
    ORDER BY p.username, p.menu_id
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Uprawnienia (Tabela: permissions)</h2>
    <a class="btn btn-primary" href="uprawnienia.php?action=add">Dodaj uprawnienie</a>
</div>

<p class="lead">Lista uprawnień użytkowników systemu.</p>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        Uzupełnij użytkownika, moduł oraz poprawny typ uprawnienia.
    </div>
<?php endif; ?>

<?php
$act = $_GET['action'] ?? '';
$edit = null;

if ($act === 'edit'
    && isset($_GET['username'])
    && isset($_GET['menu'])) {

    $username = $_GET['username'];
    $menu = $_GET['menu'];

    $editStmt = $pdo->prepare('
        SELECT username, menu, type
        FROM permissions
        WHERE username = ? AND menu = ?
    ');
    $editStmt->execute([$username, $menu]);
    $editRows = $editStmt->fetchAll(PDO::FETCH_ASSOC);

    $edit = [
        'username' => $username,
        'menu' => $menu,
        'read' => false,
        'write' => false
    ];

    foreach ($editRows as $permission) {
        if (strtoupper($permission['type']) === 'R') {
            $edit['read'] = true;
        }
        if (strtoupper($permission['type']) === 'W') {
            $edit['write'] = true;
        }
    }

    // Zapis zawsze wymaga odczytu.
    if ($edit['write']) {
        $edit['read'] = true;
    }
}

if (in_array($act, ['add', 'edit'], true)):
    $usernameVal = $edit['username'] ?? '';
    $menuVal = $edit['menu'] ?? '';
    $readVal = $edit['read'] ?? false;
    $writeVal = $edit['write'] ?? false;
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">

        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Użytkownik</label>
                <select name="username" class="form-control" required>
                    <option value="">-- Użytkownik --</option>

                    <?php foreach ($users as $user): ?>
                        <?php
                        $displayName = trim(
                            ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')
                        );

                        $label = $user['username'];
                        if ($displayName !== '') {
                            $label .= ' - ' . $displayName;
                        }
                        if (!empty($user['title'])) {
                            $label .= ' (' . $user['title'] . ')';
                        }
                        ?>
                        <option
                            value="<?php echo htmlspecialchars($user['username']); ?>"
                            <?php echo $user['username'] === $usernameVal ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Moduł / menu</label>

                <select name="menu" class="form-control" required>
                    <option value="">-- Moduł --</option>

                    <?php foreach ($menus as $menu): ?>
                        <option
                            value="<?php echo htmlspecialchars($menu); ?>"
                            <?php echo $menu === $menuVal ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($menu); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Uprawnienia</label>

                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="read"
                        id="read"
                        value="1"
                        <?php echo $readVal ? 'checked' : ''; ?>
                    >
                    <label class="form-check-label" for="read">Odczyt</label>
                </div>

                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="write"
                        id="write"
                        value="1"
                        <?php echo $writeVal ? 'checked' : ''; ?>
                        <?php echo !$readVal ? 'disabled' : ''; ?>
                    >
                    <label class="form-check-label" for="write">Zapis</label>
                </div>
            </div>

            <div class="col-md-12 mt-2">
                <button class="btn btn-success" type="submit">Zapisz</button>
                <a class="btn btn-secondary" href="uprawnienia.php">Anuluj</a>
            </div>
        </div>
    </form>

    <script>
        const readCheckbox = document.getElementById('read');
        const writeCheckbox = document.getElementById('write');

        function updateWriteCheckbox() {
            if (!readCheckbox || !writeCheckbox) return;

            if (!readCheckbox.checked) {
                writeCheckbox.checked = false;
                writeCheckbox.disabled = true;
            } else {
                writeCheckbox.disabled = false;
            }
        }

        if (readCheckbox && writeCheckbox) {
            readCheckbox.addEventListener('change', updateWriteCheckbox);
            updateWriteCheckbox();
        }
    </script>
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
                    $displayName = trim(
                        ($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')
                    );

                    if ($displayName === '') {
                        $displayName = '-';
                    }

                    $canRead = (bool)($r['can_read'] ?? false);
                    $canWrite = (bool)($r['can_write'] ?? false);

                    $permissionName = $canRead && $canWrite
                        ? 'Odczyt + Zapis'
                        : ($canRead ? 'Odczyt' : ($canWrite ? 'Zapis' : 'Brak'));
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['username']); ?></td>
                        <td><?php echo htmlspecialchars($displayName); ?></td>
                        <td><?php echo htmlspecialchars($r['title'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($r['menu']); ?></td>
                        <td><?php echo $canRead ? 'R' : ''; echo $canWrite ? ($canRead ? ' + W' : 'W') : ''; ?></td>
                        <td><?php echo htmlspecialchars($permissionName); ?></td>
                        <td>
                            <a
                                class="btn btn-sm btn-outline-primary"
                                href="uprawnienia.php?action=edit&username=<?php echo urlencode($r['username']); ?>&menu=<?php echo urlencode($r['menu']); ?>"
                            >
                                Edytuj
                            </a>

                            <a
                                class="btn btn-sm btn-outline-danger"
                                href="uprawnienia.php?action=delete&username=<?php echo urlencode($r['username']); ?>&menu=<?php echo urlencode($r['menu']); ?>"
                                onclick="return confirm('Usunąć uprawnienie?');"
                            >
                                Usuń
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
