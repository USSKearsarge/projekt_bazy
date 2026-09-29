<?php
// project_meta.php | Tabela: project_meta | Link: Ustawienia | dostępny dla wszystkich zalogowanych
require '../cfg.php';

if (!isset($_SESSION['zalogowany'])) {
    header('Location: logowanie.php');
    exit;
}

// Tabeli project_meta nie ma w baza_testowa.sql – strona nie może się wywalić, gdy jej brak
$rows = [];
$missing = false;
try {
    $stmt = $pdo->query('SELECT * FROM project_meta ORDER BY id');
    if ($stmt === false) {
        $missing = true;
    } else {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $missing = true;
}

include 'szablony/naglowek.php';
?>

<h2>Ustawienia (Tabela: PROJECT_META)</h2>
<p class="lead">Ustawienia projektu i metadane. Strona dostępna dla wszystkich zalogowanych.</p>

<?php if ($missing): ?>
    <div class="alert alert-warning">Tabela <code>project_meta</code> nie istnieje w bazie danych.</div>
<?php elseif (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <table class="table table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <?php foreach (array_keys($rows[0]) as $col): ?>
                    <th><?php echo htmlspecialchars($col); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <?php foreach ($r as $v): ?>
                        <td><?php echo htmlspecialchars((string)$v); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>
