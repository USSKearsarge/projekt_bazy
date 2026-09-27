<?php
// Plik: ord.php | Tabela: ord | Link: Lista Zamówień | Kto widzi: ADMIN (1), KIEROWNIK (3)
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
if (!in_array($rola_id, [1, 3])) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM ord ORDER BY id");
$rows = $stmt->fetchAll();

include 'szablony/naglowek.php';
?>

<h2>Lista Zamówień (Tabela: ORD)</h2>
<p class="lead">Przegląd zamówień złożonych przez klientów.</p>

<?php if (count($rows) === 0): ?>
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
