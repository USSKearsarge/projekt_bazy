<?php
 //Załęski          Jakub S   do poprawy jeszcze

// Plik: project_meta.php | Tabela: project_meta | Link: Ustawienia | Kto widzi: Wszyscy (link "Ustawienia")
require '../cfg.php';

// Brak ograniczeń ról — dostępny dla zalogowanych użytkowników
// if(!isset($_SESSION['zalogowany'])){
//     header('Location: logowanie.php');
//     exit;
// }

$stmt = $pdo->query("SELECT * FROM project_meta ORDER BY id");
$rows = $stmt->fetchAll();

include 'szablony/naglowek.php';
?>

<h2>Ustawienia (Tabela: PROJECT_META)</h2>
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
