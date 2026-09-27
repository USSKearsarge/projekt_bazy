<?php
 
require '../cfg.php';

 
if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM ustawienia_projektu ORDER BY id");
$rows = $stmt->fetchAll();

include 'szablony/naglowek.php';
?>

<h2>Ustawienia (Tabela: USTAWIENIA_PROJEKTU)</h2>
<p class="lead">Ustawienia projektu i metadane. Strona dostępna dla wszystkich zalogowanych.</p>

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
