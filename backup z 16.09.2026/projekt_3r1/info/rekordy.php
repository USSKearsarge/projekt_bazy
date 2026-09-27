<?php
require_once '../cfg.php';
$generators = [
	'generate_customers' => 'Generuj klientów (EN)',
	'generate_customers_kz' => 'Generuj klientów (Kazachstan)',
	'generate_orders' => 'Generuj zamówienia (EN)',
	'generate_orders_ext' => 'Generuj zamówienia (rozszerzone)',
	'generuj_losowe_produkty' => 'Generuj produkty',
	'generuj_pracownikow' => 'Generuj pracowników',
];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generator'], $_POST['ile'])) {
	$proc = $_POST['generator'];
	$ile = max(1, (int)$_POST['ile']);
	if (isset($generators[$proc])) {
		try {
			if ($proc === 'generate_customers_kz') {
				for ($i = 0; $i < $ile; $i++) {
					$pdo->exec("INSERT INTO klienci (imie, nazwisko, email, password_hash, telefon, pesel, adres, miasto, kod_pocztowy, kraj, data_urodzenia, data_rej) VALUES (\n
						'Ayan', 'Nurzhan', CONCAT('ayan.nurzhan', FLOOR(RAND()*10000), '@kazmail.kz'), NULL, '+7 701 123 45 67', '90010000000', 'ul. Abaya 10', 'Astana', '010000', 'Kazachstan', DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND()*15000) DAY), NOW())");
				}
			} else {
				$stmt = $pdo->prepare("CALL `$proc`(:ile)");
				$stmt->bindValue(':ile', $ile, PDO::PARAM_INT);
				$stmt->execute();
			}
			$msg = "<div class='alert alert-success mt-3'>Wygenerowano $ile rekordów procedurą: <b>{$generators[$proc]}</b></div>";
		} catch (PDOException $e) {
			$msg = "<div class='alert alert-danger mt-3'>Błąd: ".htmlspecialchars($e->getMessage())."</div>";
		}
	}
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Generatory rekordów</title>
	<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootswatch@5/dist/cosmo/bootstrap.min.css'>
	<link rel='stylesheet' href='css/style.css'>
</head>
<body>
<div class="container mt-5">
	<h1>Generatory rekordów</h1>
	<form method="post" class="row g-3">
		<div class="col-md-6">
			<label for="generator" class="form-label">Wybierz generator</label>
			<select class="form-select" id="generator" name="generator" required>
				<option value="" disabled selected>-- wybierz --</option>
				<?php foreach ($generators as $proc => $label): ?>
					<option value="<?= htmlspecialchars($proc) ?>" <?= (isset($_POST['generator']) && $_POST['generator'] === $proc) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-4">
			<label for="ile" class="form-label">Ilość rekordów</label>
			<input type="number" class="form-control" id="ile" name="ile" min="1" max="1000" value="<?= isset($_POST['ile']) ? (int)$_POST['ile'] : 10 ?>" required>
		</div>
		<div class="col-md-2 d-flex align-items-end">
			<button type="submit" class="btn btn-primary w-100">Generuj</button>
		</div>
	</form>
	<?= $msg ?>
	<div class="mt-4">
		<a href="../index.php" class="Powrot btn btn-outline-secondary me-2">Powrót na stronę główną</a>
	</div>
</div>
</body>
</html>
