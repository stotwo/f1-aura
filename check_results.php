<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, nom, date_course, round FROM courses WHERE annee = 2025 ORDER BY round ASC");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Courses 2025:\n";
foreach ($courses as $c) {
    echo "ID: " . $c['id'] . " | Round: " . $c['round'] . " | Nom: " . $c['nom'] . "\n";
}

echo "\nCurrent Points by Team:\n";
$stmtPoints = $pdo->query("
    SELECT e.nom, SUM(r.points) as total 
    FROM resultats r 
    JOIN courses c ON r.course_id = c.id
    JOIN pilotes p ON r.pilote_id = p.id
    JOIN ecuries e ON p.ecurie_id = e.id
    WHERE c.annee = 2025
    GROUP BY e.nom
    ORDER BY total DESC
");
$points = $stmtPoints->fetchAll(PDO::FETCH_ASSOC);
foreach ($points as $p) {
    echo $p['nom'] . ": " . $p['total'] . "\n";
}
?>