<?php
// Plik: warehouse.php | Tabela: warehouse | Link: Zarządzanie Magazynami | Kto widzi: ADMIN (1), MAGAZYN (4)
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
if (!in_array($rola_id, [1, 4])) {
    header('Location: index.php');
    exit;
}

// Zmodyfikowane zapytanie, które pobiera powiązane dane z tabel region i emp (zamiast wyświetlać samo ID)
$sql = "SELECT 
            w.id AS 'ID', 
            r.name AS 'Region', 
            w.address AS 'Adres', 
            w.city AS 'Miasto', 
            w.state AS 'Stan / Województwo', 
            w.country AS 'Kraj', 
            w.zip_code AS 'Kod pocztowy', 
            w.phone AS 'Telefon', 
            CONCAT(e.first_name, ' ', e.last_name) AS 'Kierownik'
        FROM warehouse w
        LEFT JOIN region r ON w.region_id = r.id
        LEFT JOIN emp e ON w.manager_id = e.id
        ORDER BY w.id";

$stmt = $pdo->query($sql);
// Wymuszenie formatu asocjacyjnego, aby dynamiczna pętla tabeli nie generowała podwójnych kolumn (indeksów numerycznych)
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<h2>Zarządzanie Magazynami (Tabela: WAREHOUSE)</h2>
<p class="lead">Lista magazynów, lokalizacji oraz przypisanych kierowników.</p>

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