<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE resultats");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $c) {
        echo $c['Field'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
