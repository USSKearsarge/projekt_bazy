<?php
require '../cfg.php';
//jakub staniec
if (session_status() === PHP_SESSION_NONE) session_start();

$rola_id = $_SESSION['rola_id'] ?? 0;
$canManage = in_array($rola_id, [1, 2, 3]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManage) {
    $id = (int)($_POST['id'] ?? 0);

    $data = [
        trim($_POST['address'] ?? ''),
        trim($_POST['city'] ?? ''),
        trim($_POST['state'] ?? ''),
        trim($_POST['country'] ?? ''),
        trim($_POST['zip_code'] ?? ''),
        trim($_POST['phone'] ?? ''),
        !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null,
        !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null
    ];

    if ($id) {
        $data[] = $id;
        $pdo->prepare("UPDATE warehouse SET address=?,city=?,state=?,country=?,zip_code=?,phone=?,region_id=?,manager_id=? WHERE id=?")->execute($data);
    } else {
        $pdo->prepare("INSERT INTO warehouse(address,city,state,country,zip_code,phone,region_id,manager_id) VALUES(?,?,?,?,?,?,?,?)")->execute($data);
    }

    header("Location: magazyny.php");
    exit;
}

if (isset($_GET['delete']) && $canManage) {
    $pdo->prepare("DELETE FROM warehouse WHERE id=?")->execute([(int)$_GET['delete']]);
    header("Location: magazyny.php");
    exit;
}

$rows = $pdo->query("
    SELECT w.id AS ID,r.name AS Region,w.address AS Adres,w.city AS Miasto,
    w.state AS Stan,w.country AS Kraj,w.zip_code AS Kod,w.phone AS Telefon,
    CONCAT(e.first_name,' ',e.last_name) AS Kierownik
    FROM warehouse w
    LEFT JOIN region r ON w.region_id=r.id
    LEFT JOIN emp e ON w.manager_id=e.id
    ORDER BY w.id
")->fetchAll(PDO::FETCH_ASSOC);

include 'szablony/naglowek.php';
?>

<h2>Zarządzanie magazynami</h2>

<table class="table table-hover table-sm">
<tr class="table-dark">
<?php foreach(array_keys($rows[0]) as $c): ?><th><?=htmlspecialchars($c)?></th><?php endforeach; ?>
<?php if($canManage): ?><th>Akcje</th><?php endif; ?>
</tr>

<?php foreach($rows as $r): ?>
<tr>
<?php foreach($r as $v): ?><td><?=htmlspecialchars($v ?? '')?></td><?php endforeach; ?>

<?php if($canManage): ?>
<td>
<a class="btn btn-sm btn-outline-danger"
href="?delete=<?=$r['ID']?>"
onclick="return confirm('Usunąć magazyn?')">Usuń</a>
</td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>

<?php include 'szablony/stopka.php'; ?>