<?php
require '../cfg.php';

$err = '';
//Jakub Staniec
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| DANE DO SELECTÓW
|--------------------------------------------------------------------------
*/

$depts = $pdo->query("
    SELECT id, name
    FROM dept
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$regions = $pdo->query("
    SELECT id, name
    FROM region
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$titles = $pdo->query("
    SELECT name, salary_min, salary_max
    FROM title
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| ZAPIS PRACOWNIKA
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'save'
) {

    $id = isset($_POST['id']) && $_POST['id'] !== ''
        ? (int)$_POST['id']
        : null;

    $imie = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // W tej bazie stanowisko jest przechowywane w emp.title
    $stanowisko = trim($_POST['stanowisko'] ?? '');

    $dzial = !empty($_POST['dzial_id'])
        ? (int)$_POST['dzial_id']
        : null;

    $region = !empty($_POST['region_id'])
        ? (int)$_POST['region_id']
        : null;

    $telefon = trim($_POST['telefon'] ?? '');
    $ulica = trim($_POST['ulica'] ?? '');
    $nr_domu = trim($_POST['nr_domu'] ?? '');
    $kod_pocztowy = trim($_POST['kod_pocztowy'] ?? '');
    $miasto = trim($_POST['miasto'] ?? '');
    $kraj = trim($_POST['kraj'] ?? '');

    $data_zatrudnienia = !empty($_POST['data_zatrudnienia'])
        ? $_POST['data_zatrudnienia']
        : null;

    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | WALIDACJA
    |--------------------------------------------------------------------------
    */

    if ($imie === '') {
        $err = 'Podaj imię.';
    }

    if ($err === '' && $nazwisko === '') {
        $err = 'Podaj nazwisko.';
    }

    if ($err === '' && $email === '') {
        $err = 'Podaj adres e-mail.';
    }

    if ($err === '' && $stanowisko === '') {
        $err = 'Wybierz stanowisko.';
    }

    if ($err === '' && $password !== $password_confirm) {
        $err = 'Hasła nie są zgodne.';
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($err === '' && $id !== null) {

        /*
         * Sprawdzamy czy pracownik istnieje.
         */
        $check = $pdo->prepare("
            SELECT *
            FROM emp
            WHERE id = ?
        ");

        $check->execute([$id]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            $err = 'Nie znaleziono pracownika o podanym ID.';
        }
    }

    if ($err === '' && $id !== null) {

        $sql = "
            UPDATE emp SET
                first_name = ?,
                last_name = ?,
                email = ?,
                title = ?,
                dept_id = ?,
                phone = ?,
                street = ?,
                house_nr = ?,
                zip_code = ?,
                city = ?,
                country = ?,
                start_date = ?
        ";

        $params = [
            $imie,
            $nazwisko,
            $email,
            $stanowisko,
            $dzial,
            $telefon,
            $ulica,
            $nr_domu,
            $kod_pocztowy,
            $miasto,
            $kraj,
            $data_zatrudnienia
        ];

        /*
         * Hasło zmieniamy tylko jeśli użytkownik coś wpisał.
         */
        if ($password !== '') {

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql .= ", password_hash = ?";
            $params[] = $hash;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        try {

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            header('Location: pracownicy.php');
            exit;

        } catch (PDOException $e) {

            $err = 'Błąd podczas zapisywania pracownika: '
                . $e->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    if ($err === '' && $id === null) {

        $hash = '';

        if ($password !== '') {
            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
        }

        /*
         * W tabeli emp kolumny gender oraz education są NOT NULL.
         * Dlatego nadajemy bezpieczne wartości domyślne.
         */
        $sql = "
            INSERT INTO emp
            (
                last_name,
                first_name,
                start_date,
                title,
                dept_id,
                phone,
                email,
                education,
                street,
                house_nr,
                zip_code,
                city,
                country,
                password_hash,
                gender
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        $params = [
            $nazwisko,
            $imie,
            $data_zatrudnienia,
            $stanowisko,
            $dzial,
            $telefon,
            $email,
            'Brak danych',
            $ulica,
            $nr_domu,
            $kod_pocztowy,
            $miasto,
            $kraj,
            $hash,
            'M'
        ];

        try {

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            header('Location: pracownicy.php');
            exit;

        } catch (PDOException $e) {

            $err = 'Błąd podczas dodawania pracownika: '
                . $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| USUWANIE
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['action'])
    && $_GET['action'] === 'delete'
    && isset($_GET['id'])
) {

    $id = (int)$_GET['id'];

    try {

        $stmt = $pdo->prepare("
            DELETE FROM emp
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        header('Location: pracownicy.php');
        exit;

    } catch (PDOException $e) {

        $err = 'Nie można usunąć pracownika: '
            . $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| LISTA PRACOWNIKÓW
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->query("
        SELECT
            e.id,
            e.first_name,
            e.last_name,
            e.email,
            e.title,
            e.dept_id,
            e.phone,
            e.start_date,
            e.end_date,
            e.street,
            e.house_nr,
            e.zip_code,
            e.city,
            e.country,
            d.name AS dept_name
        FROM emp e
        LEFT JOIN dept d
            ON e.dept_id = d.id
        ORDER BY e.id
    ");

    $pracownicy = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        'Błąd podczas pobierania pracowników: '
        . e($e->getMessage())
    );
}

/*
|--------------------------------------------------------------------------
| FORMULARZ EDYCJI / DODAWANIA
|--------------------------------------------------------------------------
*/

$showForm = isset($_GET['action'])
    && in_array($_GET['action'], ['add', 'edit'], true);

$rec = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'title' => '',
    'dept_id' => '',
    'phone' => '',
    'start_date' => '',
    'street' => '',
    'house_nr' => '',
    'zip_code' => '',
    'city' => '',
    'country' => ''
];

if ($showForm && $_GET['action'] === 'edit') {

    $eid = isset($_GET['id'])
        ? (int)$_GET['id']
        : 0;

    $stmt = $pdo->prepare("
        SELECT *
        FROM emp
        WHERE id = ?
    ");

    $stmt->execute([$eid]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {

        $rec = array_merge(
            $rec,
            $row
        );

    } else {

        $err = 'Nie znaleziono pracownika.';
        $showForm = false;
    }
}

include 'szablony/naglowek.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">

    <h2>
        Lista pracowników
    </h2>

    <div>

        <a
            href="pracownicy.php?action=add"
            class="btn btn-success btn-sm"
        >
            Dodaj pracownika
        </a>

    </div>

</div>

<p class="lead">
    Zarządzanie pracownikami zapisanymi w tabeli <strong>emp</strong>.
</p>

<?php if ($err !== ''): ?>

    <div class="alert alert-danger">
        <?php echo e($err); ?>
    </div>

<?php endif; ?>


<?php if ($showForm): ?>

<form method="post" class="mb-4">

    <input
        type="hidden"
        name="action"
        value="save"
    >

    <input
        type="hidden"
        name="id"
        value="<?php echo e($rec['id']); ?>"
    >

    <div class="row">

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Imię
            </label>

            <input
                type="text"
                name="imie"
                class="form-control"
                value="<?php echo e($rec['first_name']); ?>"
                required
            >

        </div>


        <div class="col-md-6 mb-3">

            <label class="form-label">
                Nazwisko
            </label>

            <input
                type="text"
                name="nazwisko"
                class="form-control"
                value="<?php echo e($rec['last_name']); ?>"
                required
            >

        </div>

    </div>


    <div class="mb-3">

        <label class="form-label">
            Email
        </label>

        <input
            type="email"
            name="email"
            class="form-control"
            value="<?php echo e($rec['email']); ?>"
            required
        >

    </div>


    <div class="row">

        <div class="col-md-4 mb-3">

            <label class="form-label">
                Stanowisko
            </label>

            <select
                name="stanowisko"
                class="form-control"
                required
            >

                <option value="">
                    --
                </option>

                <?php foreach ($titles as $t): ?>

                    <option
                        value="<?php echo e($t['name']); ?>"
                        <?php
                        if ($t['name'] === $rec['title']) {
                            echo 'selected';
                        }
                        ?>
                    >
                        <?php echo e($t['name']); ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="col-md-4 mb-3">

            <label class="form-label">
                Dział
            </label>

            <select
                name="dzial_id"
                class="form-control"
            >

                <option value="">
                    --
                </option>

                <?php foreach ($depts as $d): ?>

                    <option
                        value="<?php echo e($d['id']); ?>"
                        <?php
                        if ((string)$d['id'] === (string)$rec['dept_id']) {
                            echo 'selected';
                        }
                        ?>
                    >
                        <?php echo e($d['name']); ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="col-md-4 mb-3">

            <label class="form-label">
                Data zatrudnienia
            </label>

            <input
                type="date"
                name="data_zatrudnienia"
                class="form-control"
                value="<?php
                    echo e(
                        !empty($rec['start_date'])
                            ? substr($rec['start_date'], 0, 10)
                            : ''
                    );
                ?>"
            >

        </div>

    </div>


    <div class="row">

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Telefon
            </label>

            <input
                type="text"
                name="telefon"
                class="form-control"
                value="<?php echo e($rec['phone']); ?>"
            >

        </div>


        <div class="col-md-6 mb-3">

            <label class="form-label">
                Kraj
            </label>

            <input
                type="text"
                name="kraj"
                class="form-control"
                value="<?php echo e($rec['country']); ?>"
            >

        </div>

    </div>


    <div class="row">

        <div class="col-md-6 mb-3">

            <label class="form-label">
                Ulica
            </label>

            <input
                type="text"
                name="ulica"
                class="form-control"
                value="<?php echo e($rec['street']); ?>"
            >

        </div>


        <div class="col-md-2 mb-3">

            <label class="form-label">
                Nr
            </label>

            <input
                type="text"
                name="nr_domu"
                class="form-control"
                value="<?php echo e($rec['house_nr']); ?>"
            >

        </div>


        <div class="col-md-2 mb-3">

            <label class="form-label">
                Kod
            </label>

            <input
                type="text"
                name="kod_pocztowy"
                class="form-control"
                value="<?php echo e($rec['zip_code']); ?>"
            >

        </div>


        <div class="col-md-2 mb-3">

            <label class="form-label">
                Miasto
            </label>

            <input
                type="text"
                name="miasto"
                class="form-control"
                value="<?php echo e($rec['city']); ?>"
            >

        </div>

    </div>


    <div class="mb-3">

        <label class="form-label">
            Nowe hasło
        </label>

        <input
            type="password"
            name="password"
            class="form-control"
        >

        <small class="text-muted">
            Przy edycji zostaw puste, jeśli hasło ma pozostać bez zmian.
        </small>

    </div>


    <div class="mb-3">

        <label class="form-label">
            Powtórz hasło
        </label>

        <input
            type="password"
            name="password_confirm"
            class="form-control"
        >

    </div>


    <button
        type="submit"
        class="btn btn-primary"
    >
        Zapisz
    </button>

    <a
        href="pracownicy.php"
        class="btn btn-secondary"
    >
        Anuluj
    </a>

</form>


<?php else: ?>


<div class="table-responsive">

<table class="table table-hover table-sm">

    <thead class="table-dark">

        <tr>

            <th>ID</th>

            <th>Imię i nazwisko</th>

            <th>Email</th>

            <th>Stanowisko</th>

            <th>Dział</th>

            <th>Telefon</th>

            <th>Data zatrudnienia</th>

            <th>Miasto</th>

            <th>Akcje</th>

        </tr>

    </thead>


    <tbody>

        <?php foreach ($pracownicy as $p): ?>

            <tr>

                <td>
                    <?php echo e($p['id']); ?>
                </td>


                <td>
                    <?php
                    echo e(
                        $p['first_name']
                        . ' '
                        . $p['last_name']
                    );
                    ?>
                </td>


                <td>
                    <?php echo e($p['email']); ?>
                </td>


                <td>
                    <?php echo e($p['title']); ?>
                </td>


                <td>
                    <?php echo e($p['dept_name']); ?>
                </td>


                <td>
                    <?php echo e($p['phone']); ?>
                </td>


                <td>
                    <?php
                    echo e(
                        !empty($p['start_date'])
                            ? substr($p['start_date'], 0, 10)
                            : ''
                    );
                    ?>
                </td>


                <td>
                    <?php echo e($p['city']); ?>
                </td>


                <td>

                    <a
                        href="pracownicy.php?action=edit&id=<?php echo e($p['id']); ?>"
                        class="btn btn-sm btn-primary"
                    >
                        Edytuj
                    </a>


                    <a
                        href="pracownicy.php?action=delete&id=<?php echo e($p['id']); ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Usunąć tego pracownika?');"
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


<?php
include 'szablony/stopka.php';
?>