<?php
require_once 'config.php';

echo "Drivers with > 26 points in Abu Dhabi (ID 24):\n";
$stmt = $pdo->prepare("SELECT p.nom, r.points, r.position FROM resultats r JOIN pilotes p ON r.pilote_id = p.id WHERE r.course_id = 24 AND r.points > 26");
$stmt->execute();
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($res as $r) {
    echo "{$r['nom']} (Pos: {$r['position']}) -> {$r['points']} pts\n";
}
echo "Total found: " . count($res);
?>