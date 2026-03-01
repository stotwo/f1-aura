<?php
require_once 'config.php';

// Remaining points to remove from Red Bull (Driver 1)
$remainingReduction = 57;

$stmt = $pdo->prepare("SELECT id, points FROM resultats WHERE pilote_id = 1 AND points > 0 ORDER BY points DESC");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $res) {
    if ($remainingReduction <= 0) break;

    $deduct = min($res['points'], $remainingReduction);
    $newPoints = $res['points'] - $deduct;
    
    $pdo->prepare("UPDATE resultats SET points = ? WHERE id = ?")->execute([$newPoints, $res['id']]);
    
    $remainingReduction -= $deduct;
    echo "Removed $deduct from result ID " . $res['id'] . "\n";
}

echo "Done reducing Red Bull points. Remaining needed: $remainingReduction\n";
?>
