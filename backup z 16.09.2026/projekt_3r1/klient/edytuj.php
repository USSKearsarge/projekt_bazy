<?php
require '../cfg.php';
if(!isset($_SESSION['klient_id'])){
    header('Location: logowanie.php');
    exit;
}

$id = $_SESSION['klient_id'];
$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $imie = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tele = trim($_POST['telefon'] ?? '');
    $adres = trim($_POST['adres'] ?? '');
    $stmt = $pdo->prepare('UPDATE klienci SET imie=?, nazwisko=?, email=?, telefon=?, adres=? WHERE id=?');
    $stmt->execute([$imie,$nazwisko,$email,$tele,$adres,$id]);
    $_SESSION['klient_imie'] = $imie;
    $message = 'Dane zostały zapisane.';
}

$stmt = $pdo->prepare('SELECT * FROM klienci WHERE id=?');
$stmt->execute([$id]);
$user = $stmt->fetch();

include 'szablony/naglowek.php';

echo '<h2>Edytuj dane</h2>';
if($message) echo '<div class="alert alert-success">'.htmlspecialchars($message).'</div>';

?>
<form method="post" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Imię</label>
        <input name="imie" class="form-control" value="<?php echo htmlspecialchars($user['imie'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Nazwisko</label>
        <input name="nazwisko" class="form-control" value="<?php echo htmlspecialchars($user['nazwisko'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Telefon</label>
        <input name="telefon" class="form-control" value="<?php echo htmlspecialchars($user['telefon'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Adres</label>
        <textarea name="adres" class="form-control"><?php echo htmlspecialchars($user['adres'] ?? ''); ?></textarea>
    </div>
    <button class="btn btn-primary">Zapisz</button>
</form>

<?php include 'szablony/stopka.php'; ?>
