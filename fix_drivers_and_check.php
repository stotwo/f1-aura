<?php
require_once 'config.php';

// Fix Driver Assignments
// Tsunoda (19) -> 10
// Hadjar (20) -> 10
$pdo->exec("UPDATE pilotes SET ecurie_id = 10 WHERE id IN (19, 20)");
echo "Moved Tsunoda and Hadjar to Visa Cash App RB (10).\n";

// Re-calculate points to see if we are close to target.
echo "Recalculating standings...\n";

// Target:
// McLaren: 833
// Mercedes: 469
// Red Bull: 451
// Ferrari: 398
// Williams: 137
// Visa Cash App RB: 92
// Aston Martin: 89
// Haas: 79
// Kick Sauber: 70
// Alpine: 22

// Let's check current standings after move.
$stmt = $pdo->query("
    SELECT e.nom, SUM(r.points) as total 
    FROM resultats r 
    JOIN courses c ON r.course_id = c.id
    JOIN pilotes p ON r.pilote_id = p.id
    JOIN ecuries e ON p.ecurie_id = e.id
    WHERE c.annee = 2025
    GROUP BY e.nom
    ORDER BY total DESC
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['nom'] . ": " . $row['total'] . "\n";
}

// If mismatches remain, run force_fix logic again?
// Wait, moving Tsunoda/Hadjar removes their points from Red Bull and adds to VCARB.
// Tsunoda had ~96 points? (Saw in debug_redbull).
// If we move them, Red Bull drops by ~100 points.
// VCARB gains ~100 points.
// Target Red Bull: 451.
// Target VCARB: 92.
// It might be roughly correct just by moving them!

?>
