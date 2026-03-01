<?php
require_once 'config.php';

echo "Debug Points Abou Dabi (ID 24) - Max Verstappen (ID 1)\n";
$stmt = $pdo->prepare("SELECT * FROM resultats WHERE course_id = 24 AND pilote_id = 1");
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);

print_r($res);
?>