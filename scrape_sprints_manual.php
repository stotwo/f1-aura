<?php
require_once 'config.php';

set_time_limit(300);

echo "<h1>Importation manuelle des Sprints F1 2025</h1>";
echo "<pre>";

function getHtmlContent($url) {
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
        ]
    ]);
    return file_get_contents($url, false, $context);
}

function parseSprintResults($pdo, $url, $raceNameKeyword) {
    echo "Traitement du Sprint : $raceNameKeyword ($url)...\n";
    $html = getHtmlContent($url);
    
    if (!$html) {
        echo "ERREUR: Impossible de récupérer $url\n";
        return;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // 1. Find the main race to get details
    $stmtMain = $pdo->prepare("SELECT id, nom, lieu, date_course, round, annee FROM courses WHERE nom LIKE ? AND nom NOT LIKE 'Sprint%' LIMIT 1");
    $stmtMain->execute(["%$raceNameKeyword%"]);
    $mainRace = $stmtMain->fetch();

    if (!$mainRace) {
        echo "ERREUR: Course principale non trouvée pour le mot clé '$raceNameKeyword'.\n";
        return;
    }

    $sprintName = "Sprint - " . $mainRace['nom'];
    $year = $mainRace['annee'];
    $mainDate = $mainRace['date_course'];

    // 2. Check or Create Sprint Course
    $stmtSprint = $pdo->prepare("SELECT id FROM courses WHERE nom = ? AND annee = ?");
    $stmtSprint->execute([$sprintName, $year]);
    $sprintId = $stmtSprint->fetchColumn();

    if (!$sprintId) {
        $sprintDate = date('Y-m-d', strtotime($mainDate . ' -1 day'));
        $stmtIns = $pdo->prepare("INSERT INTO courses (annee, round, nom, lieu, date_course, statut) VALUES (?, ?, ?, ?, ?, 'Terminé')");
        $stmtIns->execute([$year, $mainRace['round'], $sprintName, $mainRace['lieu'], $sprintDate]);
        $sprintId = $pdo->lastInsertId();
        echo "  [NOUVEAU] Course créée : $sprintName (ID: $sprintId)\n";
    } else {
        echo "  [EXISTANT] Course trouvée : $sprintName (ID: $sprintId)\n";
    }

    // 3. Clear existing results
    $stmtDel = $pdo->prepare("DELETE FROM resultats WHERE course_id = ?");
    $stmtDel->execute([$sprintId]);
    echo "  Anciens résultats effacés.\n";

    // 4. Parse Table
    $rows = $xpath->query("//table[contains(@class,'resultsarchive-table')]/tbody/tr");
    if ($rows->length === 0) {
        $rows = $xpath->query("//tr");
    }

    echo "  Nombre de lignes trouvées: " . $rows->length . "\n";

    if ($rows->length === 0) {
        echo "ERREUR: Tableau de résultats vide ou structure changée.\n";
        return;
    }

    $top3 = [];
    $resultsCount = 0;

    foreach ($rows as $row) {
        $cols = $xpath->query(".//td", $row);
        if ($cols->length < 4) continue;

        $posText = trim($cols->item(0)->textContent);
        if (!is_numeric($posText) && $posText !== 'NC' && $posText !== 'DQ') continue; 
        
        $position = (int)$posText;
        if ($position == 0 && $posText !== 'NC' && $posText !== 'DQ') continue;

        // Find Driver Name
        $piloteNomFinal = "";
        $pilotePrenom = "";
        
        for ($i = 0; $i < $cols->length; $i++) {
            $lastNameSpans = $xpath->query(".//span[contains(@class, 'hide-for-mobile')]", $cols->item($i));
            if ($lastNameSpans->length > 0) {
                $piloteNomFinal = trim($lastNameSpans->item(0)->textContent);
                $firstNameSpans = $xpath->query(".//span[contains(@class, 'hide-for-tablet')]", $cols->item($i));
                if ($firstNameSpans->length > 0) {
                    $pilotePrenom = trim($firstNameSpans->item(0)->textContent);
                }
                break;
            }
        }

        if (empty($piloteNomFinal)) {
             // Fallback: Parse "Firstname LastnameCODE" string from Column 2
             $candidate = trim($cols->item(2)->textContent); 
             // NBSP cleanup (critical for F1.com names)
             $candidate = str_replace(["\xC2\xA0", "\xA0"], ' ', $candidate);
             // Remove newlines and condense spaces
             $candidate = preg_replace('/\s+/', ' ', $candidate);
             
             // Check if it ends with 3 uppercase letters (The specific F1 code)
             // e.g. "Lewis HamiltonHAM"
             if (preg_match('/^(.*)([A-Z]{3})$/', $candidate, $matches)) {
                 $namePart = trim($matches[1]); // "Lewis Hamilton"
                 // Additional NBSP cleanup just in case
                 $namePart = str_replace(["\xC2\xA0", "\xA0"], ' ', $namePart);
                 $namePart = preg_replace('/\s+/', ' ', $namePart);
                 $namePart = trim($namePart);

                 // Now extract last name. 
                 // Simple split by space
                 $parts = explode(' ', $namePart);
                 $piloteNomFinal = end($parts); // "Hamilton"
                 
                 // Capture first name parts if needed for disambiguation?
                 if (count($parts) > 1) {
                     array_pop($parts);
                     $pilotePrenom = implode(' ', $parts);
                 }
             } else {
                 // Try brute force taking the last word if no code found (rare)
                 $parts = explode(' ', $candidate);
                 $piloteNomFinal = end($parts);
             }
        }

        // Clean name
        if (strpos($piloteNomFinal, 'Hülkenberg') !== false || strpos($piloteNomFinal, 'Hulkenberg') !== false) $piloteNomFinal = 'Hülkenberg';
        if (strpos($piloteNomFinal, 'Pérez') !== false || strpos($piloteNomFinal, 'Perez') !== false) $piloteNomFinal = 'Pérez';
        if (strpos($piloteNomFinal, 'Zhou') !== false) $piloteNomFinal = 'Guanyu';

        // Debug Hex
        // echo "    DEBUG HEX: " . bin2hex($piloteNomFinal) . "\n";

        // Try Exact Match
        $stmtPilote = $pdo->prepare("SELECT id, nom FROM pilotes WHERE nom LIKE ? LIMIT 1");
        // Try strict match first before fallback
        $stmtPilote->execute([$piloteNomFinal]); 
        $pilote = $stmtPilote->fetch();
        
        if (!$pilote) {
             // Try prefix/suffix match
             $stmtPilote->execute(["%$piloteNomFinal%"]);
             $pilote = $stmtPilote->fetch();
        }

        if (!$pilote && !empty($pilotePrenom)) {
            $fullName = $pilotePrenom . " " . $piloteNomFinal;
            $stmtPilote2 = $pdo->prepare("SELECT id, nom FROM pilotes WHERE nom LIKE ? LIMIT 1");
            $stmtPilote2->execute(["%$fullName%"]);
            $pilote = $stmtPilote2->fetch();
        }
        
        if (!$pilote && stripos($piloteNomFinal, 'Zhou') !== false) {
             $stmtPilote = $pdo->prepare("SELECT id FROM pilotes WHERE nom LIKE '%Zhou%' LIMIT 1");
             $stmtPilote->execute();
             $pilote = $stmtPilote->fetch();
        }

        if (!$pilote) {
            echo "    [ATTENTION] Pilote inconnu dans DB: '$piloteNomFinal' (Prénom: $pilotePrenom)\n";
            continue;
        }

        $piloteId = $pilote['id'];

        // Points - usually last column
        $ptsText = trim($cols->item($cols->length - 1)->textContent); 
        $points = (int)$ptsText;

        // Time - usually 2nd to last column
        $timeText = trim($cols->item($cols->length - 2)->textContent);

        $stmtRes = $pdo->prepare("INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (?, ?, ?, ?, ?)");
        $stmtRes->execute([$sprintId, $piloteId, $position, $timeText, $points]);

        $resultsCount++;
        
        if ($position <= 3) {
            $top3[$position] = ['id' => $piloteId, 'temps' => $timeText];
        }
        
        echo "    -> Pos $position: " . $pilote['nom'] . " ($points pts)\n";
    }
    
    if (!empty($top3)) {
        $stmtUpd = $pdo->prepare("UPDATE courses SET p1_pilote_id=?, p1_temps=?, p2_pilote_id=?, p2_temps=?, p3_pilote_id=?, p3_temps=? WHERE id=?");
        $stmtUpd->execute([
            $top3[1]['id']??null, $top3[1]['temps']??null,
            $top3[2]['id']??null, $top3[2]['temps']??null,
            $top3[3]['id']??null, $top3[3]['temps']??null,
            $sprintId
        ]);
    }
    
    echo "  Total importé: $resultsCount pilotes.\n\n";
}

$sprints = [
    'Chine' => 'https://www.formula1.com/en/results/2025/races/1255/china/sprint-results',
    'Miami' => 'https://www.formula1.com/en/results/2025/races/1259/miami/sprint-results',
    'Belgique' => 'https://www.formula1.com/en/results/2025/races/1265/belgium/sprint-results',
    'États-Unis' => 'https://www.formula1.com/en/results/2025/races/1271/united-states/sprint-results',
    'Brésil' => 'https://www.formula1.com/en/results/2025/races/1273/brazil/sprint-results',
    'Qatar' => 'https://www.formula1.com/en/results/2025/races/1275/qatar/sprint-results'
];

foreach ($sprints as $keyword => $url) {
    parseSprintResults($pdo, $url, $keyword);
    sleep(1);
}

echo "</pre>";
?>