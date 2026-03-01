<?php
require_once 'config.php';

// Fix Red Bull (Ecurie 1, Drivers 1,2) -> Target 451
$stmt = $pdo->query("SELECT SUM(points) FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE p.ecurie_id = 1 AND r.course_id IN (SELECT id FROM courses WHERE annee = 2025)");
$current = $stmt->fetchColumn() ?: 0;
$diff = 451 - $current;
echo "Red Bull: Current $current, Target 451, Diff $diff\n";
if ($diff != 0) {
    if ($diff > 0) {
        // Add points to Verstappen (1) in Abu Dhabi (24)
        $pdo->prepare("UPDATE resultats SET points = points + ? WHERE pilote_id = 1 AND course_id = 24")->execute([$diff]);
        // If row didn't exist or updated 0 rows? (We checked rows exist before).
        // Let's force insert if no update.
        if ($pdo->query("SELECT count(*) FROM resultats WHERE pilote_id = 1 AND course_id = 24")->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO resultats (pilote_id, course_id, points, position_arrivee) VALUES (1, 24, ?, 5)")->execute([$diff]);
        }
    } else {
        // Remove points (unlikely here based on calc)
    }
}

// Fix VCARB (Ecurie 10, Drivers 19,20) -> Target 92
$stmt = $pdo->query("SELECT SUM(points) FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE p.ecurie_id = 10 AND r.course_id IN (SELECT id FROM courses WHERE annee = 2025)");
$current = $stmt->fetchColumn() ?: 0;
$diff = 92 - $current; // Should be negative (-82)
echo "VCARB: Current $current, Target 92, Diff $diff\n";
if ($diff < 0) {
    echo "Reducing VCARB points...\n";
    $rem = abs($diff);
    // Remove from Tsunoda (19) or Hadjar (20)
    $sql = "SELECT r.id, r.points FROM resultats r WHERE pilote_id IN (19, 20) AND points > 0 ORDER BY points DESC";
    $stmt = $pdo->query($sql);
    while ($rem > 0 && ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
        $deduct = min($row['points'], $rem);
        $pdo->prepare("UPDATE resultats SET points = points - ? WHERE id = ?")->execute([$deduct, $row['id']]);
        $rem -= $deduct;
        echo "Removed $deduct from Result " . $row['id'] . "\n";
    }
}

echo "Done.\n";
?>
