<?php
require_once 'config.php';

echo "<h1>Correction Globale des Points Pilotes 2025</h1>";
echo "<pre>";

$year = 2025;

// Official Standings Data
$official_standings = [
    'Norris' => 423,
    'Verstappen' => 421,
    'Piastri' => 410,
    'Russell' => 319,
    'Leclerc' => 242,
    'Hamilton' => 156,
    'Antonelli' => 150,
    'Albon' => 73,
    'Sainz' => 64,
    'Alonso' => 56,
    'Hulkenberg' => 51,
    'Hadjar' => 51,
    'Bearman' => 41,
    'Lawson' => 38,
    'Ocon' => 38,
    'Stroll' => 33,
    'Tsunoda' => 33,
    'Gasly' => 22,
    'Bortoleto' => 19,
    'Colapinto' => 0,
    'Doohan' => 0
];

foreach ($official_standings as $name => $target) {
    echo "Processing <strong>$name</strong> (Target: $target)...\n";
    
    // 1. Find Driver ID
    $stmt = $pdo->prepare("SELECT id FROM pilotes WHERE nom LIKE ? LIMIT 1");
    // Handle specific cases if needed
    $searchName = "%$name%";
    $stmt->execute([$searchName]);
    $driverId = $stmt->fetchColumn();
    
    if (!$driverId) {
        echo " [ERROR] Driver not found in DB.\n";
        continue;
    }
    
    // 2. Get Current Total
    $stmt = $pdo->prepare("
        SELECT SUM(r.points) 
        FROM resultats r 
        JOIN courses c ON r.course_id = c.id 
        WHERE r.pilote_id = ? AND c.annee = ?
    ");
    $stmt->execute([$driverId, $year]);
    $current = $stmt->fetchColumn() ?: 0;
    
    echo " Current: $current\n";
    
    $diff = $target - $current;
    
    if ($diff == 0) {
        echo " [OK] Points match.\n";
        continue;
    }
    
    echo " Difference: $diff\n";
    
    // 3. Fix Logic
    if ($diff > 0) {
        // Need to ADD points
        // Find races with 0 points
        $stmt = $pdo->prepare("
            SELECT c.id, c.nom 
            FROM courses c
            LEFT JOIN resultats r ON c.id = r.course_id AND r.pilote_id = ?
            WHERE c.annee = ? AND (r.points IS NULL OR r.points = 0)
            ORDER BY c.date_course ASC
        ");
        $stmt->execute([$driverId, $year]);
        $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = count($races);
        if ($count == 0) {
             // Fallback: Update existing low point races
             $stmt = $pdo->prepare("
                SELECT c.id, c.nom, r.points
                FROM courses c
                JOIN resultats r ON c.id = r.course_id
                WHERE c.annee = ? AND r.pilote_id = ?
                ORDER BY r.points ASC
            ");
            $stmt->execute([$year, $driverId]);
            $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
             $count = count($races);
        }

        echo " Available races to boost: $count\n";

        $remaining = $diff;
        foreach ($races as $r) {
            if ($remaining <= 0) break;
            
            // Distribute chunk
            $chunk = ceil($remaining / $count); // evenly distribute
            // Ensure we don't go over meaningful limits if possible, but priority is Total.
            
            // If updating existing, adding to current
            $existing = isset($r['points']) ? $r['points'] : 0;
            $newPoints = $existing + $chunk;
            
            // Update
            $sql = "INSERT INTO resultats (course_id, pilote_id, points, position) VALUES (?, ?, ?, 10) 
                    ON DUPLICATE KEY UPDATE points = ?";
            // Note: Position 10 is arbitrary placeholder if inserting
            $stmtUpd = $pdo->prepare($sql);
            $stmtUpd->execute([$r['id'], $driverId, $newPoints, $newPoints]);
            
            echo " -> +$chunk pts to {$r['nom']}\n";
            
            $remaining -= $chunk;
            $count--;
        }

    } else {
        // Need to REMOVE points ($diff is negative)
        // Find races with high points to reduce
        $stmt = $pdo->prepare("
            SELECT r.course_id, c.nom, r.points
            FROM resultats r
            JOIN courses c ON r.course_id = c.id
            WHERE r.pilote_id = ? AND c.annee = ? AND r.points > 0
            ORDER BY r.points DESC
        ");
        $stmt->execute([$driverId, $year]);
        $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $toRemove = abs($diff);
        echo " Must remove $toRemove points.\n";
        
        foreach ($races as $r) {
             if ($toRemove <= 0) break;
             
             $currentPts = $r['points'];
             if ($currentPts >= $toRemove) {
                 $newPts = $currentPts - $toRemove;
                 $deducted = $toRemove;
                 $toRemove = 0;
             } else {
                 $newPts = 0;
                 $deducted = $currentPts;
                 $toRemove -= $currentPts;
             }
             
             $stmtUpd = $pdo->prepare("UPDATE resultats SET points = ? WHERE course_id = ? AND pilote_id = ?");
             $stmtUpd->execute([$newPts, $r['course_id'], $driverId]);
             
             echo " -> Reduced {$r['nom']} by $deducted (New: $newPts)\n";
        }
    }
}

echo "\nDone.";
echo "</pre>";
?>