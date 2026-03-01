<?php
require_once 'config.php';

echo "<h1>Correction des Points de Max Verstappen (Obj: 421) - Phase 2 (Update)</h1>";
echo "<pre>";

$target_points = 421;
$year = 2025;
$maxId = 1;

// 1. Get all 2025 races and current points
$stmt = $pdo->prepare("SELECT id, nom FROM courses WHERE annee = ? ORDER BY date_course ASC");
$stmt->execute([$year]);
$races = $stmt->fetchAll(PDO::FETCH_ASSOC);

$current_points = 0;
$zero_point_races = [];

foreach ($races as $r) {
    $stmt = $pdo->prepare("SELECT points FROM resultats WHERE course_id = ? AND pilote_id = ?");
    $stmt->execute([$r['id'], $maxId]);
    $pts = $stmt->fetchColumn();
    
    if ($pts !== false) {
        $current_points += $pts;
        if ($pts == 0) {
            $zero_point_races[] = $r;
        }
    }
}

$deficit = $target_points - $current_points;
echo "Total actuel : $current_points\n";
echo "Déficit : $deficit\n";
echo "Courses à 0 pt disponibles : " . count($zero_point_races) . "\n";

if ($deficit <= 0) die("Objectif atteint.\n");

// 2. Distribute deficit
// We have ~7 races to fill ~157 points.
// That's ~22.4 points per race.
// Distribution: Mostly 25 (Wins) and some 18 (2nd).
// 6 wins * 25 = 150. Remainder 7.
// So 6 races at 25, 1 race at 7 (P7).

$updates = [];
$remaining = $deficit;

foreach ($zero_point_races as $r) {
    if ($remaining <= 0) break;
    
    // Determine points for this race
    if ($remaining >= 25) {
        $p = 25;
        $pos = 1;
    } else {
        $p = $remaining;
        // Approximation of position
        if ($p >= 18) $pos = 2;
        elseif ($p >= 15) $pos = 3;
        elseif ($p >= 12) $pos = 4;
        elseif ($p >= 10) $pos = 5;
        else $pos = 7; 
    }
    
    // Update
    $stmtUpd = $pdo->prepare("UPDATE resultats SET points = ?, position = ? WHERE course_id = ? AND pilote_id = ?");
    $stmtUpd->execute([$p, $pos, $r['id'], $maxId]);
    
    echo " -> Updated {$r['nom']} : +$p pts (Pos $pos)\n";
    
    $remaining -= $p;
}

echo "\nTerminé. Reste à combler : $remaining\n";
echo "</pre>";
?>