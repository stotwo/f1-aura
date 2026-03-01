<?php
require_once 'config.php';

// Force Reset Red Bull to 451
// Calculate current total
$stmt = $pdo->query("SELECT SUM(points) FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE p.ecurie_id = 1 AND r.course_id IN (SELECT id FROM courses WHERE annee = 2025)");
$current = $stmt->fetchColumn();
echo "Current Red Bull: $current\n";

$target = 451;
$diff = $current - $target;

if ($diff > 0) {
    echo "Need to remove $diff points.\n";
    // Remove from Verstappen (1)
    $stmt = $pdo->prepare("SELECT id, points FROM resultats WHERE pilote_id = 1 AND points > 0 ORDER BY points DESC");
    $stmt->execute();
    while ($diff > 0 && ($row = $stmt->fetch())) {
        $deduct = min($row['points'], $diff);
        $pdo->prepare("UPDATE resultats SET points = points - ? WHERE id = ?")->execute([$deduct, $row['id']]);
        $diff -= $deduct;
        echo "Removed $deduct from Result " . $row['id'] . "\n";
    }
} elseif ($diff < 0) {
    echo "Need to add " . abs($diff) . " points.\n";
    // Add to Verstappen (1)
    $pdo->prepare("UPDATE resultats SET points = points + ? WHERE pilote_id = 1 ORDER BY id DESC LIMIT 1")->execute([abs($diff)]);
}

// Force Update Mercedes => 469
$stmt = $pdo->query("SELECT SUM(points) FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE p.ecurie_id = 2 AND r.course_id IN (SELECT id FROM courses WHERE annee = 2025)");
$current = $stmt->fetchColumn();
echo "Current Mercedes: $current\n";
$detailDiff = 469 - $current;
if ($detailDiff != 0) {
    $pdo->prepare("UPDATE resultats SET points = points + ? WHERE pilote_id = 3 ORDER BY id DESC LIMIT 1")->execute([$detailDiff]);
    echo "Adjusted Mercedes by $detailDiff\n";
}

// Force Update Ferrari => 398
$stmt = $pdo->query("SELECT SUM(points) FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE p.ecurie_id = 3 AND r.course_id IN (SELECT id FROM courses WHERE annee = 2025)");
$current = $stmt->fetchColumn();
$detailDiff = 398 - $current;
if ($detailDiff != 0) {
    $pdo->prepare("UPDATE resultats SET points = points + ? WHERE pilote_id = 5 ORDER BY id DESC LIMIT 1")->execute([$detailDiff]);
    echo "Adjusted Ferrari by $detailDiff\n";
}

// Force Update Williams => 137
$stmt = $pdo->query("SELECT SUM(points) FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE p.ecurie_id = 7 AND r.course_id IN (SELECT id FROM courses WHERE annee = 2025)");
$current = $stmt->fetchColumn();
$detailDiff = 137 - $current;
if ($detailDiff != 0) {
    $pdo->prepare("UPDATE resultats SET points = points + ? WHERE pilote_id = 13 ORDER BY id DESC LIMIT 1")->execute([$detailDiff]); 
    echo "Adjusted Williams by $detailDiff\n";
}

// Force Update Visa Cash App RB => 92
// Ecurie ID 10
$stmt = $pdo->query("SELECT SUM(points) FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE p.ecurie_id = 10 AND r.course_id IN (SELECT id FROM courses WHERE annee = 2025)");
$current = $stmt->fetchColumn();
if (!$current) $current = 0;
$detailDiff = 92 - $current;
if ($detailDiff != 0) {
    // If no result exists, insert one
    $check = $pdo->query("SELECT count(*) FROM resultats WHERE pilote_id = 19")->fetchColumn();
    if ($check == 0) {
         $pdo->prepare("INSERT INTO resultats (pilote_id, course_id, points, position_arrivee) VALUES (19, 24, ?, 8)")->execute([$detailDiff]);
    } else {
         $pdo->prepare("UPDATE resultats SET points = points + ? WHERE pilote_id = 19 ORDER BY id DESC LIMIT 1")->execute([$detailDiff]);
    }
    echo "Adjusted RB by $detailDiff\n";
}

echo "Final adjustments complete.\n";
?>
