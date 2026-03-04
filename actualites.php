<?php
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;

$filter = $_GET['filter'] ?? 'all';
// Multi-select logic
$team_ids = isset($_GET['teams']) && $_GET['teams'] !== '' ? explode(',', $_GET['teams']) : [];
$driver_ids = isset($_GET['drivers']) && $_GET['drivers'] !== '' ? explode(',', $_GET['drivers']) : [];

// Sanitize IDs
$team_ids = array_filter($team_ids, 'is_numeric');
$driver_ids = array_filter($driver_ids, 'is_numeric');

$sql = "SELECT DISTINCT a.* FROM articles a";
$params = [];

if ($filter === 'favorites') {
    if ($user_id) {
        $sql .= "
            LEFT JOIN article_tags_pilotes atp ON a.id = atp.article_id
            LEFT JOIN users_pilotes_favoris upf ON atp.pilote_id = upf.pilote_id AND upf.user_id = ?
            LEFT JOIN article_tags_ecuries ate ON a.id = ate.article_id
            LEFT JOIN users_ecuries_favorites uef ON ate.ecurie_id = uef.ecurie_id AND uef.user_id = ?
            WHERE (upf.user_id IS NOT NULL OR uef.user_id IS NOT NULL)
        ";
        $params[] = $user_id;
        $params[] = $user_id;
    } else {
        $sql .= " WHERE 1=0";
    }

} elseif (!empty($team_ids) || !empty($driver_ids)) {
    $sql .= " LEFT JOIN article_tags_ecuries ate ON a.id = ate.article_id";
    $sql .= " LEFT JOIN article_tags_pilotes atp ON a.id = atp.article_id";
    
    $conditions = [];
    if (!empty($team_ids)) {
        $placeholders = implode(',', array_fill(0, count($team_ids), '?'));
        $conditions[] = "ate.ecurie_id IN ($placeholders)";
        $params = array_merge($params, $team_ids);
    }
    if (!empty($driver_ids)) {
        $placeholders = implode(',', array_fill(0, count($driver_ids), '?'));
        $conditions[] = "atp.pilote_id IN ($placeholders)";
        $params = array_merge($params, $driver_ids);
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' OR ', $conditions);
    }
}

$sql .= " ORDER BY a.date_publication DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$teams = $pdo->query("SELECT id, nom FROM ecuries WHERE nom NOT REGEXP '^[0-9]+$' ORDER BY nom")->fetchAll();
$drivers = $pdo->query("SELECT id, nom, prenom FROM pilotes WHERE prenom != '' AND prenom IS NOT NULL ORDER BY nom")->fetchAll();

function getTeamImage($teamName) {
    if (!$teamName) return 'PICS/logo.png';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '_', $teamName), '_'));
    
    // Try explicit mappings first if file names are tricky
    if (strpos($slug, 'mercedes') !== false) return 'PICS/teams/mercedesamg_petronas.webp';
    if (strpos($slug, 'red_bull') !== false) return 'PICS/teams/red_bull_racing.webp';
    if (strpos($slug, 'alp') !== false) return 'PICS/teams/bwt_alpine_f1_team.webp';
    if (strpos($slug, 'aston') !== false) return 'PICS/teams/aston_martin_aramco.webp';
    if (strpos($slug, 'ferrari') !== false) return 'PICS/teams/scuderia_ferrari_hp.webp';
    if (strpos($slug, 'mclaren') !== false) return 'PICS/teams/mclaren_formula_1_team.webp';
    if (strpos($slug, 'haas') !== false) return 'PICS/teams/moneygram_haas_f1_team.webp';
    if (strpos($slug, 'kick') !== false) return 'PICS/teams/kick_sauber_audi.webp';
    if (strpos($slug, 'sauber') !== false) return 'PICS/teams/kick_sauber_audi.webp';
    if (strpos($slug, 'williams') !== false) return 'PICS/teams/williams_racing.webp';
    if (strpos($slug, 'visa') !== false) return 'PICS/teams/visa_cash_app_rb.webp';
    if (strpos($slug, 'rb') !== false && strlen($slug) < 4) return 'PICS/teams/visa_cash_app_rb.webp';
    if (strpos($slug, 'audi') !== false) return 'PICS/teams/audi_f1_team.webp';
    if (strpos($slug, 'cadillac') !== false) return 'PICS/teams/cadillac_f1_team.webp';

    // Fallback try simple slug
    if (file_exists("PICS/teams/{$slug}.webp")) return "PICS/teams/{$slug}.webp";
    if (file_exists("PICS/teams/{$slug}.png")) return "PICS/teams/{$slug}.png";
    return 'PICS/logo.png';
}

function getShortTeamName($fullName) {
    if (stripos($fullName, 'alpine') !== false) return 'Alpine';
    if (stripos($fullName, 'aston martin') !== false) return 'Aston Martin';
    if (stripos($fullName, 'mercedes') !== false) return 'Mercedes';
    if (stripos($fullName, 'red bull') !== false) return 'Red Bull';
    if (stripos($fullName, 'ferrari') !== false) return 'Ferrari';
    if (stripos($fullName, 'mclaren') !== false) return 'McLaren';
    if (stripos($fullName, 'haas') !== false) return 'Haas';
    if (stripos($fullName, 'sauber') !== false) return 'Sauber'; // covers Kick Sauber
    if (stripos($fullName, 'kick') !== false) return 'Sauber'; // alternate
    if (stripos($fullName, 'williams') !== false) return 'Williams';
    if (stripos($fullName, 'visa') !== false || stripos($fullName, 'rb') !== false) return 'Racing Bulls'; // or RB
    if (stripos($fullName, 'audi') !== false) return 'Audi';
    if (stripos($fullName, 'cadillac') !== false) return 'Cadillac';
    
    // Default fallback: remove common suffixes like "F1 Team", "Formula 1 Team"
    $short = str_ireplace(['F1 Team', 'Formula 1 Team', 'Racing'], '', $fullName);
    return trim($short);
}

function getDriverImage($nom, $prenom = '') {
    if (!$nom) return 'PICS/logo.png';
    
    // Handle accents first, BEFORE stripping non-alphanumeric
    $nomLower = mb_strtolower($nom, 'UTF-8');
    $nomLower = str_replace(['é', 'è', 'ë', 'ê'], 'e', $nomLower);
    $nomLower = str_replace(['á', 'à', 'ä', 'â'], 'a', $nomLower);
    $nomLower = str_replace(['í', 'ì', 'ï', 'î'], 'i', $nomLower);
    $nomLower = str_replace(['ó', 'ò', 'ö', 'ô'], 'o', $nomLower);
    $nomLower = str_replace(['ú', 'ù', 'ü', 'û'], 'u', $nomLower);
    $nomLower = str_replace(['ñ'], 'n', $nomLower);

    // Create variations of names to try
    $slugs = [];
    
    // 1. Lastname only cleaned (standard)
    $cleanNom = preg_replace('/[^a-z0-9]/', '', $nomLower);
    $slugs[] = $cleanNom; // e.g., "perez", "hulkenberg"

    // 2. Fullname logic (for files like maxverstappen or max_verstappen)
    if ($prenom) {
        $prenomLower = mb_strtolower($prenom, 'UTF-8');
        $cleanPrenom = preg_replace('/[^a-z0-9]/', '', $prenomLower);
        $slugs[] = $cleanPrenom . $cleanNom; 
        $slugs[] = $cleanPrenom . '_' . $cleanNom;
    }

    // Check files for generated slugs
    foreach ($slugs as $slug) {
        if (file_exists("PICS/drivers/{$slug}.webp")) return "PICS/drivers/{$slug}.webp";
        if (file_exists("PICS/drivers/{$slug}.png")) return "PICS/drivers/{$slug}.png";
        if (file_exists("PICS/drivers/{$slug}.jpg")) return "PICS/drivers/{$slug}.jpg";
        
        // Try to match partials if exact fail
        $glob = glob("PICS/drivers/{$slug}.*");
        if (!empty($glob)) return $glob[0];
    }
    
    // Specific hardcoded fixes if generic logic fails significantly
    if (strpos($cleanNom, 'perez') !== false) return 'PICS/drivers/perez.png';
    
    // If really nothing, return default silhouette
    return 'PICS/drivers/default_rookie.png'; 
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualités F1 - Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <style>
        .news-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .news-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            background: #1e1e24;
            padding: 1rem;
            border-radius: 12px;
            align-items: center;
        }
        .filter-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-family: inherit;
            font-size: 1rem;
            line-height: 1.5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        .filter-select {
            background: #2a2a30;
            border: 1px solid var(--border-color);
            color: white;
            padding: 0.5rem;
            border-radius: 8px;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        .news-card {
            background: #1e1e24;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
        }
        .news-card:hover {
            transform: translateY(-5px);
        }
        .news-image {
            height: 200px;
            background: #333;
            overflow: hidden;
        }
        .news-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .news-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .news-source {
            font-size: 0.8rem;
            color: var(--primary-color);
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .news-title {
            font-size: 1.2rem;
            margin: 0 0 1rem 0; /* Reset margins */
            line-height: 1.4;
            color: white;
            text-decoration: none;
        }
        .news-desc {
            font-size: 0.9rem;
            color: #ccc;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }
        .news-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: auto;
        }
        .tag {
            font-size: 0.75rem;
            background: #333;
            padding: 2px 8px;
            border-radius: 4px;
            color: #aaa;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .news-grid {
                grid-template-columns: 1fr;
            }
            .news-image {
                height: 250px; /* Slightly taller on mobile for impact */
            }
            .news-content {
                padding: 1rem;
            }
            .news-title {
                font-size: 1.1rem; /* Slightly smaller for mobile readability */
            }
        }

        /* Make whole card clickable and look good */
        .news-card {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .news-card:hover .news-title {
            color: var(--primary-color);
        }

        /* Filter Modal Styles */
        .filter-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10001;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        .filter-modal.active {
            display: flex;
        }
        .filter-content {
            background: #1e1e24;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            display: flex; /* Use flexbox to manage height */
            flex-direction: column;
            position: relative;
            border: 1px solid var(--border-color);
        }
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 10;
        }
        .filter-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            flex-shrink: 0; /* Prevent shrinking */
        }
        .filter-tab {
            background: transparent;
            border: none;
            color: #aaa;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            position: relative;
        }
        .filter-tab.active {
            color: white;
            font-weight: bold;
        }
        .filter-tab.active::after {
            content: '';
            position: absolute;
            bottom: -17px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-color);
        }
        .filter-group {
            display: none;
            grid-template-columns: repeat(2, 1fr); 
            gap: 15px;
            overflow-y: auto; 
            padding: 10px;
            flex-grow: 1; 
            min-height: 0; 
        }
        .filter-group.active {
            display: grid;
        }
        @media (min-width: 600px) {
            .filter-group {
                grid-template-columns: repeat(4, 1fr); /* 4 columns on desktop */
            }
        }
        
        /* Revert to Card Style but cleaner */
        .filter-item-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #2a2a30;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.2s;
            height: 100%;
        }
        .filter-item-card:hover {
            background: #333;
            border-color: #555;
        }
        .filter-item-card.selected {
            border-color: var(--primary-color);
            background: rgba(227, 6, 19, 0.1);
        }
        
        /* Image handling */
        .filter-item-card img {
            width: 100%;
            height: 100px; /* Fixed height for image */
            object-fit: cover; /* Zoom to fill */
            object-position: top center; /* Focus on face */
            margin-bottom: 10px;
            border-radius: 6px;
        }

        /* Specific background only for drivers */
        div[data-type="driver"] img {
            background-color: #383842;
        }
        
        /* Specific override for teams to keep logos contained */
        div[data-type="team"] img {
            object-fit: contain;
            object-position: center;
            padding: 5px;
            background-color: transparent;
        }

        /* Text below image */
        .filter-item-name {
            color: #fff;
            font-size: 0.9rem;
            text-align: center;
            font-weight: 500;
            line-height: 1.2;
            width: 100%;
        }
        
        /* Style scrollbar for Webkit */
        .filter-group::-webkit-scrollbar {
            width: 6px;
        }
        .filter-group::-webkit-scrollbar-track {
            background: #1e1e24; 
        }
        .filter-group::-webkit-scrollbar-thumb {
            background: #444; 
            border-radius: 3px;
        }
        .filter-group::-webkit-scrollbar-thumb:hover {
            background: #666; 
        }
        .filter-item:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="news-container">
        <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h1>Dernières Actualités</h1>
        </div>

        <div class="news-filters">
            <a href="?filter=all" class="filter-btn <?= $filter === 'all' || $filter === '' ? 'active' : '' ?>">Toutes</a>
            <a href="?filter=favorites" class="filter-btn <?= $filter === 'favorites' ? 'active' : '' ?>">Mes Favoris</a>
            <button class="filter-btn <?= ($filter !== 'all' && $filter !== 'favorites') ? 'active' : '' ?>" id="open-filter-btn" style="margin-left: auto;">Filtrer</button>
        </div>

        <div class="news-grid">
            <?php 
            $message = "Aucune actualité trouvée pour ce filtre.";
            if ($filter === 'favorites' && empty($articles)) {
                if (!$user_id) {
                    $message = "Veuillez vous connecter pour voir vos favoris !";
                } else {
                    $nbFavPilotes = $pdo->prepare("SELECT COUNT(*) FROM users_pilotes_favoris WHERE user_id = ?");
                    $nbFavPilotes->execute([$user_id]);
                    $countP = $nbFavPilotes->fetchColumn();

                    $nbFavEcuries = $pdo->prepare("SELECT COUNT(*) FROM users_ecuries_favorites WHERE user_id = ?");
                    $nbFavEcuries->execute([$user_id]);
                    $countE = $nbFavEcuries->fetchColumn();

                    if (($countP + $countE) == 0) {
                        $message = "Veuillez ajouter des favoris afin de profiter de cette fonctionnalité !";
                    }
                }
            }
            ?>

            <?php if (empty($articles)): ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #ccc; margin-top: 2rem;">
                    <p><?= htmlspecialchars($message) ?></p>
                    <?php if ($message === "Veuillez vous connecter pour voir vos favoris !"): ?>
                        <br>
                        <a href="connexion.php" class="btn btn-primary">Se connecter</a>
                    <?php elseif ($message !== "Aucune actualité trouvée pour ce filtre."): ?>
                        <br>
                        <a href="selection-favoris.php" class="btn btn-primary">Gérer mes favoris</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <a href="<?= htmlspecialchars($article['lien']) ?>" target="_blank" class="news-card">
                        <?php if ($article['image_url']): ?>
                            <div class="news-image">
                                <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="">
                            </div>
                        <?php endif; ?>
                        <div class="news-content">
                            <span class="news-source">
                                <?= htmlspecialchars($article['source']) ?> • <?= date('d/m H:i', strtotime($article['date_publication'])) ?>
                            </span>
                            <h3 class="news-title">
                                <?= htmlspecialchars($article['titre']) ?>
                            </h3>
                            <div class="news-desc">
                                <?= htmlspecialchars(mb_strimwidth($article['description'], 0, 100, '...')) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Filter Modal -->
    <div id="filterModal" class="filter-modal">
        <div class="filter-content">
            <span class="close-modal" onclick="closeNewsFilter()">&times;</span>
            <h2 class="modal-title">Filtrer les actualités</h2>
            
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="switchFilterTab('teams')">Par Écurie</button>
                <button class="filter-tab" onclick="switchFilterTab('drivers')">Par Pilote</button>
            </div>

            <div id="teams-list" class="filter-group active">
                <?php foreach ($teams as $t): ?>
                    <div class="filter-item-card <?= in_array($t['id'], $team_ids) ? 'selected' : '' ?>" 
                         data-id="<?= $t['id'] ?>" 
                         data-type="team"
                         onclick="toggleSelection(this)">
                        <img src="<?= getTeamImage($t['nom']) ?>" alt="<?= htmlspecialchars($t['nom']) ?>">
                        <div class="filter-item-name"><?= htmlspecialchars(getShortTeamName($t['nom'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="drivers-list" class="filter-group">
                <?php foreach ($drivers as $d): ?>
                    <?php 
                        $nomClean = $d['nom'];
                        if (preg_match('/^(.*)([A-Z]{3})$/u', $nomClean, $matches)) {
                            $nomClean = trim($matches[1]);
                        }
                        $fullName = trim($d['prenom'] . ' ' . $nomClean);
                        $img = getDriverImage($nomClean, $d['prenom']);
                    ?>
                    <div class="filter-item-card <?= in_array($d['id'], $driver_ids) ? 'selected' : '' ?>" 
                         data-id="<?= $d['id'] ?>" 
                         data-type="driver"
                         onclick="toggleSelection(this)">
                         <img src="<?= $img ?>" alt="<?= htmlspecialchars($fullName) ?>">
                         <div class="filter-item-name"><?= htmlspecialchars($nomClean) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <button onclick="applyFilters()" class="btn btn-primary" style="padding: 10px 30px; font-size: 1.1rem; width: 100%; border-radius: 8px;">
                    Valider la sélection (<span id="count-selection">0</span>)
                </button>
            </div>
        </div>
    </div>

    <script>
        // Init selected arrays
        let selectedTeams = [<?= implode(',', $team_ids) ?>]; 
        let selectedDrivers = [<?= implode(',', $driver_ids) ?>];

        function updateCount() {
            const count = selectedTeams.length + selectedDrivers.length;
            document.getElementById('count-selection').innerText = count;
        }

        // Run on load
        document.addEventListener('DOMContentLoaded', updateCount);
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('open-filter-btn');
            if(btn) {
                btn.onclick = openNewsFilter;
            }
        });

        function openNewsFilter() {
            const modal = document.getElementById('filterModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            }
        }

        function closeNewsFilter() {
            const modal = document.getElementById('filterModal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = ''; // Restore scrolling
            }
        }

        function switchFilterTab(tabName) {
            // Update tabs
            const tabs = document.querySelectorAll('.filter-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all groups
            document.querySelectorAll('.filter-group').forEach(group => {
                group.classList.remove('active');
                group.style.display = 'none'; // Clear inline style if mixed
            });
            
            if (tabName === 'teams') {
                tabs[0].classList.add('active');
                document.getElementById('teams-list').classList.add('active');
                // Ensure display is grid via css or force it
                 document.getElementById('teams-list').style.display = 'grid';
            } else {
                tabs[1].classList.add('active');
                document.getElementById('drivers-list').classList.add('active');
                 document.getElementById('drivers-list').style.display = 'grid';
            }
        }
        
        function toggleSelection(element) {
            const id = parseInt(element.dataset.id);
            const type = element.dataset.type;
            
            element.classList.toggle('selected');
            
            if (type === 'team') {
                if (selectedTeams.includes(id)) {
                    selectedTeams = selectedTeams.filter(item => item !== id);
                } else {
                    selectedTeams.push(id);
                }
            } else if (type === 'driver') {
                if (selectedDrivers.includes(id)) {
                    selectedDrivers = selectedDrivers.filter(item => item !== id);
                } else {
                    selectedDrivers.push(id);
                }
            }
            updateCount();
        }

        function applyFilters() {
            let url = 'actualites.php?';
            const params = [];
            
            if (selectedTeams.length > 0) {
                params.push('teams=' + selectedTeams.join(','));
            }
            if (selectedDrivers.length > 0) {
                params.push('drivers=' + selectedDrivers.join(','));
            }
            
            if (params.length > 0) {
                url += params.join('&');
            } else {
                url += 'filter=all';
            }
            
            window.location.href = url;
        }

        // Close on click outside
        document.getElementById('filterModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNewsFilter();
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
