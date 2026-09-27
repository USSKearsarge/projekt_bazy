<?php
require '../cfg.php';
// Michał Pałyga
// If already logged in as admin, redirect straight to admin index
if(!empty($_SESSION['zalogowany'])){
	header('Location: index.php');
	exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
	$email = trim($_POST['email'] ?? '');
	$has = $_POST['haslo'] ?? '';
		$stmt = $pdo->prepare('SELECT id, first_name , password_hash, title FROM emp WHERE email=?');
	$stmt->execute([$email]);
	$u = $stmt->fetch();
	if($u && $u['password_hash'] && password_verify($has,$u['password_hash'])){
		unset($_SESSION['customer_id'], $_SESSION['name']);
		$_SESSION['zalogowany'] = true;
			$_SESSION['first_name'] = $u['first_name'];
			$_SESSION['title'] = $u['title'];
		$_SESSION['eid'] = $u['id'];
		header('Location: index.php');
		exit;
	}
	$blad = 'Nieprawidłowe dane';
}
?>
<!doctype html>
<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width,initial-scale=1'>
	<title>Logowanie pracownika</title>
	<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5/dist/cosmo/bootstrap.min.css'>
	<link rel='stylesheet' href='../css/style.css'>
</head>
<body>
<div class='container py-4'>
	<h3>Logowanie pracownika</h3>
	<?php if(isset($blad)) echo '<div class="alert alert-danger">'.htmlspecialchars($blad).'</div>'; ?>
	<form method='post' class='row g-3 col-md-6'>
		<div class='col-12'>
			<label class='form-label'>Email</label>
			<input name='email' class='form-control' type='email' required>
		</div>
		<div class='col-12'>
			<label class='form-label'>Hasło</label>
			<div class='input-group'>
				<input id='admin_haslo' type='password' name='haslo' class='form-control' required>
				<button id='toggle_admin_pw' type='button' class='btn btn-outline-secondary'>Pokaż</button>
			</div>
		</div>
		<div class='col-12'>
			<button class='btn btn-primary'>Zaloguj</button>
			<a class='btn btn-link' href='../index.php'>Powrót do wyboru</a>
		</div>
	</form>
</div>
<script>
document.getElementById('toggle_admin_pw').addEventListener('click', function(){
	var f = document.getElementById('admin_haslo');
	if(f.type === 'password'){ f.type = 'text'; this.textContent = 'Ukryj'; } else { f.type = 'password'; this.textContent = 'Pokaż'; }
});
</script>
</body>
</html>