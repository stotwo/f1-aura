<?php
require_once 'config.php';

echo "Debug Points 2025 Evolution\n";

// Get 2025 races
$stmt = $pdo->prepare("SELECT id, nom, date_course, round FROM courses WHERE annee = 2025 ORDER BY date_course ASC");
$stmt->execute();
$races = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get Max Verstappen ID (assuming 'Max' and 'Verstappen')
$stmtP = $pdo->prepare("SELECT id FROM pilotes WHERE nom LIKE '%Verstappen%'");
$stmtP->execute();
$maxId = $stmtP->fetchColumn();

if (!$maxId) {
    echo "Max Verstappen not found.\n";
    exit;
}
echo "Max Verstappen ID: $maxId\n";

$currentTotal = 0;
foreach ($races as $r) {
    // Get points for this race
    $stmt2 = $pdo->prepare("SELECT points FROM resultats WHERE course_id = ? AND pilote_id = ?");
    $stmt2->execute([$r['id'], $maxId]);
    $points = $stmt2->fetchColumn();
    
    $oldTotal = $currentTotal;
    $currentTotal += ($points ?: 0);
    
    echo sprintf(
        "Race: %-35s (ID: %3d) | Round: %2d | Date: %s | +%3d Points | Total: %4d\n",
        $r['nom'], $r['id'], $r['round'], $r['date_course'], $points, $currentTotal
    );
}
?>