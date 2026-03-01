<?php
require_once 'config.php';

echo "Nettoyage des données erronées de la saison 2026...\n";

// 1. Identifier les courses de 2026 avec des résultats
$stmt = $pdo->prepare("
    SELECT c.id, c.nom, c.date_course 
    FROM courses c
    JOIN resultats r ON r.course_id = c.id
    WHERE c.annee = 2026
    GROUP BY c.id
");
$stmt->execute();
$erroneousRaces = $stmt->fetchAll();

if (empty($erroneousRaces)) {
    echo "Aucune donnée erronée trouvée pour 2026.\n";
} else {
    foreach ($erroneousRaces as $race) {
        echo "Correction de la course: {$race['nom']} (ID: {$race['id']})\n";
        
        // Supprimer les résultats
        $delStmt = $pdo->prepare("DELETE FROM resultats WHERE course_id = ?");
        $delStmt->execute([$race['id']]);
        echo " - Résultats supprimés.\n";
        
        // Mettre à jour le statut
        $updStmt = $pdo->prepare("UPDATE courses SET statut = 'À venir' WHERE id = ?");
        $updStmt->execute([$race['id']]);
        echo " - Statut mis à 'À venir'.\n";
    }
}

echo "Terminé.";
?>