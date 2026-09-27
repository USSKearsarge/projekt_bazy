<?php
// klienci.php
session_start();
require '../cfg.php';

// Obsługa dodawania nowego klienta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name    = trim($_POST['name'] ?? '');
    $nip     = trim($_POST['nip'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');

    if (!empty($name) && !empty($email)) {
        $stmt = $pdo->prepare("INSERT INTO customer (name, nip, email, phone, city, country, gender) VALUES (:name, :nip, :email, :phone, :city, :country, 'M')");
        $stmt->execute([
            'name' => $name, 'nip' => $nip, 'email' => $email,
            'phone' => $phone, 'city' => $city, 'country' => $country
        ]);
        header('Location: klienci.php');
        exit;
    }
}

// Obsługa usuwania klienta
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM customer WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header('Location: klienci.php');
    exit;
}

// Pobieranie listy klientów z opcją wyszukiwania
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE name LIKE :q OR city LIKE :q OR nip LIKE :q ORDER BY id DESC LIMIT 50");
    $stmt->execute(['q' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM customer ORDER BY id DESC LIMIT 50");
}
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Lista Klientów</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Lista Klientów</h2>
        <a href="pracownicy.php" class="btn btn-outline-secondary">Przejdź do pracowników</a>
    </div>

    <!-- Formularz dodawania -->
    <div class="card mb-4">
        <div class="card-header">Dodaj nowego klienta</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="add">
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control" placeholder="Nazwa / Imię i Nazwisko *" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="nip" class="form-control" placeholder="NIP">
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email *" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="phone" class="form-control" placeholder="Telefon">
                </div>
                <div class="col-md-5">
                    <input type="text" name="city" class="form-control" placeholder="Miasto">
                </div>
                <div class="col-md-5">
                    <input type="text" name="country" class="form-control" placeholder="Kraj">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Dodaj</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Wyszukiwarka -->
    <form method="GET" class="mb-3 d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Szukaj po nazwie, mieście lub NIP..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Szukaj</button>
        <?php if ($search): ?>
            <a href="klienci.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Tabela wyników -->
    <table class="table table-striped table-hover border">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nazwa / Nazwisko</th>
                <th>NIP</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Miasto / Kraj</th>
                <th>Ocena kredytowa</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <td><?= htmlspecialchars($c['nip'] ?: '-') ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                <td><?= htmlspecialchars($c['city'] . ($c['country'] ? ', ' . $c['country'] : '')) ?></td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($c['credit_rating'] ?: 'Brak') ?></span></td>
                <td>
                    <a href="klienci.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno usunąć?')">Usuń</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>