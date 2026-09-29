<?php
require '../cfg.php';
if(!isset($_SESSION['klient_id'])){
    header('Location: logowanie.php');
    exit;
}
//edytował Jakub Piotrowski

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['add_to_cart'])){
        $pid = (int)$_POST['product_id'];
        $qty = max(1,(int)($_POST['quantity']??1));
        if(!isset($_SESSION['cart'])) $_SESSION['cart']=[];
        if(isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid] += $qty; else $_SESSION['cart'][$pid] = $qty;
    }
    if(isset($_POST['clear_cart'])){ unset($_SESSION['cart']); }
}

include 'szablony/naglowek.php';
//edytował Jakub Piotrowski

echo '<h2>Produkty (Witaj ' . htmlspecialchars($_SESSION['klient_imie']) . ')</h2>';
if(isset($msg)) echo '<div class="alert alert-info">'.htmlspecialchars($msg).'</div>';

echo '<form method="get" class="mb-3"><div class="input-group"><input type="text" name="szukaj" class="form-control" placeholder="Szukaj produktu..." value="'.htmlspecialchars($_GET['szukaj'] ?? '').'">';
echo '<button class="btn btn-outline-primary" type="submit">Szukaj</button></div></form>';

$perPage = 50;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$search = trim($_GET['szukaj'] ?? '');
if($search !== ''){
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM product WHERE name LIKE ?');
    $countStmt->execute(['%'.$search.'%']);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT id,name,suggested_price,short_desc FROM product WHERE name LIKE ? ORDER BY id LIMIT ? OFFSET ?');
    $stmt->bindValue(1, '%'.$search.'%', PDO::PARAM_STR);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $countStmt = $pdo->query('SELECT COUNT(*) FROM product');
    $total = (int)$countStmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT id,name,suggested_price,short_desc FROM product ORDER BY id LIMIT ? OFFSET ?');
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
}
$products = $stmt->fetchAll();

$totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;
echo '<div class="row">';
foreach($products as $p){
    echo '<div class="col-md-4 mb-3">';
    echo '<div class="card"><div class="card-body">';
    echo '<h5 class="card-title">'.htmlspecialchars($p['name']).'</h5>';
    echo '<p>'.htmlspecialchars($p['short_desc']).'</p>';
    echo '<p><strong>'.number_format($p['suggested_price'],2).' PLN</strong></p>';
    echo "<form method='post'><input type='hidden' name='product_id' value='".htmlspecialchars($p['id'])."'> Ilość: <input name='quantity' value='1' size='3'> <button name='add_to_cart' class='btn btn-sm btn-primary' type='submit'>Dodaj do koszyka</button></form>";
    echo '</div></div></div>';
}
echo '</div>';

if($totalPages > 1){
    $base = '?';
    if($search !== '') $base .= 'szukaj=' . urlencode($search) . '&';
    echo '<nav aria-label="Strony"><ul class="pagination">';
    if($page > 1){
        echo '<li class="page-item"><a class="page-link" href="' . $base . 'page=' . ($page-1) . '">Poprzednia</a></li>';
    }
    for($p = 1; $p <= $totalPages; $p++){
        if($p == $page) {
            echo '<li class="page-item active" aria-current="page"><span class="page-link">' . $p . '</span></li>';
        } else {
            echo '<li class="page-item"><a class="page-link" href="' . $base . 'page=' . $p . '">' . $p . '</a></li>';
        }
    }
    if($page < $totalPages){
        echo '<li class="page-item"><a class="page-link" href="' . $base . 'page=' . ($page+1) . '">Następna</a></li>';
    }
    echo '</ul></nav>';
}

echo '<p><a href="koszyk.php" class="btn btn-outline-secondary">Pokaż koszyk / Przejdź do zamówienia</a></p>';

include 'szablony/stopka.php';
?>