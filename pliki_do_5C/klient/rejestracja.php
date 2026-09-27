<?php
require '../cfg.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $first_name = trim($_POST['first_name'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if($first_name === '' || $name === '' || $email === '' || $pass === ''){
        $err = 'Wypełnij wszystkie pola.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM customer WHERE email = ?');
        $stmt->execute([$email]);
        if($stmt->fetch()){
            $err = 'Ten email jest już zarejestrowany.';
        } else {
            // Validate date of birth (no future dates) NOGAJ
            $dob = trim($_POST['birth_date'] ?? '');
            if ($dob !== '') {
                $today = date('Y-m-d');
                if ($dob > $today) {
                    $err = 'Data urodzenia nie może być z przyszłości.';
                }
            }
            if (empty($err)) {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO customer (first_name,name,email,password_hash,phone,street,city,zip_code,country,birth_date) VALUES (?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$first_name,$name,$email,$hash, $_POST['phone'] ?? '', $_POST['street'] ?? '', $_POST['city'] ?? '', $_POST['zip_code'] ?? '', $_POST['country'] ?? '', $dob !== '' ? $dob : NULL]);
                $id = $pdo->lastInsertId();
                unset($_SESSION['zalogowany'], $_SESSION['first_name'], $_SESSION['rola_id'], $_SESSION['eid']);
                $_SESSION['klient_id'] = $id;
                $_SESSION['klient_imie'] = $first_name;
                header('Location: index.php');
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width,initial-scale=1'>
    <title>Rejestracja klienta</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5/dist/cosmo/bootstrap.min.css'>
    <link rel='stylesheet' href='../css/style.css'>
</head>
<body>
<div class='container py-4'>
    <h3>Rejestracja klienta</h3>
    <?php if(isset($err)) echo '<div class="alert alert-danger">'.htmlspecialchars($err).'</div>'; ?>
    <form method='post' class='row g-3 col-md-8'>
        <div class='col-md-6'>
            <label class='form-label'>Imię</label>
            <input name='first_name' class='form-control' required>
        </div>
        <div class='col-md-6'>  
            <label class='form-label'>Nazwisko</label>
            <input name='name' class='form-control' required>
        </div>
        <div class='col-md-6'>
            <label class='form-label'>Email</label>
            <input name='email' class='form-control' type='email' required>
        </div>
        <div class='col-md-6'>
            <label class='form-label'>Hasło</label>
            <div class='input-group'>
                <input id='reg_password' name='password' class='form-control' type='password' required>
                <button id='toggle_reg_pw' type='button' class='btn btn-outline-secondary'>Pokaż</button>
            </div>
        </div>
        <div class='col-md-6'>
            <label class='form-label'>Telefon</label>
            <input name='phone' class='form-control'>
        </div>
        <div class='col-md-6'>
            <label class='form-label'>Miasto</label>
            <input name='city' class='form-control'>
        </div>
        <div class='col-12'>
            <label class='form-label'>Adres</label>
            <input name='street' class='form-control'>
        </div>
        <div class='col-md-4'>
            <label class='form-label'>Kod pocztowy</label>
            <input name='zip_code' class='form-control'>
        </div>
        <div class='col-md-4'>
            <label class='form-label'>Kraj</label>
            <input name='country' class='form-control' value='Polska'>
        </div>
        <div class='col-md-4'>
            <label class='form-label'>Data urodzenia</label>
            <input name='birth_date' class='form-control' type='date' max='<?php echo date("Y-m-d"); ?>' value='<?php echo isset($_POST["birth_date"]) ? htmlspecialchars($_POST["birth_date"]) : ""; ?>'>
        </div>
        <div class='col-12'>
            <button type='submit' class='btn btn-primary'>Zarejestruj</button>
            <a class='btn btn-link' href='logowanie.php'>Masz konto? Zaloguj się</a>
        </div>
    </form>
</div>
<script>
document.getElementById('toggle_reg_pw').addEventListener('click', function(){
    var f = document.getElementById('reg_password');
    if(f.type === 'password'){ f.type = 'text'; this.textContent = 'Ukryj'; } else { f.type = 'password'; this.textContent = 'Pokaż'; }
});
</script>
</body>
</html>
