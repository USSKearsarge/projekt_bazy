<?php
 //zrobił Mateusz Syska
require '../cfg.php';

require 'auth.php';
require_access(['hr', 'magazyn']);

$rows = $pdo->query("SELECT * FROM ord ORDER BY id")->fetchAll();

include 'szablony/naglowek.php';
?>

<h2>Lista Zamówień (Tabela: ord)</h2>
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
