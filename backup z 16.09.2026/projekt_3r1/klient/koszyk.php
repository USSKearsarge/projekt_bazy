<?php
require '../cfg.php';
if(!isset($_SESSION['klient_id'])){
    header('Location: logowanie.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])){
    $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    if($pid && isset($_SESSION['cart'][$pid])){
        $current = (int)$_SESSION['cart'][$pid];
        if($current > 1){
            $_SESSION['cart'][$pid] = $current - 1;
        } else {
            unset($_SESSION['cart'][$pid]);
        }
        if(empty($_SESSION['cart'])) unset($_SESSION['cart']);
    }
    header('Location: koszyk.php');
    exit;
}

include 'szablony/naglowek.php';

echo '<h2>Twój koszyk</h2>';
$cart = $_SESSION['cart'] ?? [];
if(empty($cart)){
    echo '<p>Twój koszyk jest pusty.</p>';
} else {
    echo '<table class="table table-sm"><thead><tr><th>Produkt</th><th>Ilość</th><th>Cena</th><th>Razem</th><th>Akcje</th></tr></thead><tbody>';
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0,count($ids),'?'));
    $stmt = $pdo->prepare("SELECT id,nazwa,cena FROM produkty WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    $map = [];
    foreach($rows as $r) $map[$r['id']] = $r;
    $sum = 0;
    foreach($cart as $pid=>$q){
        $p = $map[$pid] ?? null;
        $price = $p ? $p['cena'] : 0;
        $line = $price * $q; $sum += $line;
        echo '<tr>';
        echo '<td>'.htmlspecialchars($p['nazwa'] ?? 'Unknown').'</td>';
        echo '<td>'.(int)$q.'</td>';
        echo '<td>'.number_format($price,2).'</td>';
        echo '<td>'.number_format($line,2).'</td>';
        echo "<td><form method='post' style='display:inline'><input type='hidden' name='product_id' value='".htmlspecialchars($pid)."'><button name='remove_item' class='btn btn-sm btn-outline-danger' type='submit'>Usuń</button></form></td>";
        echo '</tr>';
    }
    echo '</tbody><tfoot><tr><th></th><th></th><th>Razem</th><th>'.number_format($sum,2).'</th></tr></tfoot></table>';

    echo "<a href='zamowienie.php' class='btn btn-success'>Przejdź do zamówienia</a> ";
    echo "<form method='post' action='sklep.php' class='d-inline ms-2'><button name='clear_cart' class='btn btn-secondary' type='submit'>Wyczyść koszyk</button></form>";
}

include 'szablony/stopka.php';

?>
