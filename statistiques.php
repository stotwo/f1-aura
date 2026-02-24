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
    $driverLabels[] = $d['prenom'] . ' ' . $d['nom'];
    $driverPoints[] = $d['total_points'];
    $driverColors[] = $d['team_color'] ?: '#ccc';
}

$stmt = $pdo->prepare("SELECT e.id, e.nom, e.couleur, SUM(r.points) as total_points 
                     FROM resultats r 
                     JOIN pilotes p ON r.pilote_id = p.id 
                     JOIN ecuries e ON p.ecurie_id = e.id 
                     JOIN courses c ON r.course_id = c.id
                     WHERE c.annee = ?
                     GROUP BY e.id 
                     ORDER BY total_points DESC");
$stmt->execute([$year]);
$allConstructorsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$top5Constructors = array_slice($allConstructorsData, 0, 5);
$teamLabels = [];
$teamPoints = [];
$teamColors = [];

foreach ($top5Constructors as $c) {
    $teamLabels[] = $c['nom'];
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
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); 
            gap: 2rem; 
            margin-top: 2rem;
        }
        .stat-card {
            background: var(--bg-card); 
            border: 1px solid var(--border-color); 
            border-radius: 15px; 
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            position: relative;
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
        canvas {
            max-height: 400px;
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
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>🏆 Classement Pilotes (Top 5)</h3>
                <canvas id="driversChart"></canvas>
            </div>

            <div class="stat-card">
                <h3>🏎️ Classement Constructeurs (Top 5)</h3>
                <canvas id="constructorsChart"></canvas>
            </div>

            <div class="stat-card full-width">
                <h3>📈 Comparateur d'Évolution (Cumulatif)</h3>
                
                <div class="compare-btn-container">
                    <button id="openComparisonModal" class="btn btn-primary">
                        <span style="margin-right:8px">👥</span> Comparer les pilotes
                    </button>
                </div>

                <canvas id="pointsEvolutionChart"></canvas>
            </div>
        </div>
    </main>

    <div id="comparisonModal" class="modal-overlay">
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
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        const constructorsCtx = document.getElementById('constructorsChart').getContext('2d');
        new Chart(constructorsCtx, {
            type: 'doughnut',
            data: {
                labels: teamLabels,
                datasets: [{
                    data: teamPoints,
                    backgroundColor: teamColors,
                    borderWidth: 2,
                    borderColor: '#1a1a24'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right' }
                },
                cutout: '60%'
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
                plugins: {
                    legend: { 
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        title: { display: true, text: 'Points Cumulés' }
                    },
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
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

    <?php include 'includes/footer.php'; ?>
</body>
</html>
