<?php
require_once 'config.php';

$year = 2025;

// 1. Récupérer toutes les courses 2025 et les grouper par round en PHP
$stmt = $pdo->prepare("
    SELECT c.*, 
           p1.nom as p1_nom, p1.prenom as p1_prenom, p1.image_url as p1_img,
           p2.nom as p2_nom, p2.prenom as p2_prenom, p2.image_url as p2_img,
           p3.nom as p3_nom, p3.prenom as p3_prenom, p3.image_url as p3_img
    FROM courses c
    LEFT JOIN pilotes p1 ON c.p1_pilote_id = p1.id
    LEFT JOIN pilotes p2 ON c.p2_pilote_id = p2.id
    LEFT JOIN pilotes p3 ON c.p3_pilote_id = p3.id
    WHERE c.annee = ?
    ORDER BY c.round ASC, c.nom DESC
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

// 2. Classement Pilotes 2025
$stmtPilotes = $pdo->prepare("
    SELECT p.id, p.nom, p.prenom, p.image_url, e.nom as team_name, e.couleur as team_color, SUM(r.points) as total_points 
    FROM resultats r 
    JOIN pilotes p ON r.pilote_id = p.id 
    JOIN ecuries e ON p.ecurie_id = e.id 
    JOIN courses c ON r.course_id = c.id
    WHERE c.annee = ?
    GROUP BY p.id 
    ORDER BY total_points DESC
");
$stmtPilotes->execute([$year]);
$driverStandings = $stmtPilotes->fetchAll();

// 3. Classement Écuries 2025
$stmtTeams = $pdo->prepare("
    SELECT e.id, e.nom, e.couleur, SUM(r.points) as total_points 
    FROM resultats r 
    JOIN pilotes p ON r.pilote_id = p.id 
    JOIN ecuries e ON p.ecurie_id = e.id 
    JOIN courses c ON r.course_id = c.id
    WHERE c.annee = ?
    GROUP BY e.id 
    ORDER BY total_points DESC
");
$stmtTeams->execute([$year]);
$teamStandings = $stmtTeams->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saison 2025 - F1 Aura</title>
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <style>
        .saison-nav {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 3rem;
            position: sticky;
            top: 70px;
            background: var(--bg-dark);
            padding: 1rem;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }
        .saison-nav a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .saison-nav a:hover {
            color: var(--primary-color);
        }
        .section-title {
            margin-top: 4rem;
            margin-bottom: 2rem;
            text-align: center;
            border-bottom: 2px solid var(--primary-color);
            display: inline-block;
            padding-bottom: 0.5rem;
        }
        .standings-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-card);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 3rem;
        }
        .standings-table th, .standings-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .standings-table th {
            background: rgba(255,255,255,0.05);
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .rank-col { width: 50px; text-align: center; font-weight: bold; }
        .points-col { width: 100px; text-align: right; font-weight: bold; color: var(--primary-color); }
        .team-indicator { width: 5px; padding: 0 !important; }
        
        /* Podium Styles from calendrier.php */
        .podium-container {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            margin-top: 1rem;
            gap: 10px;
            height: 120px;
        }
        .podium-place { text-align: center; position: relative; }
        .podium-img {
            width: 50px; height: 50px; border-radius: 50%; object-fit: cover;
            border: 2px solid #fff; margin-bottom: 5px; background-color: #333;
            object-position: top;
        }
        .podium-p1 .podium-img { width: 70px; height: 70px; border-color: #FFD700; }
        .podium-p2 .podium-img { border-color: #C0C0C0; }
        .podium-p3 .podium-img { border-color: #CD7F32; }

        /* V4 - Design Final Premium */
        .race-card {
            background: linear-gradient(145deg, #1e1e2e, #12121a);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.06);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }
        .race-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        }
        .race-content {
            padding: 1.5rem;
            flex-grow: 1;
        }
        .race-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
        }
        .race-round {
            background: rgba(227, 6, 19, 0.15);
            color: #e30613;
            border: 1px solid rgba(227, 6, 19, 0.35);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .race-status.completed {
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.45);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .race-card h3 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.35rem;
            letter-spacing: -0.3px;
        }
        .race-date {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
            margin-bottom: 1rem;
        }

        /* V5 - Pill Buttons Modern */
        .race-actions-v4 {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-v4 {
            padding: 0.55rem 1.4rem;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.25s ease;
            border: none;
        }

        .btn-v4-gp {
            background: var(--primary-color);
            color: #fff;
            box-shadow: 0 4px 18px rgba(227, 6, 19, 0.35);
        }
        .btn-v4-gp:hover {
            background: #ff1a29;
            box-shadow: 0 6px 24px rgba(227, 6, 19, 0.55);
            transform: translateY(-2px);
            color: #fff;
        }

        .btn-v4-sprint {
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.12);
        }
        .btn-v4-sprint:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.25);
        }

        .btn-v4 .ico {
            font-size: 0.7rem;
            transition: transform 0.2s;
        }
        .btn-v4:hover .ico {
            transform: translateX(3px);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1 class="page-title">🏆 Bilan de la Saison 2025</h1>
        
        <nav class="saison-nav">
            <a href="#calendrier">Calendrier</a>
            <a href="#pilotes">Classement Pilotes</a>
            <a href="#ecuries">Classement Écuries</a>
        </nav>

        <section id="calendrier">
            <h2 class="section-title">🏁 Résultats des Grands Prix</h2>
            <div class="calendar-container" style="text-align: left;">
                <?php foreach ($groupedCourses as $round => $data): 
                    $course = $data['gp'] ?? $data['sprint'];
                    $hasSprint = ($data['gp'] && $data['sprint']);
                ?>
                <div class="race-card">
                    <div class="race-content">
                        <div class="race-header">
                            <span class="race-round">ROUND <?= $course['round'] ?></span>
                            <?php if ($hasSprint): ?>
                                <span class="race-status completed">Format Sprint</span>
                            <?php endif; ?>
                        </div>
                        <h3><?= htmlspecialchars($course['nom']) ?></h3>
                        <p class="race-date">📅 <?= date('d M Y', strtotime($course['date_course'])) ?></p>
                        
                        <?php if ($course['p1_nom']): ?>
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

                    <?php if ($course['p1_nom']): ?>
                    <div class="race-actions-v4">
                        <a href="resultat_course.php?id=<?= $course['id'] ?>" class="btn-v4 btn-v4-gp">
                            Résultats <span class="ico">→</span>
                        </a>
                        <?php if ($hasSprint): ?>
                            <a href="resultat_course.php?id=<?= $data['sprint']['id'] ?>" class="btn-v4 btn-v4-sprint">
                                Sprint <span class="ico">→</span>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="pilotes" style="text-align: center;">
            <h2 class="section-title">👤 Classement Mondial des Pilotes</h2>
            <table class="standings-table">
                <thead>
                    <tr>
                        <th class="rank-col">Pos</th>
                        <th class="team-indicator"></th>
                        <th>Pilote</th>
                        <th>Écurie</th>
                        <th class="points-col">Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($driverStandings as $d): ?>
                    <tr>
                        <td class="rank-col"><?= $rank++ ?></td>
                        <td class="team-indicator" style="background-color: <?= $d['team_color'] ?>;"></td>
                        <td><strong><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($d['team_name']) ?></td>
                        <td class="points-col"><?= $d['total_points'] ?> CP</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="ecuries" style="text-align: center;">
            <h2 class="section-title">🏎️ Classement des Constructeurs</h2>
            <table class="standings-table">
                <thead>
                    <tr>
                        <th class="rank-col">Pos</th>
                        <th class="team-indicator"></th>
                        <th>Écurie</th>
                        <th class="points-col">Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($teamStandings as $t): ?>
                    <tr>
                        <td class="rank-col"><?= $rank++ ?></td>
                        <td class="team-indicator" style="background-color: <?= $t['couleur'] ?>;"></td>
                        <td><strong><?= htmlspecialchars($t['nom']) ?></strong></td>
                        <td class="points-col"><?= $t['total_points'] ?> CP</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
