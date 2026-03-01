<?php
require_once 'config.php';

$year = isset($_GET['year']) ? (int)$_GET['year'] : 2026;
if (!in_array($year, [2025, 2026])) {
    $year = 2026;
}

$stmt = $pdo->prepare("
    SELECT c.*, 
           p1.nom as p1_nom, p1.image_url as p1_img,
           p2.nom as p2_nom, p2.image_url as p2_img,
           p3.nom as p3_nom, p3.image_url as p3_img
    FROM courses c
    LEFT JOIN pilotes p1 ON c.p1_pilote_id = p1.id
    LEFT JOIN pilotes p2 ON c.p2_pilote_id = p2.id
    LEFT JOIN pilotes p3 ON c.p3_pilote_id = p3.id
    WHERE c.annee = ?
    ORDER BY c.date_course ASC
");
$stmt->execute([$year]);
$allCourses = $stmt->fetchAll();

$groupedCourses = [];
foreach ($allCourses as $c) {
    if (!isset($groupedCourses[$c['round']])) {
        $groupedCourses[$c['round']] = [
            'gp' => null,
            'sprint' => null
        ];
    }
    if (stripos($c['nom'], 'Sprint') !== false) {
        $groupedCourses[$c['round']]['sprint'] = $c;
    } else {
        $groupedCourses[$c['round']]['gp'] = $c;
    }
}

$userFavorites = [];
if (isset($_SESSION['user_id'])) {
    $stmtFav = $pdo->prepare("SELECT pilote_id FROM users_pilotes_favoris WHERE user_id = ?");
    $stmtFav->execute([$_SESSION['user_id']]);
    $userFavorites = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}

$stmtPilotes = $pdo->query("SELECT id, nom, prenom, image_url, ecurie_id FROM pilotes ORDER BY ecurie_id, nom");
$allPilots = $stmtPilotes->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier & Résultats <?= $year ?> - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="CSS/year_selector.css">
    <link rel="stylesheet" href="CSS/calendar_style.css">
    <style>
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.8); 
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: var(--bg-card);
            margin: 10% auto; 
            padding: 2rem;
            border: 1px solid var(--border-color);
            width: 80%; 
            max-width: 600px;
            border-radius: 15px;
            color: var(--text-color);
            position: relative;
        }
        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-modal:hover {
            color: #fff;
        }
        .filter-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .filter-btn {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-btn.active {
            background: var(--primary-color);
            color: #fff;
        }
        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .result-pos { font-weight: bold; width: 30px; }
        .result-driver { flex-grow: 1; display: flex; align-items: center; gap: 10px; }
        .result-time { font-family: monospace; }
        .driver-mini-img { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }
        .favorite-row .result-driver span { color: #FFD700; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1 class="page-title">🏁 Calendrier & Résultats <?= $year ?></h1>
        
        <div class="year-selector">
            <a href="?year=2025" class="year-btn <?= $year == 2025 ? 'active' : '' ?>">2025</a>
            <a href="?year=2026" class="year-btn <?= $year == 2026 ? 'active' : '' ?>">2026</a>
        </div>
        
        <div class="calendar-container" style="text-align: left; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem;">
            <?php foreach ($groupedCourses as $round => $data): 
                $course = $data['gp'] ?? $data['sprint'];
                $hasSprint = ($data['gp'] && $data['sprint']);
                $normalizedStatus = str_replace('é', 'e', $course['statut']);
                $isCompleted = ($normalizedStatus === 'Termine' || $normalizedStatus === 'Terminé');
                
                // Status Logic
                $statusClass = 'completed';
                $displayStatus = $course['statut'];
                
                if (!$isCompleted) {
                    $statusClass = 'upcoming';
                    $raceDate = new DateTime($course['date_course']);
                    $now = new DateTime(); 
                    $raceDate->setTime(0,0);
                    $now->setTime(0,0);
                    
                    if ($raceDate > $now) {
                        $interval = $now->diff($raceDate);
                        $days = $interval->days;
                        $displayStatus = "J-" . $days;
                    } elseif ($raceDate == $now) {
                        $displayStatus = "Aujourd'hui";
                    } else {
                        $displayStatus = "À venir"; 
                    }
                }
            ?>
            <div class="race-card">
                <div class="race-content">
                    <div class="race-header">
                        <span class="race-round">Course <?= $course['round'] ?></span>
                        <span class="race-status <?= $statusClass ?>"><?= htmlspecialchars($displayStatus) ?></span>
                    </div>
                    <h3><?= htmlspecialchars($course['nom']) ?></h3>
                    <p class="race-date">📅 <?= date('d M Y', strtotime($course['date_course'])) ?></p>
                    
                    <?php if ($isCompleted && $course['p1_nom']): ?>
                        <div class="podium-container">
                            <div class="podium-place podium-p2">
                                <?php if ($course['p2_img']): ?><img src="<?= htmlspecialchars($course['p2_img']) ?>" class="podium-img" alt="P2"><?php endif; ?>
                                <span class="podium-name"><?= htmlspecialchars($course['p2_nom']) ?></span>
                            </div>
                            <div class="podium-place podium-p1">
                                <?php if ($course['p1_img']): ?><img src="<?= htmlspecialchars($course['p1_img']) ?>" class="podium-img" alt="P1"><?php endif; ?>
                                <span class="podium-name"><?= htmlspecialchars($course['p1_nom']) ?></span>
                            </div>
                            <div class="podium-place podium-p3">
                                <?php if ($course['p3_img']): ?><img src="<?= htmlspecialchars($course['p3_img']) ?>" class="podium-img" alt="P3"><?php endif; ?>
                                <span class="podium-name"><?= htmlspecialchars($course['p3_nom']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($isCompleted && $course['p1_nom']): ?>
                <div class="race-actions-v4">
                    <a href="resultat_course.php?id=<?= $course['id'] ?>" class="btn-v4 btn-v4-gp">
                        Résultats <span class="ico">→</span>
                    </a>
                    <?php if ($hasSprint && isset($data['sprint'])): ?>
                        <a href="resultat_course.php?id=<?= $data['sprint']['id'] ?>" class="btn-v4 btn-v4-sprint">
                            Sprint <span class="ico">→</span>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($groupedCourses)): ?>
                <p>Aucune course trouvée pour cette année.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
