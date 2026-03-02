<?php 
require_once 'config.php';

$year = isset($_GET['year']) && in_array($_GET['year'], [2025, 2026]) ? (int)$_GET['year'] : 2025;

$stmt = $pdo->prepare("SELECT p.id, p.nom, p.prenom, e.nom as team_name, e.couleur as team_color, SUM(r.points) as total_points 
                     FROM resultats r 
                     JOIN pilotes p ON r.pilote_id = p.id 
                     JOIN ecuries e ON p.ecurie_id = e.id 
                     JOIN courses c ON r.course_id = c.id
                     WHERE c.annee = ?
                     GROUP BY p.id 
                     ORDER BY total_points DESC");
$stmt->execute([$year]);
$allDriversData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$top5Drivers = array_slice($allDriversData, 0, 5);
$driverLabels = [];
$driverPoints = [];
$driverColors = [];

foreach ($top5Drivers as $d) {
    // Shorten name to "P. Nom" to avoid truncation
    $initial = mb_substr($d['prenom'], 0, 1) . '.';
    $driverLabels[] = $initial . ' ' . $d['nom'];
    $driverPoints[] = $d['total_points'];
    $driverColors[] = $d['team_color'] ?: '#ccc';
}

$stmt = $pdo->prepare("SELECT e.id, e.nom, e.couleur, COALESCE(SUM(r.points), 0) as total_points 
                     FROM ecuries e 
                     LEFT JOIN pilotes p ON e.id = p.ecurie_id
                     LEFT JOIN resultats r ON p.id = r.pilote_id AND r.course_id IN (SELECT id FROM courses WHERE annee = ?)
                     WHERE e.nom NOT REGEXP '^[0-9]+$' AND LENGTH(e.nom) > 2 " . ($year == 2025 ? "AND e.nom NOT LIKE '%Cadillac%'" : " AND 1=1 ") . "
                     GROUP BY e.id 
                     ORDER BY total_points DESC");
$stmt->execute([$year]);
$allConstructorsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$top5Constructors = array_slice($allConstructorsData, 0, 5);
$teamLabels = [];
$teamPoints = [];
$teamColors = [];

// Helper to shorten team names like in actualites.php
function getShortTeamNameStat($fullName) {
    if (stripos($fullName, 'Red Bull') !== false) return 'Red Bull';
    if (stripos($fullName, 'Mercedes') !== false) return 'Mercedes';
    if (stripos($fullName, 'Ferrari') !== false) return 'Ferrari';
    if (stripos($fullName, 'McLaren') !== false) return 'McLaren';
    if (stripos($fullName, 'Aston Martin') !== false) return 'Aston Martin';
    if (stripos($fullName, 'Alpine') !== false) return 'Alpine';
    if (stripos($fullName, 'Williams') !== false) return 'Williams';
    if (stripos($fullName, 'Haas') !== false) return 'Haas';
    if (stripos($fullName, 'Sauber') !== false) return 'Sauber';
    if (stripos($fullName, 'Racing Bulls') !== false || stripos($fullName, 'RB') !== false) return 'Racing Bulls';
    
    // Default fallback
    $short = str_ireplace(['F1 Team', 'Formula 1 Team', 'Racing'], '', $fullName);
    return trim($short);
}

foreach ($top5Constructors as $c) {
    $teamLabels[] = getShortTeamNameStat($c['nom']);
    $teamPoints[] = $c['total_points'];
    $teamColors[] = $c['couleur'] ?: '#ccc';
}

$stmt = $pdo->prepare("SELECT id, nom AS nom_grand_prix FROM courses WHERE annee = ? ORDER BY date_course ASC");
$stmt->execute([$year]);
$races = $stmt->fetchAll(PDO::FETCH_ASSOC);
$raceLabels = array_column($races, 'nom_grand_prix');
$raceIds = array_column($races, 'id');

$resultsMatrix = [];
if (!empty($raceIds)) {
    $in = str_repeat('?,', count($raceIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT pilote_id, course_id, points FROM resultats WHERE course_id IN ($in)");
    $stmt->execute($raceIds);
    $resultsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultsRaw as $res) {
        $resultsMatrix[$res['pilote_id']][$res['course_id']] = $res['points'];
    }
}

$evolutionDatasets = [];

foreach ($allDriversData as $driver) {
    $pointsData = [];
    $currentTotal = 0;
    
    foreach ($raceIds as $rId) {
        $points = isset($resultsMatrix[$driver['id']][$rId]) ? $resultsMatrix[$driver['id']][$rId] : 0;
        $currentTotal += $points;
        $pointsData[] = $currentTotal;
    }

    $evolutionDatasets[] = [
        'label' => $driver['prenom'] . ' ' . $driver['nom'],
        'data' => $pointsData,
        'borderColor' => $driver['team_color'] ?: '#FFFFFF',
        'backgroundColor' => $driver['team_color'] ?: '#FFFFFF',
        'tension' => 0.1,
        'fill' => false,
        'driverId' => $driver['id'], 
        'hidden' => true 
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Interactives - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 2rem; 
            margin-top: 2rem;
            margin-bottom: 4rem; /* Added spacing */
            width: 100%;
        }
        .stat-card {
            background: var(--bg-card); 
            border: 1px solid var(--border-color); 
            border-radius: 15px; 
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            position: relative;
            width: 100%; /* Ensure card doesn't exceed container */
            min-width: 0; /* Important for grid items to shrink */
            overflow: hidden; /* Prevent overflow */
            box-sizing: border-box;
        }
        .stat-card h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.25rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 400px; /* Default height for big chart */
        }
        .chart-wrapper-small {
            position: relative;
            width: 100%;
            height: 300px; /* Fixed height for small charts to prevent infinity growth */
        }
        canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .mobile-hint {
                display: block !important;
                text-align: center;
                margin-bottom: 10px;
            }
            .chart-wrapper {
                height: 40vh; /* Reduced height for better fit */
                min-height: 300px;
            }
            .stats-grid {
                display: flex !important;
                flex-direction: column;
                gap: 1.5rem;
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                margin-bottom: 5rem !important; /* Bottom spacing for mobile */
            }
            .container {
                padding: 0 15px !important; /* Safe padding */
                width: 100% !important;
                overflow-x: hidden;
            }
            .stat-card {
                padding: 1rem !important;
                width: 100% !important;
                box-sizing: border-box !important;
                margin: 0 !important;
            }
            .page-title {
                font-size: 1.6rem !important;
                margin-bottom: 0.5rem;
            }
            .modal-driver-list {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); /* Smaller items for mobile */
                gap: 8px;
                padding: 10px;
            }
            .driver-toggle {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
            /* Modal should take more space on mobile */
            .modal-box {
                width: 95%;
                max-height: 90vh; /* Allow it to be taller */
            }
        }
        
        .modal-driver-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            max-height: 450px;
            overflow-y: auto;
            padding: 15px;
        }
        .driver-toggle {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            border-radius: 30px;
            background: rgba(255,255,255,0.03);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            font-weight: 600;
            color: #aaa;
            user-select: none;
            position: relative;
        }
        .driver-toggle:hover {
            background: rgba(255,255,255,0.08);
            transform: translateY(-2px);
            color: #fff;
        }
        .driver-toggle.active {
            background: var(--chip-color);
            border-color: var(--chip-color);
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .driver-toggle input {
            display: none;
        }
        .color-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 12px;
            background-color: var(--chip-color);
            transition: all 0.3s ease;
            box-shadow: 0 0 5px var(--chip-color);
        }
        .driver-toggle.active .color-dot {
            background-color: #fff;
            box-shadow: 0 0 8px #fff;
        }
        .compare-btn-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

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
        
        /* Table Styles */
        .hidden-table {
            display: none;
            margin-top: 1rem;
            width: 100%;
            overflow-x: auto;
        }
        .hidden-table.active {
            display: block;
            animation: fadeIn 0.3s;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .stats-table th {
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #444;
            color: #888;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .stats-table td {
            padding: 8px;
            border-bottom: 1px solid #333;
        }
        .stats-table tr:last-child td {
            border-bottom: none;
        }
        .stats-pos {
            font-weight: bold;
            color: var(--primary-color);
            width: 30px;
        }
        .stats-pts {
            font-weight: bold;
            text-align: right;
        }
        .btn-toggle-stats {
            background: none;
            border: 1px solid #444;
            color: #aaa;
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.85rem;
        }
        .btn-toggle-stats:hover {
            background: #333;
            color: white;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h1 class="page-title" style="text-align: center; margin-bottom: 1rem; font-size: 2.5rem; text-transform: uppercase;">
            <span style="color: var(--primary-color);">📊 Statistiques</span> Saison <?= $year ?>
        </h1>

        <div class="year-selector">
            <a href="?year=2025" class="year-btn <?= $year === 2025 ? 'active' : '' ?>">2025</a>
            <a href="?year=2026" class="year-btn <?= $year === 2026 ? 'active' : '' ?>">2026</a>
        </div>
        
        <?php if (empty($allDriversData)): ?>
            <div style="text-align: center; margin: 4rem 0; padding: 3rem; background: var(--bg-card); border-radius: 10px; border: 1px solid var(--border-color);">
                <i class="fa-solid fa-hourglass-start" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <h2 style="font-size: 2rem; margin-bottom: 0.5rem; color: #fff;">La saison n'a pas encore commencée</h2>
                <p style="opacity: 0.7;">Les classements seront disponibles après la première course.</p>
            </div>
            </main>
        <?php else: ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>🏆 Classement Pilotes (Top 5)</h3>
                <div class="chart-wrapper-small">
                    <canvas id="driversChart"></canvas>
                </div>
                
                <button class="btn-toggle-stats" onclick="toggleStats('drivers-full')">
                    Voir le classement complet ▼
                </button>
                <div id="drivers-full" class="hidden-table">
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>Pilote</th> <!-- No "Pos" since we use # -->
                                <th style="text-align:right;">Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($allDriversData as $i => $d): ?>
                                <tr>
                                    <td class="stats-pos"><?= $i+1 ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <span style="display:inline-block; width:4px; height:15px; margin-right:8px; background:<?= $d['team_color'] ?: '#fff' ?>; border-radius:2px;"></span>
                                            <?= htmlspecialchars($d['prenom'].' '.$d['nom']) ?>
                                        </div>
                                    </td>
                                    <td class="stats-pts"><?= $d['total_points'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="stat-card">
                <h3>🏎️ Classement Constructeurs (Top 5)</h3>
                <div class="chart-wrapper-small">
                    <canvas id="constructorsChart"></canvas>
                </div>

                <button class="btn-toggle-stats" onclick="toggleStats('constructors-full')">
                    Voir le classement complet ▼
                </button>
                <div id="constructors-full" class="hidden-table">
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>Écurie</th>
                                <th style="text-align:right;">Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($allConstructorsData as $i => $c): ?>
                                <tr>
                                    <td class="stats-pos"><?= $i+1 ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <span style="display:inline-block; width:4px; height:15px; margin-right:8px; background:<?= $c['couleur'] ?: '#fff' ?>; border-radius:2px;"></span>
                                            <?= htmlspecialchars(getShortTeamNameStat($c['nom'])) ?>
                                        </div>
                                    </td>
                                    <td class="stats-pts"><?= $c['total_points'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="stat-card full-width">
                <h3>📈 Comparateur d'Évolution (Cumulatif)</h3>
                
                <div class="compare-btn-container">
                    <button id="openComparisonModal" class="btn btn-primary">
                        <span style="margin-right:8px">👥</span> Comparer les pilotes
                    </button>
                    <!-- Small instruction text for mobile users since legend is hidden -->
                    <p class="mobile-hint" style="font-size: 0.8rem; color: #888; margin-top: 5px; display: none;">
                        Touchez les points pour voir les détails
                    </p>
                </div>

                <div class="chart-wrapper">
                    <canvas id="pointsEvolutionChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <div id="comparisonModal" class="modal-overlay">
        <!-- Replaced content via full file context, keeping minimal for snippet -->
        <div class="modal-box" style="max-width: 800px; width: 90%;">
            <div class="modal-header">
                <h3>Sélectionner les pilotes à comparer</h3>
                <button type="button" class="close-modal" id="closeComparisonModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalDriverList" class="modal-driver-list">
                    
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary" id="validateComparison">Valider</button>
            </div>
        </div>
    </div>

    <script>
        function toggleStats(id) {
            const el = document.getElementById(id);
            const hidden = el.style.display === 'none' || !el.style.display;
            
            if (hidden) {
                el.style.display = 'block';
                el.classList.add('active');
            } else {
                el.style.display = 'none';
                el.classList.remove('active');
            }
        }
        
        Chart.defaults.color = '#e0e0e0';
        Chart.defaults.font.family = "'Segoe UI', sans-serif";
        
        const driverLabels = <?= json_encode($driverLabels) ?>;
        const driverPoints = <?= json_encode($driverPoints) ?>;
        const driverColors = <?= json_encode($driverColors) ?>;

        const teamLabels = <?= json_encode($teamLabels) ?>;
        const teamPoints = <?= json_encode($teamPoints) ?>;
        const teamColors = <?= json_encode($teamColors) ?>;

        const raceLabels = <?= json_encode($raceLabels) ?>;
        const evolutionDatasets = <?= json_encode($evolutionDatasets) ?>;

        const driversCtx = document.getElementById('driversChart').getContext('2d');
        new Chart(driversCtx, {
            type: 'bar',
            data: {
                labels: driverLabels,
                datasets: [{
                    label: 'Points',
                    data: driverPoints,
                    backgroundColor: driverColors,
                    borderRadius: 5,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#e0e0e0' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#e0e0e0',
                            font: { size: 11 },
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 0,
                            display: false // Always hide x-axis labels to be safe based on user feedback
                        }
                    }
                }
            }
        });

        const constructorsCtx = document.getElementById('constructorsChart').getContext('2d');
        new Chart(constructorsCtx, {
            type: 'bar',
            data: {
                labels: teamLabels,
                datasets: [{
                    label: 'Points',
                    data: teamPoints,
                    backgroundColor: teamColors,
                    borderRadius: 5,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#e0e0e0' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#e0e0e0',
                            font: { size: 11 },
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 0,
                            display: window.innerWidth > 768 /* Hide on mobile like drivers chart */
                        }
                    }
                }
            }
        });

        const evolutionCtx = document.getElementById('pointsEvolutionChart').getContext('2d');
        
        evolutionDatasets.forEach((ds, index) => {
            if (index < 5) ds.hidden = false;
        });

        const evolutionChart = new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: raceLabels,
                datasets: evolutionDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 0,
                        right: 10,
                        top: 0,
                        bottom: 0
                    }
                },
                plugins: {
                    legend: { 
                        display: false, // Force hide legend as per user feedback
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        mode: 'nearest',
                        intersect: true, /* Require touching the line/point */
                        position: 'nearest',
                        bodyFont: { size: 12 },
                        titleFont: { size: 13 },
                        padding: 10,
                        caretPadding: 5
                    }
                },
                elements: {
                    point: {
                        radius: 2, /* Smaller default dots to avoid overlapping */
                        hitRadius: 20, /* Keep large hit area for touch */
                        hoverRadius: 6 /* Smaller hover effect */
                    },
                    line: {
                        borderWidth: 2 /* Thinner lines */
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        title: { display: window.innerWidth > 768, text: 'Points Cumulés' },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { display: false } /* Hide race names completely */
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'xy', /* Scan both axis */
                    intersect: true /* This is KEY: requires direct touch */
                }
            }
        });

        const modal = document.getElementById('comparisonModal');
        const openBtn = document.getElementById('openComparisonModal');
        const closeBtn = document.getElementById('closeComparisonModal');
        const validateBtn = document.getElementById('validateComparison');
        const listContainer = document.getElementById('modalDriverList');

        evolutionDatasets.forEach((dataset, index) => {
            const label = document.createElement('label');
            label.className = 'driver-toggle' + (!dataset.hidden ? ' active' : '');
            label.style.setProperty('--chip-color', dataset.borderColor);
            if (!dataset.hidden) {
                label.style.borderColor = dataset.borderColor;
            }

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = !dataset.hidden;
            
            label.addEventListener('click', (e) => {
                e.preventDefault();
                const isNowChecked = !checkbox.checked;
                checkbox.checked = isNowChecked;
                
                evolutionChart.data.datasets[index].hidden = !isNowChecked;
                evolutionChart.update();
                
                label.classList.toggle('active', isNowChecked);
                if (isNowChecked) {
                    label.style.borderColor = dataset.borderColor;
                } else {
                    label.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                }
            });

            const colorDot = document.createElement('span');
            colorDot.className = 'color-dot';

            label.appendChild(checkbox);
            label.appendChild(colorDot);
            label.appendChild(document.createTextNode(dataset.label));
            
            listContainer.appendChild(label);
        });

        openBtn.addEventListener('click', () => {
            modal.classList.add('active');
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        validateBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    </script>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
