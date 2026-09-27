<?php
 
require '../cfg.php';

if(!isset($_SESSION['zalogowany'])){
    header('Location: logowanie.php');
    exit;
}

$rola_id = $_SESSION['rola_id'] ?? 0;
if (!in_array($rola_id, [1, 2, 3])) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM klienci ORDER BY id");
$rows = $stmt->fetchAll();

$canManage = in_array($rola_id, [1, 3]);

include 'szablony/naglowek.php';
?>

<div class="text-center mb-4">
    <h2>Klienci (Tabela: KLIENCI)</h2>
    <p class="lead">Lista klientów sklepu.</p>
</div>

<?php if (count($rows) === 0): ?>
    <p>Brak rekordów.</p>
<?php else: ?>
    <?php
    // Build friendly headers from the first row keys and remove sensitive columns
    $firstKeys = array_keys($rows[0]);
    // Exclude password_hash from display
    $filtered = [];
    foreach ($firstKeys as $k) {
        if ($k === 'password_hash') continue;
        $filtered[] = $k;
    }
    $firstKeys = $filtered;

    $labelMap = [
        'id' => 'ID',
        'imie' => 'Imię',
        'nazwisko' => 'Nazwisko',
        'email' => 'Email',
        'telefon' => 'Telefon',
        'adres' => 'Adres',
        'data_rej' => 'Data rejestracji',
    ];
    ?>

<div class="container-fluid px-2">
    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead class="table-dark">
                <tr>
                    <?php foreach ($firstKeys as $col): ?>
                        <th><?php echo htmlspecialchars($labelMap[$col] ?? ucfirst($col)); ?></th>
                    <?php endforeach; ?>
                    <?php if ($canManage): ?><th>Akcje</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <?php foreach ($firstKeys as $k): ?>
                            <?php $v = $r[$k] ?? ''; ?>
                            <td>
                                <?php
                                if ($k === 'data_rej' && !empty($v)) {
                                    $dt = strtotime($v);
                                    echo $dt ? htmlspecialchars(date('Y-m-d H:i', $dt)) : htmlspecialchars($v);
                                } else {
                                    echo htmlspecialchars((string)$v);
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if ($canManage): ?>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="klienci.php?action=edit&id=<?php echo urlencode($r['id']); ?>">Edytuj</a>
                                <a class="btn btn-sm btn-outline-danger" href="klienci.php?action=delete&id=<?php echo urlencode($r['id']); ?>" onclick="return confirm('Na pewno usunąć klienta?');">Usuń</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php include 'szablony/stopka.php'; ?>