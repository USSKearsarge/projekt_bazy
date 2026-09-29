<?php
// klienci.php | Tabela: customer | Wyszukiwanie, dodawanie, usuwanie
require '../cfg.php';
require 'csrf.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

// Uprawnienia jak w index.php (moduł sprzedaży: dostęp do 'hr' lub 'magazyn').
// Zapis/usuwanie tylko z typem uprawnień 'W'.
$perms = $_SESSION['perms'] ?? [];
if (!isset($perms['hr']) && !isset($perms['magazyn'])) {
    header('Location: index.php');
    exit;
}
$canManage = ($perms['hr'] ?? '') === 'W' || ($perms['magazyn'] ?? '') === 'W';

// Ochrona CSRF dla wszystkich żądań POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

$err = '';

// Dodawanie klienta
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $data = [
        'name'    => trim($_POST['name'] ?? ''),
        'nip'     => trim($_POST['nip'] ?? ''),
        'email'   => trim($_POST['email'] ?? ''),
        'phone'   => trim($_POST['phone'] ?? ''),
        'city'    => trim($_POST['city'] ?? ''),
        'country' => trim($_POST['country'] ?? ''),
    ];
    // limity długości z tabeli customer
    $max = ['name' => 50, 'nip' => 10, 'email' => 30, 'phone' => 25, 'city' => 30, 'country' => 30];

    if ($data['name'] === '' || $data['email'] === '') {
        $err = 'Nazwa i e-mail są wymagane.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $err = 'Nieprawidłowy adres e-mail.';
    } else {
        foreach ($max as $field => $len) {
            if (mb_strlen($data[$field]) > $len) {
                $err = "Pole '$field' może mieć maksymalnie $len znaków.";
                break;
            }
        }
    }

    if ($err === '') {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO customer (name, nip, email, phone, city, country, gender)
                 VALUES (:name, :nip, :email, :phone, :city, :country, 'M')"
            );
            $stmt->execute($data);
            header('Location: klienci.php');
            exit;
        } catch (PDOException $e) {
            $err = 'Nie udało się dodać klienta.';
        }
    }
}

// Usuwanie klienta (POST, nie GET)
if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM customer WHERE id = :id');
            $stmt->execute(['id' => $id]);
            header('Location: klienci.php');
            exit;
        } catch (PDOException $e) {
            // ord.customer_id ma klucz obcy do customer
            $err = 'Nie można usunąć klienta – ma powiązane zamówienia.';
        }
    }
}

// Lista klientów (bez password_hash, pesel itp.)
$cols = 'id, name, nip, email, phone, city, country, credit_rating';
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT $cols FROM customer
         WHERE name LIKE :q1 OR city LIKE :q2 OR nip LIKE :q3
         ORDER BY id DESC LIMIT 50"
    );
    $like = '%' . $search . '%';
    $stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like]);
} else {
    $stmt = $pdo->query("SELECT $cols FROM customer ORDER BY id DESC LIMIT 50");
}
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<h2>Lista Klientów</h2>
<p class="lead">Lista klientów sklepu.</p>

<?php if ($err !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<?php if ($canManage): ?>
<div class="card mb-4">
    <div class="card-header">Dodaj nowego klienta</div>
    <div class="card-body">
        <form method="post" class="row g-3">
        <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" maxlength="50" placeholder="Nazwa / Imię i Nazwisko *" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="nip" class="form-control" maxlength="10" placeholder="NIP">
            </div>
            <div class="col-md-3">
                <input type="email" name="email" class="form-control" maxlength="30" placeholder="Email *" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="phone" class="form-control" maxlength="25" placeholder="Telefon">
            </div>
            <div class="col-md-5">
                <input type="text" name="city" class="form-control" maxlength="30" placeholder="Miasto">
            </div>
            <div class="col-md-5">
                <input type="text" name="country" class="form-control" maxlength="30" placeholder="Kraj">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Dodaj</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<form method="get" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" placeholder="Szukaj po nazwie, mieście lub NIP..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn btn-primary">Szukaj</button>
    <?php if ($search !== ''): ?>
        <a href="klienci.php" class="btn btn-secondary">Reset</a>
    <?php endif; ?>
</form>

<?php if (count($customers) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nazwa / Nazwisko</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Miasto / Kraj</th>
                    <th>Ocena kredytowa</th>
                    <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?php echo (int)$c['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars((string)$c['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($c['nip'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars((string)$c['email']); ?></td>
                        <td><?php echo htmlspecialchars($c['phone'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars(($c['city'] ?? '') . ($c['country'] ? ', ' . $c['country'] : '')); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($c['credit_rating'] ?: 'Brak'); ?></span></td>
                        <?php if ($canManage): ?>
                            <td>
                                <form method="post" class="d-inline" onsubmit="return confirm('Na pewno usunąć klienta?');">
        <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                    <button class="btn btn-sm btn-outline-danger">Usuń</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
