<?php
require_once 'config.php';

// Year selection logic
$available_years = [2025, 2026];
$year = isset($_GET['year']) && in_array((int)$_GET['year'], $available_years) ? (int)$_GET['year'] : 2025;

// 1. Classement Pilotes
$stmtPilotes = $pdo->prepare("
    SELECT p.id, p.nom, p.prenom, p.image_url, e.nom as team_name, e.couleur as team_color, SUM(r.points) as total_points 
    FROM resultats r 
    JOIN pilotes p ON r.pilote_id = p.id 
    JOIN ecuries e ON r.ecurie_id = e.id 
    JOIN courses c ON r.course_id = c.id
    WHERE c.annee = ?
    GROUP BY p.id 
    ORDER BY total_points DESC
");
$stmtPilotes->execute([$year]);
$driverStandings = $stmtPilotes->fetchAll();

// 3. Classement Constructeurs (Exclure Cadillac en 2025)
$sqlTeams = "
    SELECT e.id, e.nom, e.couleur, COALESCE(SUM(r.points), 0) as total_points 
    FROM ecuries e 
    LEFT JOIN resultats r ON r.ecurie_id = e.id AND r.course_id IN (SELECT id FROM courses WHERE annee = ?)
    WHERE 1=1
";

// Exclure Cadillac seulement en 2025 (car ils arrivent en 2026)
if ($year == 2025) {
    $sqlTeams .= " AND e.nom NOT LIKE '%Cadillac%'";
}

$sqlTeams .= " GROUP BY e.id ORDER BY total_points DESC, e.nom ASC";

$stmtTeams = $pdo->prepare($sqlTeams);
$stmtTeams->execute([$year]);
$teamStandings = $stmtTeams->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saison <?php echo $year; ?> - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="CSS/year_selector.css">
    <style>
/* Modern Sub-Navigation Styles */
.saison-nav-container {
    display: flex;
    justify-content: center;
    margin: 2rem 0;
}

.saison-nav {
    display: inline-flex;
    background: rgba(30, 30, 35, 0.8);
    border-radius: 50px;
    padding: 5px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
}

.saison-nav a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-family: 'Titillium Web', sans-serif;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    padding: 10px 25px;
    border-radius: 40px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.saison-nav a:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.saison-nav a.active {
    background: var(--primary-color);
    color: #fff;
    box-shadow: 0 2px 10px rgba(227, 6, 19, 0.4);
}


        /* Section Titles */
        .section-header {
            text-align: center;
            margin: 6rem 0 3rem;
            position: relative;
        }
        
        .section-header::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -1px;
            margin: 0;
            text-transform: uppercase;
            background: linear-gradient(to right, #fff, #aaa);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Smooth Scroll Padding for Sticky Header */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 160px;
        }

        .standings-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-card);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 3rem;
            /* Ensure centering within container if container is wider */
            margin-left: auto;
            margin-right: auto;
        }
        .standings-table th, .standings-table td {
            padding: 1rem;
            /* Center alignment for most cells helps balance appearance */
            vertical-align: middle;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .standings-table th {
            background: rgba(255,255,255,0.05);
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.8rem;
            text-align: center; /* Center headers */
        }
        /* Align headers with content visually */
        #pilotes .standings-table th:nth-child(3) {
            padding-left: 70px; /* Drivers: shift for image */
            text-align: left;
        }
        
        #ecuries .standings-table th:nth-child(3) {
             padding-left: 30px; /* Constructors: align with start of team name */
             text-align: left;
        }

        /* Mobile adjustment for header alignment */
        @media (max-width: 768px) {
            #pilotes .standings-table th:nth-child(3) {
                padding-left: 50px !important;
            }
            #ecuries .standings-table th:nth-child(3) {
                padding-left: 15px !important; /* Closer fit for teams on mobile */
            }
        }


        .table-row-hover {
            transition: all 0.2s ease;
        }
        .table-row-hover:hover {
            background-color: rgba(255, 255, 255, 0.05);
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 10;
            position: relative;
        }
        .points-col { width: 100px; text-align: center; font-weight: bold; color: var(--primary-color); }
        
        .team-indicator { 
            width: 15px !important; 
            min-width: 15px !important;
            max-width: 15px !important;
            padding: 0 !important;
            border: none !important; 
            vertical-align: middle;
            text-align: center; /* Center the div inside */
        }
        
        .team-bar {
            width: 5px; /* Fixed width for the bar itself */
            border-radius: 4px; /* Slight roundness */
            margin: 0 auto; /* Center in cell */
            height: 35px; /* Slightly shorter than row to look like a capsule */
        }
        
        .table-row-hover td {
            vertical-align: middle;
        }
        .rank-col { 
            width: 60px; 
            min-width: 60px;
            text-align: center; 
            font-weight: bold; 
            padding-left: 15px; /* Add breathing room like PTS column */
        }
        
        .standings-table td.team-indicator {
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        /* --- MOBILE OPTIMIZATION --- */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem !important;
                margin-top: 1.5rem !important;
                margin-bottom: 1.5rem !important;
            }

            /* Better Navigation on Mobile */
            .saison-nav-container {
                margin: 0 0 2rem;
                padding: 0 10px;
                position: relative; /* No longer sticky */
            }

            .saison-nav {
                display: flex; /* Force flex for nav items */
                width: 100%;
                justify-content: space-between;
                border-radius: 12px;
                padding: 4px;
                background: rgba(20, 20, 25, 0.95);
            }

            .saison-nav a {
                padding: 8px 5px;
                font-size: 0.75rem;
                flex: 1;
                justify-content: center;
                border-radius: 8px;
                text-align: center;
                white-space: nowrap;
            }

            /* Optimized Constructors Table for Mobile */
            .standings-table {
                width: 100%;
                table-layout: auto;
                font-size: 0.85rem; /* Slightly smaller base font */
                margin: 0 auto; /* Center table */
            }

            .standings-table th, 
            .standings-table td {
                padding: 10px 4px; /* Balanced padding */
            }
            
            /* Rank Column */
            .rank-col { 
                width: 40px !important; 
                font-size: 1rem !important;
                padding-left: 10px !important; /* Add breathing room from edge */
                padding-right: 2px !important; 
                text-align: center;
            }
            
            /* Team Indicator Bar */
            .team-indicator { 
                width: 6px !important; 
                padding: 0 !important;
                /* Ensure consistent fill */
                height: 1px; /* trick to make height fill row */
            }
            
            /* Name/Team Cell */
            .standings-table td:nth-child(3) {
                text-align: left;
                padding-left: 8px !important;
                white-space: normal; /* Allow text wrapping */
            }
            
            /* Smaller text for names on mobile */
            .standings-table td:nth-child(3) span,
            .standings-table td:nth-child(3) div {
                 font-size: 0.9rem !important;
            }

            /* Points Column */
            .points-col { 
                width: 50px !important; 
                text-align: right;
                padding-right: 5px !important; /* Ensure it's not stuck to edge */
                font-size: 1rem !important; 
            }

            /* Fix Specific Team Table padding if needed */
            #ecuries .standings-table td:nth-child(3) {
                padding-left: 10px !important;
            }

            /* Disable hover headers on mobile to prevent sticky focus */
            .table-row-hover:hover {
                transform: none !important;
                box-shadow: none !important;
                background-color: transparent !important; 
            }
        }

    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1 class="page-title" style="text-align: center; margin-top: 3rem; margin-bottom: 1.5rem; font-size: 3rem; color: #fff;">SAISON <?php echo $year; ?></h1>
        
        <div class="year-selector">
            <a href="?year=2025" class="year-btn <?php echo $year == 2025 ? 'active' : ''; ?>">2025</a>
            <a href="?year=2026" class="year-btn <?php echo $year == 2026 ? 'active' : ''; ?>">2026</a>
        </div>
        
        <div class="saison-nav-container">
            <nav class="saison-nav">
                <a href="#pilotes" class="active">
                    <i class="fa-solid fa-helmet-safety"></i> Pilotes
                </a>
                <a href="#ecuries">
                    <i class="fa-solid fa-people-group"></i> Écuries
                </a>
            </nav>
        </div>

        <?php if (empty($driverStandings)): ?>
        <div style="text-align: center; margin: 4rem 0; padding: 3rem; background: var(--bg-card); border-radius: 10px; border: 1px solid var(--border-color);">
            <i class="fa-solid fa-hourglass-start" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h2 style="font-size: 2rem; margin-bottom: 0.5rem; color: #fff;">La saison n'a pas encore commencée</h2>
            <p style="opacity: 0.7;">Les classements seront disponibles après la première course.</p>
        </div>
        <?php else: ?>

        <section id="pilotes">
            <div class="section-header">
                <h2 class="section-title">CLASSEMENT PILOTES</h2>
            </div>
            
            <table class="standings-table">
                <thead>
                    <tr>
                        <th class="rank-col">Pos</th>
                        <th class="team-indicator-header" style="width: 15px; padding: 0 !important;"></th>
                        <th>Pilote</th>
                        <th>Écurie</th>
                        <th class="points-col">PTS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    $teamShortNames = [
                        'Red Bull Racing' => 'Red Bull',
                        'Mercedes-AMG Petronas' => 'Mercedes',
                        'Scuderia Ferrari' => 'Ferrari',
                        'McLaren' => 'McLaren',
                        'Aston Martin' => 'Aston Martin',
                        'Alpine' => 'Alpine',
                        'Williams' => 'Williams',
                        'Visa Cash App RB' => 'VCARB',
                        'Kick Sauber / Audi' => 'Sauber',
                        'Haas' => 'Haas'
                    ];

                    foreach ($driverStandings as $d): 
                        // Clean up name by trimming just in case
                        $dbName = trim($d['team_name']);
                        $shortTeamName = $teamShortNames[$dbName] ?? $dbName;
                    ?>
                    <tr class="table-row-hover">
                        <td class="rank-col" style="font-size: 1.1rem; color: #fff; text-align: center;"><?= $rank++ ?></td>
                        <td class="team-indicator">
                            <div class="team-bar" style="background-color: <?= $d['team_color'] ?>;"></div>
                        </td>
                        <td>
                             <div style="display: flex; align-items: center; gap: 15px;">
                                <?php if($d['image_url']): ?>
                                    <img src="<?= htmlspecialchars($d['image_url']) ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; object-position: top; border: 1px solid rgba(255,255,255,0.2);">
                                <?php endif; ?>
                                <div>
                                    <span style="font-size: 0.85rem; opacity: 0.7; display: block; line-height: 1;"><?= htmlspecialchars($d['prenom']) ?></span>
                                    <span style="font-size: 1.1rem; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($d['nom']) ?></span>
                                </div>
                             </div>
                        </td>
                        <td style="color: <?= $d['team_color'] ?>; font-weight: 600; letter-spacing: 0.5px;"><?= htmlspecialchars($shortTeamName) ?></td>
                        <td class="points-col" style="font-size: 1.2rem; text-align: center;"><?= $d['total_points'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- SEPARATOR -->
        <div style="height: 100px;"></div>

        <section id="ecuries" style="text-align: center;">
            <div class="section-header">
                <h2 class="section-title">CLASSEMENT CONSTRUCTEURS</h2>
            </div>
            
            <table class="standings-table">
                <thead>
                    <tr>
                        <th class="rank-col">Pos</th>
                        <th class="team-indicator-header" style="width: 15px; padding: 0 !important;"></th>
                        <th>Écurie</th>
                        <th class="points-col">PTS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($teamStandings as $t): ?>
                    <tr class="table-row-hover">
                        <td class="rank-col" style="font-size: 1.2rem; color: #fff; text-align: center;"><?= $rank++ ?></td>
                        <td class="team-indicator">
                            <div class="team-bar" style="background-color: <?= $t['couleur'] ?>;"></div>
                        </td>
                        <td style="text-align: left; padding-left: 30px;">
                            <span style="font-size: 1.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #fff;"><?= htmlspecialchars($t['nom']) ?></span>
                        </td>
                        <td class="points-col" style="font-size: 1.3rem; text-align: center;"><?= $t['total_points'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <script>
            // Simple active state toggle
            const navLinks = document.querySelectorAll('.saison-nav a');
            
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    navLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                });
            });
        </script>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
