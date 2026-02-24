<?php
require_once 'config.php';

$year = 2026;

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
    <style>
        .year-selector {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .year-btn {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: bold;
        }
        .year-btn.active, .year-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        
        .podium-container {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            margin-top: 1rem;
            gap: 10px;
            height: 120px;
        }
        .podium-place {
            text-align: center;
            position: relative;
        }
        .podium-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            margin-bottom: 5px;
            background-color: #333;
            object-position: top;
        }
        .podium-p1 .podium-img {
            width: 70px;
            height: 70px;
            border-color: #FFD700; 
        }
        .podium-p2 .podium-img {
            border-color: #C0C0C0; 
        }
        .podium-p3 .podium-img {
            border-color: #CD7F32; 
        }
        .podium-name {
            font-size: 0.8rem;
            font-weight: bold;
            display: block;
        }
        .podium-time {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.7);
            display: block;
        }
        
        .race-card {
            transition: transform 0.3s;
        }
        .race-card:hover {
            transform: translateY(-5px);
        }
        
        
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
        
        
        <div class="calendar-container">
            <?php foreach ($groupedCourses as $round => $data): 
                $course = $data['gp'] ?? $data['sprint'];
                $hasSprint = ($data['gp'] && $data['sprint']);
                $normalizedStatus = str_replace('é', 'e', $course['statut']);
                $statusClass = ($normalizedStatus === 'Termine' || $normalizedStatus === 'Terminé') ? 'completed' : 'upcoming';
                $isCompleted = ($normalizedStatus === 'Termine' || $normalizedStatus === 'Terminé');
            ?>
            <div class="race-card">
                <div class="race-header">
                    <span class="race-round">Round <?= $course['round'] ?></span>
                    <span class="race-status <?= $statusClass ?>"><?= htmlspecialchars($course['statut']) ?></span>
                </div>
                <h3><?= htmlspecialchars($course['nom']) ?></h3>
                <p class="race-location">📍 <?= htmlspecialchars($course['lieu']) ?></p>
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

                <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; align-items: center;">
                    <?php if ($isCompleted): ?>
                        <a href="resultat_course.php?id=<?= $course['id'] ?>" class="btn btn-sm">Classement Grand Prix</a>
                        <?php if ($hasSprint): ?>
                            <a href="resultat_course.php?id=<?= $data['sprint']['id'] ?>" class="btn btn-sm" style="background-color: #444;">Classement Sprint</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge">Course à venir</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($courses)): ?>
                <p>Aucune course trouvée pour cette année.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
