<?php
// Sowula Mateusz
$cfgFile = __DIR__ . '/../cfg.php';
$cfg = is_readable($cfgFile) ? file_get_contents($cfgFile) : '';
$host = 'localhost'; $db = 'bazatestowa'; $user = 'root'; $pass = ''; $charset = 'utf8mb4';
if($cfg){
    if(preg_match("/\$host\s*=\s*'([^']*)'/", $cfg, $m)) $host = $m[1];
    if(preg_match("/\$db\s*=\s*'([^']*)'/", $cfg, $m)) $db = $m[1];
    if(preg_match("/\$user\s*=\s*'([^']*)'/", $cfg, $m)) $user = $m[1];
    if(preg_match("/\$pass\s*=\s*'([^']*)'/", $cfg, $m)) $pass = $m[1];
    if(preg_match("/\$charset\s*=\s*'([^']*)'/", $cfg, $m)) $charset = $m[1];
}
$sqlFile = __DIR__ . '/../baza/baza_testowa.sql';
if(!is_readable($sqlFile)){
    echo "SQL file not found: $sqlFile\n";
    exit(2);
}

$dsnNoDb = "mysql:host=$host;charset=$charset";
try{
    $serverPdo = new PDO($dsnNoDb, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}catch(PDOException $e){
    echo "Błąd PDO (server connect): " . $e->getMessage() . "\n";
    exit(3);
}
try{
    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset COLLATE ${charset}_polish_ci");
}catch(PDOException $e){
    echo "Błąd tworzenia DB: " . $e->getMessage() . "\n";
    exit(4);
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try{
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}catch(PDOException $e){
    echo "Błąd PDO (db connect): " . $e->getMessage() . "\n";
    exit(5);
}

$fh = fopen($sqlFile, 'r');
$delimiter = ";";
$buffer = '';
$lineNo = 0;
while(($line = fgets($fh)) !== false){
    $lineNo++;
    $trim = trim($line);
    if($trim === '' ) continue;
    if(strpos($trim, 'DELIMITER') === 0){
        $parts = preg_split('/\s+/', $trim);
        $delimiter = $parts[1] ?? ';';
        continue;
    }
    if(strpos($trim, '--') === 0 || strpos($trim, '#') === 0) continue;
    $buffer .= $line;
    $end = substr(trim($buffer), -strlen($delimiter));
    if($delimiter !== '' && $end === $delimiter){
        $stmt = substr(trim($buffer), 0, -strlen($delimiter));
        $buffer = '';
        if($stmt === '') continue;
        try{
            $pdo->exec($stmt);
            echo "OK: Executed statement ending at line $lineNo\n";
        }catch(PDOException $e){
            echo "ERROR (line $lineNo): " . $e->getMessage() . "\n";
            echo "Failed statement snippet:\n" . substr($stmt,0,1000) . "\n---\n";
        }
    }
}
if(trim($buffer) !== ''){
    try{ $pdo->exec($buffer); echo "OK: Executed final buffer\n"; }catch(PDOException $e){ echo "ERROR (final): " . $e->getMessage() . "\n"; }
}
echo "Import finished.\n";
