<?php
require '../cfg.php';

// If already logged in as klient, redirect to klient index
//logowanie dla klienta
//Jakub Piotrowski
if(!empty($_SESSION['klient_id'])){
    header('Location: index.php');
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	$email = trim($_POST['email'] ?? '');
	$has = $_POST['haslo'] ?? '';
		$stmt=$pdo->prepare('SELECT id,first_name,password_hash FROM customer WHERE email=?');
	$stmt->execute([$email]);
	$u=$stmt->fetch();
	// var_dump($email);
	// var_dump($u);
	// if ($u) {
	//     var_dump(password_verify($has, $u['password_hash']));
	// }
	//exit; // tymczasowo, usuń to po sprawdzeniu
	if($u && $u['password_hash'] && password_verify($has,$u['password_hash'])){
		unset($_SESSION['zalogowany'], $_SESSION['first_name'], $_SESSION['rola_id'], $_SESSION['eid']);
		$_SESSION['klient_id']=$u['id'];
			$_SESSION['klient_imie']=$u['first_name'];
		header('Location: index.php');
		exit;
	}
	$blad='Nieprawidłowe dane lub brak hasła. Jeśli nie masz konta, <a href="rejestracja.php">zarejestruj się</a>.';
}
?>
<!doctype html>
<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width,initial-scale=1'>
	<title>Logowanie klienta</title>
	<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5/dist/cosmo/bootstrap.min.css'>
	<link rel='stylesheet' href='../css/style.css'>
</head>
<body>
<div class='container py-4'>
	<h3>Logowanie klienta</h3>
	<?php if(isset($blad)) echo '<div class="alert alert-danger">'. $blad .'</div>'; ?>
	<form method='post' class='row g-3 col-md-6'>
		<div class='col-12'>
			<label class='form-label'>Email</label>
			<input name='email' class='form-control' type='email' required>
		</div>
		<div class='col-12'>
			<label class='form-label'>Hasło</label>
			<div class='input-group'>
				<input id='klient_haslo' type='password' name='haslo' class='form-control' required>
				<button id='toggle_klient_pw' type='button' class='btn btn-outline-secondary'>Pokaż</button>
			</div>
		</div>
		<div class='col-12'>
			<button class='btn btn-success'>Zaloguj</button>
			<a class='btn btn-link' href='../index.php'>Powrót do wyboru</a>
		</div>
	</form>
	<p class='mt-3'>Nie masz konta? <a href='rejestracja.php'>Zarejestruj się</a></p>
</div>
<script>
document.getElementById('toggle_klient_pw').addEventListener('click', function(){
	var f = document.getElementById('klient_haslo');
	if(f.type === 'password'){ f.type = 'text'; this.textContent = 'Ukryj'; } else { f.type = 'password'; this.textContent = 'Pokaż'; }
});
</script>
</body>
</html>