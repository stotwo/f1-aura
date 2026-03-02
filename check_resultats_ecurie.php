<?php
require_once 'config.php';

$year = 2025;
echo "Checking resultats.ecurie_id population for $year...\n";

$sql = "SELECT COUNT(*) as total, COUNT(ecurie_id) as has_ecurie_id FROM resultats r JOIN courses c ON r.course_id = c.id WHERE c.annee = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$year]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total results: " . $res['total'] . "\n";
echo "With ecurie_id: " . $res['has_ecurie_id'] . "\n";

if ($res['total'] > 0) {
    echo "\nSample entries:\n";
    $sql = "SELECT r.id, r.pilote_id, r.ecurie_id, r.points FROM resultats r JOIN courses c ON r.course_id = c.id WHERE c.annee = ? LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        print_r($row);
    }
}
?>
