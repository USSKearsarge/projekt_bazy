<?php
require '../cfg.php';
if(!isset($_SESSION['klient_id'])){
    header('Location: logowanie.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if(empty($cart)){
    header('Location: koszyk.php');
    exit;
}

$msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])){
    $adres = trim($_POST['adres'] ?? '');
    $platnosc = $_POST['platnosc'] ?? '';
    if($adres === '' || $platnosc === ''){
        $msg = 'Podaj adres i wybierz sposób płatności.';
    } else {
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0,count($ids),'?'));
        $stmt = $pdo->prepare("SELECT id, suggested_price FROM product WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $total = 0.0;
        foreach($cart as $pid=>$q){ $price = $products[$pid] ?? 0; $total += $price * $q; }
        $stmt = $pdo->prepare('INSERT INTO ord (customer_id, date_ordered, total, payment_type, status) VALUES (?,?,?,?,?)');
        $paymentMap = [
            'BLIK' => 'Blik',
            'Przelew' => 'Bank transfer',
            'Gotówka przy odbiorze' => 'Cash'
        ];
        $paymentType = $paymentMap[$platnosc] ?? null;

        $stmt->execute([
            $_SESSION['klient_id'], date('Y-m-d H:i:s'), $total, $paymentType, 'Nowe'
        ]);
        $ord_id = $pdo->lastInsertId();
        $itemStmt = $pdo->prepare('INSERT INTO item (ord_id,item_id,product_id,price,gross,quantity,quantity_shipped) VALUES (?,?,?,?,?,?,?)');
        $i = 1;
        foreach($cart as $pid=>$q){
            $price = $products[$pid] ?? 0;
            $itemStmt->execute([$ord_id, $i, $pid, $price, $price, $q, 0]);
            $i++;
        }
        unset($_SESSION['cart']);
        $msg = 'Zamówienie zostało złożone (ID: '.$ord_id.').';
    }
}

include 'szablony/naglowek.php';

// Pobierz adres klienta z bazy
$stmt = $pdo->prepare('SELECT street, zip_code, city, country FROM customer WHERE id=?');
$stmt->execute([$_SESSION['klient_id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
$userAdres = '';
if ($customer) {
    $parts = array_filter([
        $customer['street'] ?? '',
        $customer['zip_code'] ?? '',
        $customer['city'] ?? '',
        $customer['country'] ?? ''
    ]);
    $userAdres = implode(', ', $parts);
}
$adresVal = htmlspecialchars($_POST['adres'] ?? $userAdres);

if($msg) echo '<div class="alert alert-info">'.htmlspecialchars($msg).'</div>';

if(empty($msg)){
    echo '<h2>Podsumowanie zamówienia</h2>';
    echo '<table class="table table-sm"><thead><tr><th>Produkt</th><th>Ilość</th><th>Cena</th><th>Razem</th></tr></thead><tbody>';
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0,count($ids),'?'));
    $stmt = $pdo->prepare("SELECT id, name, suggested_price FROM product WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    $map = [];
    foreach($rows as $r) $map[$r['id']] = $r;
    $sum = 0;
    foreach($cart as $pid=>$q){
        $p = $map[$pid] ?? null;
        $price = $p ? (float)$p['suggested_price'] : 0;
        $line = $price * $q; $sum += $line;
        echo '<tr><td>'.htmlspecialchars($p['name'] ?? 'Unknown').'</td><td>'.(int)$q.'</td><td>'.number_format($price,2).'</td><td>'.number_format($line,2).'</td></tr>';
    }
    echo '</tbody><tfoot><tr><th></th><th></th><th>Razem</th><th>'.number_format($sum,2).'</th></tr></tfoot></table>';
    echo '<form method="post" class="mb-3">';
    echo '<div class="mb-2"><label for="adres" class="form-label">Adres dostawy:</label>';
    echo '<input type="text" name="adres" id="adres" class="form-control" value="'.$adresVal.'" required>';
    echo '<div class="form-text">Możesz zmienić adres lub zostawić domyślny z konta.</div></div>';
    echo '<div class="mb-2"><label class="form-label">Sposób płatności:</label><select name="platnosc" class="form-select" required>';
    echo '<option value="">Wybierz...</option>';
    echo '<option value="BLIK"'.(isset($_POST['platnosc'])&&$_POST['platnosc']=='BLIK'?' selected':'').'>BLIK</option>';
    echo '<option value="Przelew"'.(isset($_POST['platnosc'])&&$_POST['platnosc']=='Przelew'?' selected':'').'>Przelew</option>';
    echo '<option value="Gotówka przy odbiorze"'.(isset($_POST['platnosc'])&&$_POST['platnosc']=='Gotówka przy odbiorze'?' selected':'').'>Gotówka przy odbiorze</option>';
    echo '</select></div>';
    echo '<button name="place_order" class="btn btn-success" type="submit">Złóż zamówienie</button>';
    echo '</form>';
}

include 'szablony/stopka.php';
?>
