<?php
require '../cfg.php';
if(!isset($_SESSION['klient_id'])){
    header('Location: logowanie.php');
    exit;
}

$id = $_SESSION['klient_id'];
$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $first_name = trim($_POST['first_name'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $stmt = $pdo->prepare('UPDATE customer SET first_name=?, name=?, email=?, phone=?, street=? WHERE id=?');
    $stmt->execute([$first_name,$name,$email,$phone,$street,$id]);
    $_SESSION['klient_imie'] = $first_name;
    $message = 'Dane zostały zapisane.';
}

$stmt = $pdo->prepare('SELECT * FROM customer WHERE id=?');
$stmt->execute([$id]);
$user = $stmt->fetch();

include 'szablony/naglowek.php';

echo '<h2>Edytuj dane</h2>';
if($message) echo '<div class="alert alert-success">'.htmlspecialchars($message).'</div>';

?>
<form method="post" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Imię</label>
        <input name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Nazwisko</label>
        <input name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Telefon</label>
        <input name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Adres</label>
        <textarea name="street" class="form-control"><?php echo htmlspecialchars($user['street'] ?? ''); ?></textarea>
    </div>
    <button class="btn btn-primary">Zapisz</button>
</form>

<?php include 'szablony/stopka.php'; ?>
