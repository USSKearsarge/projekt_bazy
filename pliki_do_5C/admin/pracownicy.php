<?php
// pracownicy.php
session_start();
require '../cfg.php';

// Obsługa dodawania pracownika
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $title      = trim($_POST['title'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $salary     = (float)($_POST['salary'] ?? 0);
    $dept_id    = !empty($_POST['dept_id']) ? (int)$_POST['dept_id'] : NULL;

    if (!empty($first_name) && !empty($last_name)) {
        $stmt = $pdo->prepare("INSERT INTO emp (menu_id, first_name, last_name, title, email, city, salary, dept_id, start_date, gender) 
                               VALUES ('', :first_name, :last_name, :title, :email, :city, :salary, :dept_id, NOW(), 'M')");
        $stmt->execute([
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'title'      => $title,
            'email'      => $email,
            'city'       => $city,
            'salary'     => $salary,
            'dept_id'    => $dept_id
        ]);
        header('Location: pracownicy.php');
        exit;
    }
}

// Obsługa usuwania pracownika
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM emp WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header('Location: pracownicy.php');
    exit;
}

// Pobieranie listy departamentów do formularza
$departments = $pdo->query("SELECT id, name FROM dept ORDER BY name ASC")->fetchAll();

// Pobieranie pracowników wraz z nazwą departamentu
$query = "SELECT e.*, d.name AS dept_name 
          FROM emp e 
          LEFT JOIN dept d ON e.dept_id = d.id 
          ORDER BY e.id DESC";
$employees = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Katalog Pracowników</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Katalog Pracowników</h2>
        <div>
            <a href="klienci.php" class="btn btn-outline-secondary">Zarządzaj klientami</a>
            <a href="logowanie.php" class="btn btn-outline-danger ms-2">Wyloguj</a>
        </div>
    </div>

    <!-- Formularz dodawania pracownika -->
    <div class="card mb-4">
        <div class="card-header">Rejestracja Nowego Pracownika</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="add">
                <div class="col-md-3">
                    <input type="text" name="first_name" class="form-control" placeholder="Imię *" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="last_name" class="form-control" placeholder="Nazwisko *" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="title" class="form-control" placeholder="Stanowisko">
                </div>
                <div class="col-md-3">
                    <select name="dept_id" class="form-select">
                        <option value="">-- Wybierz Dział --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> (ID: <?= $d['id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="email" name="email" class="form-control" placeholder="Email">
                </div>
                <div class="col-md-3">
                    <input type="text" name="city" class="form-control" placeholder="Miasto" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="salary" class="form-control" placeholder="Wynagrodzenie (PLN)">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Zapisz</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela wyników -->
    <table class="table table-striped table-hover border">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Pracownik</th>
                <th>Stanowisko</th>
                <th>Dział</th>
                <th>Email</th>
                <th>Miasto</th>
                <th>Pensja</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $e): ?>
            <tr>
                <td><?= $e['id'] ?></td>
                <td><strong><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></strong></td>
                <td><?= htmlspecialchars($e['title'] ?: '-') ?></td>
                <td><?= htmlspecialchars($e['dept_name'] ?: 'Brak') ?></td>
                <td><?= htmlspecialchars($e['email'] ?: '-') ?></td>
                <td><?= htmlspecialchars($e['city']) ?></td>
                <td><?= number_format($e['salary'], 2, ',', ' ') ?> zł</td>
                <td>
                    <a href="pracownicy.php?delete=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno usunąć pracownika?')">Usuń</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>