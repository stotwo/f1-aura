<?php
require_once 'config.php';

echo "Debug Points 2026\n";

// 1. Check races for 2026
$stmt = $pdo->prepare("SELECT id, nom, date_course, statut FROM courses WHERE annee = 2026");
$stmt->execute();
$races = $stmt->fetchAll();

echo "Courses 2026:\n";
foreach ($races as $r) {
    echo "- ID: {$r['id']} | {$r['nom']} | {$r['date_course']} | {$r['statut']}\n";
    
    // Check points for this race
    $stmt2 = $pdo->prepare("SELECT count(*) as count, SUM(points) as total_points FROM resultats WHERE course_id = ?");
    $stmt2->execute([$r['id']]);
    $res = $stmt2->fetch();
    echo "  -> Entries: {$res['count']} | Total Points: {$res['total_points']}\n";
}
?>