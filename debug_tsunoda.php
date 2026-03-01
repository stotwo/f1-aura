<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("
        SELECT r.id, r.points, c.nom, p.nom as pilote 
        FROM resultats r 
        JOIN courses c ON r.course_id = c.id 
        JOIN pilotes p ON r.pilote_id = p.id 
        WHERE p.nom LIKE '%Tsunoda%' AND c.annee = 2025
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Tsunoda results:\n";
    foreach ($results as $row) {
        echo "ID: {$row['id']} | Points: {$row['points']} | Race: {$row['nom']}\n";
    }

    // Check Teams
    $stmtTeam = $pdo->query("SELECT id, nom FROM ecuries WHERE nom LIKE '%Red Bull%' OR nom LIKE '%RB%'");
    $teams = $stmtTeam->fetchAll(PDO::FETCH_ASSOC);
    echo "Teams:\n";
    foreach ($teams as $t) {
        echo "ID: {$t['id']} | Name: {$t['nom']}\n";
    }

} catch(Exception $e) {
    echo $e->getMessage();
}
?>
