<?php
require_once 'config.php';

// Debug Red Bull Points
echo "Red Bull Results Breakdown:\n";
$sql = "SELECT r.id, r.points, c.nom, p.nom as pilote 
        FROM resultats r 
        JOIN courses c ON r.course_id = c.id 
        JOIN pilotes p ON r.pilote_id = p.id 
        WHERE p.ecurie_id = 1 AND c.annee = 2025 
        ORDER BY r.points DESC";
$stmt = $pdo->query($sql);
$total = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Res ID: " . $row['id'] . " | " . $row['pilote'] . " | " . $row['nom'] . " | Points: " . $row['points'] . "\n";
    $total += $row['points'];
}
echo "Total Red Bull: $total\n";

// Run fix if total > 451
if ($total > 451) {
    $diff = $total - 451;
    echo "Removing $diff points...\n";
    
    // Remove from largest points first
    $stmt = $pdo->query($sql);
    while ($diff > 0 && ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
        if ($row['points'] > 0) {
            $deduct = min($row['points'], $diff);
            $newPoints = $row['points'] - $deduct;
            $pdo->prepare("UPDATE resultats SET points = ? WHERE id = ?")->execute([$newPoints, $row['id']]);
            echo "Updated Res ID " . $row['id'] . ": " . $row['points'] . " -> $newPoints\n";
            $diff -= $deduct;
        }
    }
}
?>
