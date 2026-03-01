<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: calendrier.php');
    exit;
}

$courseId = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: calendrier.php');
    exit;
}

$stmtPartner = $pdo->prepare("SELECT id, nom FROM courses WHERE round = ? AND annee = ? AND id != ?");
$stmtPartner->execute([$course['round'], $course['annee'], $courseId]);
$partner = $stmtPartner->fetch();

$stmtResults = $pdo->prepare("
    SELECT r.position, r.temps, r.points, 
           COALESCE(p.nom, 'Inconnu') as nom, 
           COALESCE(p.prenom, '') as prenom, 
           COALESCE(p.image_url, 'PICS/drivers/default.png') as img, 
           r.pilote_id, 
           COALESCE(e.nom, '-') as ecurie
    FROM resultats r
    LEFT JOIN pilotes p ON r.pilote_id = p.id
    LEFT JOIN ecuries e ON p.ecurie_id = e.id
    WHERE r.course_id = ?
    ORDER BY CAST(r.position AS UNSIGNED) ASC
");
$stmtResults->execute([$courseId]);
$allPilots = $stmtResults->fetchAll();

$userFavorites = [];
if (isset($_SESSION['user_id'])) {
    $stmtFav = $pdo->prepare("SELECT pilote_id FROM users_pilotes_favoris WHERE user_id = ?");
    $stmtFav->execute([$_SESSION['user_id']]);
    $userFavorites = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - <?= htmlspecialchars($course['nom']) ?> - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #E10600;
            --bg-dark: #15151e;
            --bg-card: #1f1f2b;
            --text-light: #f0f0f0;
            --border-color: #38384f;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            margin: 0;
        }

        
        .result-header {
            text-align: center;
            padding: 3rem 1rem;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('PICS/bg-main.jpg');
            background-size: cover;
            background-position: center;
            border-bottom: 3px solid var(--primary-color);
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .gp-title { font-size: 2.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .gp-meta { display: flex; justify-content: center; align-items: center; gap: 1rem; }
        .gp-date { font-size: 1.1rem; color: #ccc; font-weight: 300; }
        .gp-location { color: var(--primary-color); font-weight: 700; font-size: 1.1rem; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        .btn-back { display: inline-block; margin-bottom: 1rem; color: #aaa; text-decoration: none; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { color: #fff; transform: translateX(-5px); }

        
        .filter-section { display: flex; justify-content: flex-end; margin-bottom: 1.5rem; gap: 1rem; }
        .filter-btn {
            background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: #fff;
            padding: 0.6rem 1.2rem; border-radius: 50px; cursor: pointer; transition: all 0.3s;
            font-family: inherit; font-weight: 600; font-size: 0.9rem;
            display: inline-flex; justify-content: center; align-items: center; /* Flexbox for centering */
            text-align: center; /* Fallback */
        }
        .filter-btn:hover { background: rgba(255,255,255,0.1); border-color: #fff; }
        .filter-btn.active { background: var(--primary-color); border-color: var(--primary-color); }

        
        .result-table { width: 100%; border-collapse: separate; border-spacing: 0 0.8rem; }
        .result-table th {
            text-align: left; padding: 1rem 1.5rem; color: #888; font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 1px; font-weight: 600;
        }
        .result-row {
            background: var(--bg-card);
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .result-row:hover {
            transform: translateY(-2px);
            background: #252533;
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .result-cell { padding: 1.2rem 1.5rem; vertical-align: middle; border-top: 1px solid rgba(255,255,255,0.02); }
        .result-cell:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        .result-cell:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

        
        .pos-col { font-weight: 800; font-size: 1.2rem; min-width: 50px; text-align: center; }
        .pos-1 { color: #FFD700; } .pos-2 { color: #C0C0C0; } .pos-3 { color: #CD7F32; }
        
        .driver-col { display: flex; align-items: center; gap: 1rem; }
        .driver-avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; object-position: top; border: 2px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
        .driver-name { font-weight: 700; font-size: 1.1rem; line-height: 1.2; }
        .driver-team-mobile { display: none; font-size: 0.85rem; color: #888; font-weight: 400; margin-top: 2px; }
        
        .ecurie-desktop { font-weight: 500; color: #ccc; }
        .time-text { font-family: 'Courier New', monospace; font-weight: 600; color: #fff; background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 4px; }
        .pts-text { font-weight: 800; color: var(--primary-color); font-size: 1.1rem; text-align: right; display: block; }

        .favorite-row { border-left: 4px solid #FFD700; }

        
        .mobile-only { display: none !important; }
        .desktop-only { display: table-cell !important; }

        
        @media (max-width: 768px) {
            .mobile-only { display: block !important; }
            .desktop-only { display: none !important; }

            
            .result-header { padding: 1rem 0.5rem; margin-bottom: 0.5rem; } 
            .gp-title { font-size: 1.2rem; letter-spacing: 0; line-height: 1.1; margin: 0; }
            .gp-meta { font-size: 0.8rem; display: flex; flex-direction: row; justify-content: center; gap: 0.5rem; } 
            .btn-back { font-size: 0.75rem; margin-bottom: 0.2rem; }
            
            .container { padding: 0 0.5rem; }

            .filter-section { justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; } 
            .filter-btn { padding: 0.3rem 0.6rem; font-size: 0.7rem; flex: 0 1 auto; min-width: 100px; }

            
            .result-table, .result-table tbody { display: block; width: 100%; }
            .result-table thead { display: none; }
            
            .result-row {
                display: grid !important; 
                grid-template-columns: 35px 1fr auto; /* Pos | Driver | Stats */
                align-items: center;
                gap: 8px;
                padding: 10px;
                margin-bottom: 8px; 
                border-bottom: 1px solid rgba(255,255,255,0.05); 
                background: rgba(255,255,255,0.02);
                border-radius: 8px;
            }

            .result-cell { padding: 0; border: none; background: transparent; }

            /* Position Column */
            .pos-col { 
                grid-column: 1;
                text-align: center; 
                font-size: 0.95rem; /* Reduced specific font size for NC */
                font-weight: 800; 
                width: 100%;
            }

            /* Driver Column (Name & Image) */
            .driver-col-cell { 
                grid-column: 2;
                display: flex;
                align-items: center;
                overflow: hidden;
            }
            
            .driver-col {
                display: flex;
                align-items: center;
                gap: 10px;
                width: 100%;
            }

            .driver-avatar { 
                width: 38px; height: 38px; min-width: 38px;
                border-radius: 50%;
                border: 2px solid rgba(255,255,255,0.1); 
                object-fit: cover;
                flex-shrink: 0;
            }
            
            .driver-info-wrapper {
                display: flex; 
                flex-direction: column; 
                justify-content: center;
                overflow: hidden; 
                min-width: 0; 
                padding-left: 2px;
            }

            .driver-name {
                font-size: 0.95rem; /* Slightly smaller for mobile if names are long */
                font-weight: 700;
                /* display: flex is set inline */
                overflow: hidden;
            }

            /* Truncate the name span specifically */
            .driver-name > span:first-child {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                min-width: 0;
                display: block; /* Ensure ellipsis works */
            }

            /* Make sure the star doesn't shrink */
            .driver-name > span:last-child {
                flex-shrink: 0;
            }

            .driver-team-mobile {
                 font-size: 0.75rem;
                 color: rgba(255,255,255,0.5);
                 white-space: nowrap;
                 overflow: hidden;
                 text-overflow: ellipsis;
                 display: block;
                 max-width: 100%;
            }

            /* Stats Column (Time & Pts) */
            .stats-col-cell.mobile-only { 
                grid-column: 3;
                text-align: right; 
                display: flex !important; 
                flex-direction: column; 
                align-items: flex-end; 
                justify-content: center;
            }
            .time-text { font-size: 0.8rem; color: #ccc; margin: 0; font-family: monospace; background: transparent; padding: 0; }
            .pts-text { font-size: 0.9rem; color: var(--primary-color); font-weight: 800; margin-top: 3px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="result-header">
        <div class="container">
            <a href="calendrier" class="btn-back">← Retour calendrier</a>
            <div class="gp-title"><?= htmlspecialchars($course['nom']) ?></div>
            <div class="gp-meta">
                <span class="gp-date">📅 <?= date('d M Y', strtotime($course['date_course'])) ?></span>
                <span class="gp-location"> &nbsp;📍 <?= htmlspecialchars($course['lieu']) ?></span>
            </div>
        </div>
    </div>

    <main class="container">
        <div class="filter-section">
            <?php if ($partner): ?>
                <a href="resultat_course?id=<?= $partner['id'] ?>" class="filter-btn" style="text-decoration: none;">
                    Voir <?= stripos($partner['nom'], 'Sprint') !== false ? 'le Sprint' : 'le Grand Prix' ?>
                </a>
            <?php endif; ?>
            <button class="filter-btn active" onclick="filterResults('all')">Tous les pilotes</button>
            <button class="filter-btn" onclick="filterResults('favorites')">⭐ Mes Favoris</button>
        </div>

        <div id="no-favorites-msg" style="display: none; text-align: center; color: #ccc; padding: 3rem; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 2rem;">
            <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Veuillez ajouter des favoris afin de profiter de cette fonctionnalité !</p>
            <a href="selection-favoris" class="btn btn-primary">Gérer mes favoris</a>
        </div>

        <?php if (empty($allPilots)): ?>
            <div style="text-align: center; padding: 4rem; color: #666; font-style: italic;">
                <h3>Données indisponibles</h3>
                <p>Les résultats officiels n'ont pas encore été publiés.</p>
            </div>
        <?php else: ?>
            <table class="result-table" id="results-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">Pos</th>
                        <th>Pilote</th>
                        <th class="desktop-only">Écurie</th>
                        <th class="desktop-only">Temps / Écart</th>
                        <th class="desktop-only" style="text-align: right;">Pts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $teamMapping = [
                        'Red Bull Racing' => 'Red Bull',
                        'Mercedes-AMG Petronas' => 'Mercedes',
                        'Scuderia Ferrari' => 'Ferrari',
                        'McLaren Formula 1 Team' => 'McLaren',
                        'Aston Martin Aramco' => 'Aston Martin',
                        'Alpine F1 Team' => 'Alpine',
                        'Williams Racing' => 'Williams',
                        'Visa Cash App RB' => 'VCARB',
                        'Stake F1 Team Kick Sauber' => 'Sauber',
                        'Kick Sauber' => 'Sauber',
                        'MoneyGram Haas F1 Team' => 'Haas',
                        'Haas F1 Team' => 'Haas'
                    ];

                    foreach ($allPilots as $driver): 
                        $isFavorite = in_array($driver['pilote_id'], $userFavorites);
                        $rowClass = 'result-row';
                        if ($isFavorite) $rowClass .= ' favorite-row';
                        
                        // Shorten Team Name
                        $teamName = $driver['ecurie'] ?? '-';
                        foreach ($teamMapping as $long => $short) {
                            if (stripos($teamName, $long) !== false) {
                                $teamName = $short;
                                break;
                            }
                        }
                    ?>
                    <tr class="<?= $rowClass ?>" data-favorite="<?= $isFavorite ? 'true' : 'false' ?>">
                        <td class="result-cell pos-col pos-<?= $driver['position'] ?>">
                            <?= ($driver['position'] >= 99) ? 'NC' : $driver['position'] ?>
                        </td>

                        <td class="result-cell driver-col-cell">
                            <div class="driver-col">
                                <img src="<?= htmlspecialchars($driver['img']) ?>" alt="Driver" class="driver-avatar">
                                <div class="driver-info-wrapper">
                                    <div class="driver-name" style="display: flex; flex-direction: row; align-items: center; gap: 5px;">
                                        <span><?php 
                                            $fullName = $driver['prenom'] . ' ' . $driver['nom'];
                                            if (strip_tags($fullName) == 'Andrea Kimi Antonelli') {
                                                echo 'Kimi Antonelli';
                                            } else {
                                                echo htmlspecialchars($fullName);
                                            }
                                        ?></span>
                                        <?php if ($isFavorite): ?>
                                            <span style="color: #FFD700; font-size: 0.8rem;">★</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="driver-team-mobile"><?= htmlspecialchars($teamName) ?></div>
                                </div>
                            </div>
                        </td>

                        <td class="result-cell desktop-only ecurie-desktop">
                            <?= htmlspecialchars($teamName) ?>
                        </td>

                        <td class="result-cell desktop-only">
                            <span class="time-text"><?= htmlspecialchars($driver['temps']) ?></span>
                        </td>

                        <td class="result-cell desktop-only">
                            <span class="pts-text">+<?= $driver['points'] ?> PTS</span>
                        </td>

                        <td class="result-cell mobile-only stats-col-cell">
                            <span class="time-text"><?= htmlspecialchars($driver['temps']) ?></span>
                            <span class="pts-text">+<?= $driver['points'] ?> PTS</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <script>
        function filterResults(type) {
            const rows = document.querySelectorAll('.result-row');
            const table = document.getElementById('results-table');
            const noFavMsg = document.getElementById('no-favorites-msg');
            const hasFavorites = <?= count($userFavorites) > 0 ? 'true' : 'false' ?>;

            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            if (type === 'favorites' && !hasFavorites) {
                if (table) table.style.display = 'none';
                noFavMsg.style.display = 'block';
                return;
            }

            if (noFavMsg) noFavMsg.style.display = 'none';
            if (table) table.style.display = 'table';

            let visibleRows = 0;
            rows.forEach(row => {
                const isFav = row.getAttribute('data-favorite') === 'true';
                if (type === 'all') {
                    row.style.setProperty('display', '', 'important');
                    if (window.innerWidth <= 768) {
                        row.classList.remove('hidden-row');
                    } else {
                        row.style.display = '';
                    }
                    visibleRows++;
                } else if (type === 'favorites') {
                    if (isFav) {
                        row.style.display = '';
                        row.classList.remove('hidden-row');
                        visibleRows++;
                    } else {
                        row.style.display = 'none'; // Works for desktop where !important is not used
                        row.classList.add('hidden-row'); // For mobile
                    }
                }
            });
        }
    </script>
    <style>
        .hidden-row { display: none !important; }
    </style>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
