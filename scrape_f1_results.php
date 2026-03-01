<?php
require_once 'config.php';

set_time_limit(300);

echo "<h1>Lancement du scraping des résultats F1 2025</h1>";
echo "<pre>";

function getHtmlContent($url) {
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
        ]
    ]);
    return file_get_contents($url, false, $context);
}

function parseRaceResults($pdo, $raceUrl, $courseId) {
    echo "Traitement de la course ID $courseId ($raceUrl)...\n";
    $html = getHtmlContent($raceUrl);
    
    if (!$html) {
        echo "ERREUR: Impossible de récupérer $raceUrl\n";
        return;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $stmt = $pdo->prepare("DELETE FROM resultats WHERE course_id = ?");
    $stmt->execute([$courseId]);

    $rows = $xpath->query("//table/tbody/tr");
    
    if ($rows->length === 0) {
        $rows = $xpath->query("//div[contains(@class, 'resultsarchive-wrapper')]//table/tbody/tr");
    }

    if ($rows->length === 0) {
        echo "ERREUR: Impossible de trouver le tableau des résultats.\n";
        return;
    }

    $top3 = [];

    foreach ($rows as $row) {
        $cols = $xpath->query(".//td", $row);
        
        if ($cols->length < 5) continue;

        $posText = $cols->item(0) ? trim($cols->item(0)->textContent) : '';
        $driverText = $cols->item(2) ? trim($cols->item(2)->textContent) : ''; 
        $carText = $cols->item(3) ? trim($cols->item(3)->textContent) : '';
        $timeText = $cols->item(5) ? trim($cols->item(5)->textContent) : '';
        $ptsText = $cols->item(6) ? trim($cols->item(6)->textContent) : '0';

        $firstNameNode = $xpath->query(".//span[contains(@class, 'hide-for-tablet')]", $cols->item(3));
        $lastNameNode = $xpath->query(".//span[contains(@class, 'hide-for-mobile')]", $cols->item(3)); 
        
        $pilotePrenom = "";
        $piloteNom = "";

        if ($firstNameNode->length > 0) $pilotePrenom = trim($firstNameNode->item(0)->textContent);
        if ($lastNameNode->length > 0) $piloteNom = trim($lastNameNode->item(0)->textContent);
        
        if (empty($piloteNom)) {
             $piloteNom = $driverText; 
        }

        $ecurieNom = $carText;

        $points = (int)$ptsText;

        $temps = $timeText;
        $position = (int)$posText;

        if (!is_numeric($posText)) {
            $position = 99;
            if (strpos($timeText, 'DNF') !== false || strpos($timeText, 'Ret') !== false) {
                 $temps = "DNF";
            }
        }
        
        echo "  -> Found: Pilote=$piloteNom ($pilotePrenom), Ecurie=$ecurieNom, Pos=$position, Temps=$temps, Pts=$points\n";

        $stmtEcurie = $pdo->prepare("SELECT id FROM ecuries WHERE nom LIKE ? LIMIT 1");
        $stmtEcurie->execute(["%" . explode(' ', $ecurieNom)[0] . "%"]); 
        $ecurieId = $stmtEcurie->fetchColumn();

        if (!$ecurieId && !empty($ecurieNom)) {
            $stmtInsEcurie = $pdo->prepare("INSERT INTO ecuries (nom) VALUES (?)");
            $stmtInsEcurie->execute([$ecurieNom]);
            $ecurieId = $pdo->lastInsertId();
            echo "     [NEW ECURIE] $ecurieNom (ID: $ecurieId)\n";
        }

        $fullNameClean = $piloteNom;
        if (preg_match('/^(.*)([A-Z]{3,4})$/u', $piloteNom, $matches)) {
            $fullNameClean = trim($matches[1]);
        } else {
            // Deuxième tentative : suppression des 3 dernières lettres si elles sont majuscules
            $fullNameClean = preg_replace('/[A-Z]{3}$/u', '', $piloteNom);
        }
        $fullNameClean = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $fullNameClean);
        $fullNameClean = trim($fullNameClean);

        if (empty($pilotePrenom)) {
            $parts = explode(' ', $fullNameClean);
            if (count($parts) > 1) {
                $piloteNomFinal = array_pop($parts);
                $pilotePrenomFinal = implode(' ', $parts);
            } else {
                $piloteNomFinal = $fullNameClean;
                $pilotePrenomFinal = "";
            }
        } else {
            $piloteNomFinal = $fullNameClean;
            $pilotePrenomFinal = $pilotePrenom;
        }

        if (empty($piloteNomFinal)) $piloteNomFinal = $driverText;

        echo "  -> Parsed: $pilotePrenomFinal $piloteNomFinal (from $driverText)\n";
        
        $stmtPilote = $pdo->prepare("SELECT id FROM pilotes WHERE nom LIKE ? OR nom LIKE ? LIMIT 1");
        $stmtPilote->execute(["%$piloteNomFinal%", "$piloteNomFinal%"]);
        $piloteId = $stmtPilote->fetchColumn();

        if (!$piloteId) {
            echo "     [PILOTE NON TROUVÉ] $pilotePrenomFinal $piloteNomFinal - Création...\n";
            $stmtInsPilote = $pdo->prepare("INSERT INTO pilotes (nom, prenom, ecurie_id) VALUES (?, ?, ?)");
            $stmtInsPilote->execute([$piloteNomFinal, $pilotePrenomFinal, $ecurieId]);
            $piloteId = $pdo->lastInsertId();
        } else {
             $stmtUpdPilote = $pdo->prepare("UPDATE pilotes SET ecurie_id = ? WHERE id = ?");
             $stmtUpdPilote->execute([$ecurieId, $piloteId]);
        }

        $stmtRes = $pdo->prepare("INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (?, ?, ?, ?, ?)");
        $stmtRes->execute([$courseId, $piloteId, $position, $temps, $points]);

        if ($position >= 1 && $position <= 3) {
            $top3[$position] = ['id' => $piloteId, 'temps' => $temps];
        }
    }

    if (!empty($top3)) {
        $stmtUpdateCourse = $pdo->prepare("
            UPDATE courses SET 
                statut = 'Terminé',
                p1_pilote_id = ?, p1_temps = ?,
                p2_pilote_id = ?, p2_temps = ?,
                p3_pilote_id = ?, p3_temps = ?
            WHERE id = ?
        ");
        $stmtUpdateCourse->execute([
            $top3[1]['id'] ?? null, $top3[1]['temps'] ?? null,
            $top3[2]['id'] ?? null, $top3[2]['temps'] ?? null,
            $top3[3]['id'] ?? null, $top3[3]['temps'] ?? null,
            $courseId
        ]);
        echo "  -> Course mise à jour avec le Top 3.\n";
    }
}


$year = 2025;
$indexUrl = "https://www.formula1.com/en/results/$year/races";
$html = getHtmlContent($indexUrl);

if (!$html) {
    die("Impossible de lire l'index : $indexUrl");
}

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$links = $xpath->query("//a[contains(@href, '/en/results/$year/races/') and (contains(@href, '/race-result') or contains(@href, '/sprint-results'))]");

echo "Sessions trouvées : " . $links->length . "\n";

$processedCourses = [];

foreach ($links as $link) {
    if (!($link instanceof DOMElement)) continue;
    $href = $link->getAttribute('href');
    $isSprint = (strpos($href, '/sprint-results') !== false);
    
    if (strpos($href, 'http') === false) {
        $fullUrl = "https://www.formula1.com" . $href;
    } else {
        $fullUrl = $href;
    }
    
    $raceSlug = '';
    if (preg_match('/\/races\/\d+\/([^\/]+)\//', $href, $matches)) {
        $raceSlug = $matches[1];
    } else {
        // Fallback or better regex for simple paths
        $parts = explode('/', trim($href, '/'));
        // usually .../races/ID/SLUG/TYPE
        // index 4 or 5 depending on prefix
        // Let's assume the one before 'sprint-results' or 'race-result' is the slug
        $raceSlug = $parts[count($parts)-2];
    }
    
    $uniqueKey = $raceSlug . ($isSprint ? '-sprint' : '-race');
    if (in_array($uniqueKey, $processedCourses)) continue; 
    $processedCourses[] = $uniqueKey;

    echo "Traitement de : $uniqueKey\n";

    $mapping = [
        'bahrain' => 'Bahreïn',
        'saudi-arabia' => 'Arabie',
        'australia' => 'Australie',
        'japan' => 'Japon',
        'china' => 'Chine',
        'miami' => 'Miami',
        'emilia-romagna' => 'Imola', 
        'monaco' => 'Monaco',
        'canada' => 'Canada',
        'spain' => 'Espagne',
        'austria' => 'Autriche',
        'great-britain' => 'Grande-Bretagne',
        'hungary' => 'Hongrie',
        'belgium' => 'Belgique',
        'netherlands' => 'Pays-Bas',
        'italy' => 'Monza', 
        'azerbaijan' => 'Azerbaïdjan',
        'singapore' => 'Singapour',
        'united-states' => 'Austin', 
        'mexico' => 'Mexique',
        'brazil' => 'Brésil',
        'las-vegas' => 'Las Vegas',
        'qatar' => 'Qatar',
        'abu-dhabi' => 'Abou Dabi'
    ];

    $dbTerm = $mapping[$raceSlug] ?? ucfirst($raceSlug);
    $courseSearchName = $isSprint ? "Sprint de $dbTerm" : "%$dbTerm%";
    
    $stmt = $pdo->prepare("SELECT id, nom FROM courses WHERE annee = ? AND nom LIKE ?");
    $stmt->execute([$year, $isSprint ? "Sprint de " . $dbTerm : "%" . $dbTerm . "%"]);
    $course = $stmt->fetch();

    if (!$course && $isSprint) {
        $stmtParent = $pdo->prepare("SELECT lieu, date_course, round FROM courses WHERE annee = ? AND nom LIKE ? LIMIT 1");
        $stmtParent->execute([$year, "%$dbTerm%"]);
        $parent = $stmtParent->fetch();
        
        if ($parent) {
            $sprintDate = date('Y-m-d', strtotime($parent['date_course'] . ' -1 day'));
            $stmtIns = $pdo->prepare("INSERT INTO courses (annee, round, nom, lieu, date_course, statut) VALUES (?, ?, ?, ?, ?, 'Terminé')");
            $stmtIns->execute([$year, $parent['round'], "Sprint de $dbTerm", $parent['lieu'], $sprintDate]);
            $courseId = $pdo->lastInsertId();
            echo "     [NEW SPRINT COURSE] Sprint de $dbTerm (ID: $courseId)\n";
            parseRaceResults($pdo, $fullUrl, $courseId);
        }
    } elseif ($course) {
        echo "Match trouvé DB ID: " . $course['id'] . " (" . $course['nom'] . ")\n";
        parseRaceResults($pdo, $fullUrl, $course['id']);

        // Check for Sprint link within the race page itself
        if (!$isSprint) {
            $raceHtml = getHtmlContent($fullUrl);
            if ($raceHtml) {
                $raceDom = new DOMDocument();
                @$raceDom->loadHTML($raceHtml);
                $raceXpath = new DOMXPath($raceDom);
                
                // Look for links with "Sprint" in text or href
                $sprintLinks = $raceXpath->query("//a[contains(@href, 'sprint') or contains(translate(text(), 'SPRINT', 'sprint'), 'sprint')]");
                
                if ($sprintLinks->length > 0) {
                    $node = $sprintLinks->item(0);
                    if ($node instanceof DOMElement) {
                        $sprintHref = $node->getAttribute('href');
                        // Resolve relative URL
                        if (strpos($sprintHref, 'http') === false) {
                            $sprintUrl = "https://www.formula1.com" . $sprintHref;
                        } else {
                            $sprintUrl = $sprintHref;
                        }

                        echo "  -> Found potential Sprint link: $sprintUrl\n";

                        // Verify if it's actually the results page (simple check)
                        $sprintContent = @getHtmlContent($sprintUrl);
                        if ($sprintContent && strpos($sprintContent, 'resultsarchive-table') !== false) {
                            // Create sprint course logic (same as before)
                            $sprintName = "Sprint - " . $course['nom'];
                            $stmtSprint = $pdo->prepare("SELECT id FROM courses WHERE nom = ? AND annee = ?");
                            $stmtSprint->execute([$sprintName, $year]);
                            $sprintId = $stmtSprint->fetchColumn();

                            if (!$sprintId) {
                                // Logic to create the sprint race if it doesn't exist
                                $stmtMain = $pdo->prepare("SELECT lieu, date_course, round FROM courses WHERE id = ?");
                                $stmtMain->execute([$course['id']]);
                                $mainRace = $stmtMain->fetch();

                                if ($mainRace) {
                                    $sprintDate = date('Y-m-d', strtotime($mainRace['date_course'] . ' -1 day'));
                                    // Insert the new sprint race
                                    $stmtIns = $pdo->prepare("INSERT INTO courses (annee, round, nom, lieu, date_course, statut) VALUES (?, ?, ?, ?, ?, 'Terminé')");
                                    $stmtIns->execute([$year, $mainRace['round'], $sprintName, $mainRace['lieu'], $sprintDate]);
                                    $sprintId = $pdo->lastInsertId();
                                    echo "     [NEW SPRINT] $sprintName (ID: $sprintId)\n";
                                }
                            } else {
                                echo "     [SPRINT EXISTS] $sprintName (ID: $sprintId)\n";
                            }

                            // If we have a sprint ID (either existing or just created), parse results
                            if ($sprintId) {
                                parseRaceResults($pdo, $sprintUrl, $sprintId);
                            }
                        }
                    }
                }
            }
        }

        sleep(1);
    } else {
        echo "Pas de match DB pour $raceSlug ($dbTerm) - Ignoré.\n";
    }
}

echo "\nTerminé.";
echo "</pre>";
?>
