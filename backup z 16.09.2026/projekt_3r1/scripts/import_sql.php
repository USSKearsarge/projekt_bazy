<?php
 // Sowula Mateusz
// Simple SQL importer that respects DELIMITER directives (e.g., $$)
require __DIR__ . '/../cfg.php';
$sqlFile = __DIR__ . '/../baza/baza_testowa.sql';
if(!is_readable($sqlFile)){
    echo "SQL file not found: $sqlFile\n";
    exit(2);
}
$fh = fopen($sqlFile, 'r');
if(!$fh){ echo "Unable to open SQL file\n"; exit(2); }
$delimiter = ";";
$buffer = '';
$lineNo = 0;
try{
    while(($line = fgets($fh)) !== false){
        $lineNo++;
        $trim = trim($line);
        if($trim === '' ) continue;
        // handle delimiter directive
        if(strpos($trim, 'DELIMITER') === 0){
            $parts = preg_split('/\s+/', $trim);
            $delimiter = $parts[1] ?? ';';
            continue;
        }
        // skip SQL comments that would confuse execution
        if(strpos($trim, '--') === 0 || strpos($trim, '#') === 0) continue;

        $buffer .= $line;

        // check if buffer ends with current delimiter (after trimming whitespace)
        $end = substr(trim($buffer), -strlen($delimiter));
        if($delimiter !== '' && $end === $delimiter){
            // remove the delimiter from the end
            $stmt = substr(trim($buffer), 0, -strlen($delimiter));
            $buffer = '';
            if($stmt === '') continue;
            try{
                $pdo->exec($stmt);
                echo "OK: Executed statement ending at line $lineNo\n";
            }catch(PDOException $e){
                echo "ERROR (line $lineNo): " . $e->getMessage() . "\n";
                echo "Failed statement:\n" . substr($stmt,0,1000) . "\n---\n";
                // continue attempting to run rest
            }
        }
    }
    // flush any remaining buffer
    if(trim($buffer) !== ''){
        try{
            $pdo->exec($buffer);
            echo "OK: Executed final buffer\n";
        }catch(PDOException $e){
            echo "ERROR (final): " . $e->getMessage() . "\n";
        }
    }
    fclose($fh);
    echo "Import finished.\n";
}catch(Exception $e){
    echo "Fatal: " . $e->getMessage() . "\n";
}

?>