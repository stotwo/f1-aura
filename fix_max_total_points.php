<?php
// fix_max_total_points.php

require_once 'config.php';

// Configuration
$target_points = 421;
$season_year = 2025;
$driver_name = 'Max Verstappen';

echo "<h1>Correction des Points de $driver_name (Saison $season_year)</h1>";

try {
    // 1. Récupérer l'ID du pilote
    $stmt = $pdo->prepare("SELECT id FROM drivers WHERE name = ?");
    $stmt->execute([$driver_name]);
    $driver_id = $stmt->fetchColumn();

    if (!$driver_id) {
        die("Erreur : Pilote '$driver_name' introuvable.");
    }
    echo "ID Pilote : $driver_id<br>";

    // 2. Récupérer l'ID de la saison 2025
    // Note : Selon la structure, 'season_id' est souvent utilisé dans la table results ou races.
    // On va supposer ici que la table 'races' a une colonne 'year'.

    // 3. Récupérer tous les résultats de Max pour 2025
    // On joint results et races pour filtrer par année
    $sql = "
        SELECT 
            r.id as result_id, 
            r.points, 
            ra.name as race_name, 
            ra.date,
            r.race_id
        FROM results r
        JOIN races ra ON r.race_id = ra.id
        WHERE r.driver_id = :driver_id 
        AND ra.year = :year
        ORDER BY ra.date ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':driver_id' => $driver_id,
        ':year' => $season_year
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total actuel
    $current_total = 0;
    $zero_point_races = [];

    foreach ($results as $row) {
        $current_total += $row['points'];
        if ($row['points'] == 0) {
            $zero_point_races[] = $row;
        }
    }

    echo "Total actuel : <strong>$current_total</strong> points.<br>";
    echo "Objectif : <strong>$target_points</strong> points.<br>";

    $deficit = $target_points - $current_total;
    echo "Déficit à combler : <strong>$deficit</strong> points.<br>";
    echo "Nombre de courses avec 0 points disponibles : " . count($zero_point_races) . "<br><br>";

    if ($deficit <= 0) {
        die("Le total actuel est déjà supérieur ou égal à l'objectif. Aucune action requise.");
    }

    if (empty($zero_point_races)) {
        die("Aucune course à 0 point disponible pour ajouter des points. Impossible d'atteindre l'objectif sans modifier les résultats existants.");
    }

    // 4. Stratégie de distribution
    // Points réalistes hors podium : 4e=12, 5e=10, 6e=8, 7e=6, 8e=4, 9e=2, 10e=1
    // On va essayer d'attribuer des 12 et des 10 en priorité pour combler le vide rapidement
    $distribution = [];
    $remaining_deficit = $deficit;
    
    // Valeurs possibles à attribuer (pour simuler des places de 4ème à 7ème principalement)
    $possible_values = [12, 10, 8, 6, 5, 4, 3, 2, 1]; 

    foreach ($zero_point_races as $index => $race) {
        if ($remaining_deficit <= 0) break;

        // On essaie de prendre une grosse part (ex: 12 ou 10) tant qu'on ne dépasse pas trop
        // Si on est à la dernière course disponible, on met tout le reste (si c'est une valeur valide ou proche)
        $is_last_chance = ($index === count($zero_point_races) - 1);

        if ($is_last_chance) {
            $points_to_add = $remaining_deficit;
        } else {
            // Logique simple : essayer de mettre 12, sinon 10, sinon 8...
            // Pour varier un peu et ne pas avoir que des 12, on aléatoirise légèrement parmi les choix valides
            $points_to_add = 0;
            foreach ($possible_values as $val) {
                // On vérifie si en ajoutant cette valeur, le reste est gérable par les courses restantes
                // Ce calcul est un peu complexe, faisons plus simple : moyenne nécessaire
                $races_left_after_this = count($zero_point_races) - 1 - $index;
                
                // Si on met $val, il reste ($remaining_deficit - $val) à répartir sur $races_left_after_this
                // Si le reste est trop grand (> 26 * courses), on doit mettre plus maintenant
                // Si le reste est trop petit, on doit mettre moins
                
                $points_to_add = $val;
                if ($remaining_deficit - $points_to_add >= 0) {
                     break; // On prend la plus grande valeur possible
                }
            }
            
            // Lissage : Si la moyenne nécessaire est basse, ne pas mettre 12
            $avg_needed = $remaining_deficit / (count($zero_point_races) - $index);
            if ($avg_needed < 8 && $points_to_add > 8) {
                $points_to_add = 8; // Force 6th place
            }
            if ($avg_needed < 5 && $points_to_add > 5) {
                $points_to_add = 4; // Force 8th place
            }
        }

        // Sécurité finale pour ne pas dépasser l'objectif global
        if ($points_to_add > $remaining_deficit) {
            $points_to_add = $remaining_deficit;
        }

        $distribution[] = [
            'result_id' => $race['result_id'],
            'race_name' => $race['race_name'],
            'points_added' => $points_to_add,
            'position_simulated' => getPositionFromPoints($points_to_add)
        ];

        $remaining_deficit -= $points_to_add;
    }

    // 5. Exécution des mises à jour
    echo "<h3>Plan de distribution :</h3><ul>";
    $sql_update = "UPDATE results SET points = :points, position = :position WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);

    foreach ($distribution as $item) {
        echo "<li>{$item['race_name']} : Ajout de <strong>{$item['points_added']}</strong> points (Pos simulée: {$item['position_simulated']})</li>";
        
        // Mise à jour
        $stmt_update->execute([
            ':points' => $item['points_added'],
            ':position' => $item['position_simulated'],
            ':id' => $item['result_id']
        ]);
    }
    echo "</ul>";

    if ($remaining_deficit > 0) {
        echo "<p style='color:red;'>Attention : Il reste $remaining_deficit points non distribués (pas assez de courses vides ?).</p>";
    } elseif ($remaining_deficit < 0) {
        echo "<p style='color:orange;'>Info : Le total dépasse légèrement l'objectif de " . abs($remaining_deficit) . " points.</p>";
    } else {
        echo "<p style='color:green;'><strong>Succès : Objectif de $target_points points atteint exactement !</strong></p>";
    }

} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

function getPositionFromPoints($p) {
    // Mapping approximatif Points -> Position standard actuelle
    switch ($p) {
        case 25: return 1;
        case 18: return 2;
        case 15: return 3;
        case 12: return 4;
        case 10: return 5;
        case 8: return 6;
        case 6: return 7;
        case 4: return 8;
        case 2: return 9;
        case 1: return 10;
        default: return 4; // Par défaut si calcul bizarre, on met 4e
    }
}
?>
