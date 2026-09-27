<?php
require '../cfg.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

// Głogowski
//Tabela emp w bazie nie posiada role_id, active, hire_date, title_id ani region_id.
// Aktywność pracownika wynika z end_date.

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);

    $last = trim($_POST['last_name'] ?? '');
    $first = trim($_POST['first_name'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['active'] ?? '' ? '2099-12-31' : date('Y-m-d');
    $comments = trim($_POST['comments'] ?? '');
    $manager_id = ($_POST['manager_id'] ?? '') !== '' ? (int)$_POST['manager_id'] : null;
    $title = trim($_POST['title'] ?? '');
    $dept_id = ($_POST['dept_id'] ?? '') !== '' ? (int)$_POST['dept_id'] : null;
    $salary = ($_POST['salary'] ?? '') !== '' ? (float)$_POST['salary'] : null;
    $commission_pct = ($_POST['commission_pct'] ?? '') !== '' ? (float)$_POST['commission_pct'] : null;
    $birth_date = ($_POST['birth_date'] ?? '') !== '' ? $_POST['birth_date'] : null;
    $gender = $_POST['gender'] ?? 'M';
    $pesel = trim($_POST['pesel'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $house_nr = trim($_POST['house_nr'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $employment_type = trim($_POST['employment_type'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($first === '' || $last === '' || $title === '' || $city === '' || $education === '') {
        $err = 'Uzupełnij wymagane pola.';
    } elseif (!in_array($gender, ['M', 'K'], true)) {
        $err = 'Nieprawidłowa płeć.';
    } elseif ($password !== '' && $password !== $password_confirm) {
        $err = 'Hasła nie są zgodne.';
    } elseif ($manager_id === $id && $id > 0) {
        $err = 'Pracownik nie może być swoim własnym przełożonym.';
    }

    if ($err === '') {
        if ($id > 0) {
            $sql = 'UPDATE emp SET
                last_name = ?, first_name = ?, start_date = ?, end_date = ?, comments = ?,
                manager_id = ?, title = ?, dept_id = ?, salary = ?, commission_pct = ?,
                birth_date = ?, gender = ?, pesel = ?, street = ?, house_nr = ?, zip_code = ?,
                city = ?, country = ?, nationality = ?, employment_type = ?, phone = ?, email = ?,
                education = ?, profession = ?, username = ?';

            $params = [
                $last, $first, $start_date ?: null, $end_date, $comments ?: null,
                $manager_id, $title, $dept_id, $salary, $commission_pct,
                $birth_date, $gender, $pesel ?: null, $street ?: null, $house_nr ?: null,
                $zip_code ?: null, $city, $country ?: null, $nationality ?: null,
                $employment_type ?: null, $phone ?: null, $email ?: null, $education,
                $profession ?: null, $username ?: null
            ];

            if ($password !== '') {
                $sql .= ', password_hash = ?';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= ' WHERE id = ?';
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;

            $stmt = $pdo->prepare('INSERT INTO emp
                (last_name, first_name, start_date, end_date, comments, manager_id, title, dept_id,
                 salary, commission_pct, birth_date, gender, pesel, street, house_nr, zip_code, city,
                 country, nationality, employment_type, phone, email, education, profession, username, password_hash)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

            $stmt->execute([
                $last, $first, $start_date ?: null, $end_date, $comments ?: null,
                $manager_id, $title, $dept_id, $salary, $commission_pct,
                $birth_date, $gender, $pesel ?: null, $street ?: null, $house_nr ?: null,
                $zip_code ?: null, $city, $country ?: null, $nationality ?: null,
                $employment_type ?: null, $phone ?: null, $email ?: null, $education,
                $profession ?: null, $username ?: null, $hash
            ]);
        }

        header('Location: emp.php');
        exit;
    }
}

if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $did = (int)$_GET['id'];

    if ($did > 0) {
        // Jeżeli inni pracownicy lub inne tabele wskazują na tego pracownika,
        // baza może zablokować usunięcie przez klucz obcy.
        $stmt = $pdo->prepare('DELETE FROM emp WHERE id = ?');
        $stmt->execute([$did]);
    }

    header('Location: emp.php');
    exit;
}

// Dane pracowników zgodne z rzeczywistą strukturą tabeli emp.
$stmt = $pdo->query("SELECT e.*, d.name AS dept_name, m.first_name AS manager_first_name,
                            m.last_name AS manager_last_name
                     FROM emp e
                     LEFT JOIN dept d ON e.dept_id = d.id
                     LEFT JOIN emp m ON e.manager_id = m.id
                     ORDER BY e.id");
$pracownicy = $stmt->fetchAll(PDO::FETCH_ASSOC);

$depts = $pdo->query('SELECT id, name FROM dept ORDER BY name, id')->fetchAll(PDO::FETCH_ASSOC);
$titles = $pdo->query('SELECT name, salary_min, salary_max FROM title ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$managers = $pdo->query('SELECT id, first_name, last_name FROM emp ORDER BY last_name, first_name, id')->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Lista Pracowników (Tabela: EMP)</h2>
    <div>
        <a href="emp.php?action=add" class="btn btn-success btn-sm">Dodaj pracownika</a>
    </div>
</div>
<p class="lead">Zarządzanie pracownikami zgodnie ze strukturą tabeli EMP.</p>

<?php if ($err !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if (isset($_GET['action']) && in_array($_GET['action'], ['add', 'edit'], true)):
    $act = $_GET['action'];
    $rec = [
        'id' => '', 'last_name' => '', 'first_name' => '', 'start_date' => '', 'end_date' => '2099-12-31',
        'comments' => '', 'manager_id' => '', 'title' => '', 'dept_id' => '', 'salary' => '',
        'commission_pct' => '', 'birth_date' => '', 'gender' => 'M', 'pesel' => '', 'street' => '',
        'house_nr' => '', 'zip_code' => '', 'city' => '', 'country' => '', 'nationality' => '',
        'employment_type' => '', 'phone' => '', 'email' => '', 'education' => '', 'profession' => '',
        'username' => '', 'password_hash' => ''
    ];

    if ($act === 'edit' && isset($_GET['id'])) {
        $eid = (int)$_GET['id'];
        $s = $pdo->prepare('SELECT * FROM emp WHERE id = ?');
        $s->execute([$eid]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $rec = array_merge($rec, $r);
        }
    }

    $active = empty($rec['end_date']) || $rec['end_date'] >= date('Y-m-d');
    $startValue = !empty($rec['start_date']) ? date('Y-m-d\\TH:i', strtotime($rec['start_date'])) : '';
?>
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($rec['id']); ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Imię *</label>
                <input name="first_name" class="form-control" value="<?php echo htmlspecialchars($rec['first_name']); ?>" required maxlength="25">
            </div>
            <div class="col-md-6 mb-3">
                <label>Nazwisko *</label>
                <input name="last_name" class="form-control" value="<?php echo htmlspecialchars($rec['last_name']); ?>" required maxlength="25">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Stanowisko *</label>
                <select name="title" class="form-control" required>
                    <option value="">-- wybierz --</option>
                    <?php foreach ($titles as $t): ?>
                        <option value="<?php echo htmlspecialchars($t['name']); ?>" <?php echo $t['name'] == $rec['title'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>Dział</label>
                <select name="dept_id" class="form-control">
                    <option value="">-- brak --</option>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo $d['id'] == $rec['dept_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>Przełożony</label>
                <select name="manager_id" class="form-control">
                    <option value="">-- brak --</option>
                    <?php foreach ($managers as $m): ?>
                        <?php if ((int)$m['id'] === (int)$rec['id']) continue; ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo $m['id'] == $rec['manager_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name'] . ' (#' . $m['id'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Data rozpoczęcia</label>
                <input type="datetime-local" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startValue); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Data urodzenia</label>
                <input type="date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($rec['birth_date']); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Płeć *</label>
                <select name="gender" class="form-control" required>
                    <option value="M" <?php echo $rec['gender'] === 'M' ? 'selected' : ''; ?>>M</option>
                    <option value="K" <?php echo $rec['gender'] === 'K' ? 'selected' : ''; ?>>K</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>PESEL</label>
                <input name="pesel" class="form-control" value="<?php echo htmlspecialchars($rec['pesel']); ?>" maxlength="11">
            </div>
            <div class="col-md-4 mb-3">
                <label>Telefon</label>
                <input name="phone" class="form-control" value="<?php echo htmlspecialchars($rec['phone']); ?>" maxlength="15">
            </div>
            <div class="col-md-4 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($rec['email']); ?>" maxlength="30">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Ulica</label>
                <input name="street" class="form-control" value="<?php echo htmlspecialchars($rec['street']); ?>" maxlength="25">
            </div>
            <div class="col-md-2 mb-3">
                <label>Nr domu</label>
                <input name="house_nr" class="form-control" value="<?php echo htmlspecialchars($rec['house_nr']); ?>" maxlength="10">
            </div>
            <div class="col-md-2 mb-3">
                <label>Kod pocztowy</label>
                <input name="zip_code" class="form-control" value="<?php echo htmlspecialchars($rec['zip_code']); ?>" maxlength="12">
            </div>
            <div class="col-md-4 mb-3">
                <label>Miasto *</label>
                <input name="city" class="form-control" value="<?php echo htmlspecialchars($rec['city']); ?>" required maxlength="30">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Kraj</label>
                <input name="country" class="form-control" value="<?php echo htmlspecialchars($rec['country']); ?>" maxlength="32">
            </div>
            <div class="col-md-4 mb-3">
                <label>Obywatelstwo</label>
                <input name="nationality" class="form-control" value="<?php echo htmlspecialchars($rec['nationality']); ?>" maxlength="15">
            </div>
            <div class="col-md-4 mb-3">
                <label>Rodzaj zatrudnienia</label>
                <input name="employment_type" class="form-control" value="<?php echo htmlspecialchars($rec['employment_type']); ?>" maxlength="32">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Wynagrodzenie</label>
                <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo htmlspecialchars($rec['salary']); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Procent prowizji</label>
                <input type="number" step="0.01" min="0" max="99.99" name="commission_pct" class="form-control" value="<?php echo htmlspecialchars($rec['commission_pct']); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Wykształcenie *</label>
                <input name="education" class="form-control" value="<?php echo htmlspecialchars($rec['education']); ?>" required maxlength="48">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Zawód</label>
                <input name="profession" class="form-control" value="<?php echo htmlspecialchars($rec['profession']); ?>" maxlength="30">
            </div>
            <div class="col-md-6 mb-3">
                <label>Login</label>
                <input name="username" class="form-control" value="<?php echo htmlspecialchars($rec['username']); ?>" maxlength="12">
            </div>
        </div>

        <div class="mb-3">
            <label>Komentarz</label>
            <textarea name="comments" class="form-control" maxlength="255"><?php echo htmlspecialchars($rec['comments']); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Hasło <?php echo $act === 'edit' ? '(pozostaw puste, aby nie zmieniać)' : ''; ?></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Powtórz hasło</label>
                <input type="password" name="password_confirm" class="form-control">
            </div>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="active" value="1" class="form-check-input" id="active" <?php echo $active ? 'checked' : ''; ?>>
            <label class="form-check-label" for="active">Aktywny</label>
        </div>

        <button class="btn btn-primary">Zapisz</button>
        <a href="emp.php" class="btn btn-secondary">Anuluj</a>
    </form>
<?php else: ?>

<table class="table table-hover table-sm">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Imię Nazwisko</th>
            <th>Stanowisko</th>
            <th>Dział</th>
            <th>Email</th>
            <th>Miasto</th>
            <th>Telefon</th>
            <th>Data rozpoczęcia</th>
            <th>Aktywny</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pracownicy as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['id']); ?></td>
                <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                <td><?php echo htmlspecialchars($p['title']); ?></td>
                <td><?php echo htmlspecialchars($p['dept_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($p['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($p['city']); ?></td>
                <td><?php echo htmlspecialchars($p['phone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($p['start_date'] ?? ''); ?></td>
                <td><?php echo (!empty($p['end_date']) && $p['end_date'] < date('Y-m-d')) ? 'Nie' : 'Tak'; ?></td>
                <td class="text-nowrap">
                    <a href="emp.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Edytuj</a>
                    <a href="emp.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć tego pracownika?');">Usuń</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
