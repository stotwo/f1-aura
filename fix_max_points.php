<?php
require_once 'config.php';

echo "Correction des points pour Max Verstappen à Abou Dabi (ID 24)...\n";

$pilote_id = 1; // Max
$course_id = 24; // Abou Dabi
$points = 25; // 1st Place

$stmt = $pdo->prepare("UPDATE resultats SET points = ? WHERE course_id = ? AND pilote_id = ?");
$stmt->execute([$points, $course_id, $pilote_id]);

echo "Points corrigés à $points.\n";
?>