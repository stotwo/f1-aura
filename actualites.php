<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

$filter = $_GET['filter'] ?? 'all';
$filter_id = $_GET['id'] ?? null;

$sql = "SELECT DISTINCT a.* FROM articles a";
$params = [];

if ($filter === 'favorites') {
    $sql .= "
        LEFT JOIN article_tags_pilotes atp ON a.id = atp.article_id
        LEFT JOIN users_pilotes_favoris upf ON atp.pilote_id = upf.pilote_id AND upf.user_id = ?
        LEFT JOIN article_tags_ecuries ate ON a.id = ate.article_id
        LEFT JOIN users_ecuries_favorites uef ON ate.ecurie_id = uef.ecurie_id AND uef.user_id = ?
        WHERE (upf.user_id IS NOT NULL OR uef.user_id IS NOT NULL)
    ";
    $params[] = $user_id;
    $params[] = $user_id;

} elseif ($filter === 'team' && $filter_id) {
    $sql .= " JOIN article_tags_ecuries ate ON a.id = ate.article_id WHERE ate.ecurie_id = ?";
    $params[] = $filter_id;

} elseif ($filter === 'driver' && $filter_id) {
    $sql .= " JOIN article_tags_pilotes atp ON a.id = atp.article_id WHERE atp.pilote_id = ?";
    $params[] = $filter_id;
}

$sql .= " ORDER BY a.date_publication DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$teams = $pdo->query("SELECT id, nom FROM ecuries WHERE nom NOT REGEXP '^[0-9]+$' ORDER BY nom")->fetchAll();
$drivers = $pdo->query("SELECT id, nom, prenom FROM pilotes WHERE prenom != '' AND prenom IS NOT NULL ORDER BY nom")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Actualités F1 - Aura</title>
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
            margin-bottom: 1rem;
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="news-container">
        <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h1>🏁 Dernières Actualités</h1>
            <a href="fetch_news.php" class="btn btn-secondary" style="font-size:0.8rem;">🔄 Actualiser</a>
        </div>

        <div class="news-filters">
            <a href="?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">Toutes</a>
            <a href="?filter=favorites" class="filter-btn <?= $filter === 'favorites' ? 'active' : '' ?>">⭐ Mes Favoris</a>
            
            <form action="" method="GET" style="display:flex; gap:0.5rem; align-items:center;">
                <input type="hidden" name="filter" value="team">
                <select name="id" class="filter-select" onchange="this.form.submit()">
                    <option value="">Par Écurie...</option>
                    <?php foreach ($teams as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($filter === 'team' && $filter_id == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <form action="" method="GET" style="display:flex; gap:0.5rem; align-items:center;">
                <input type="hidden" name="filter" value="driver">
                <select name="id" class="filter-select" onchange="this.form.submit()">
                    <option value="">Par Pilote...</option>
                    <?php foreach ($drivers as $d): ?>
                        <?php 
                            $nomClean = $d['nom'];
                            if (preg_match('/^(.*)([A-Z]{3})$/', $nomClean, $matches)) {
                                $nomClean = trim($matches[1]);
                            }
                            $fullName = trim($d['prenom'] . ' ' . $nomClean);
                        ?>
                        <option value="<?= $d['id'] ?>" <?= ($filter === 'driver' && $filter_id == $d['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fullName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="news-grid">
            <?php 
            $message = "Aucune actualité trouvée pour ce filtre.";
            if ($filter === 'favorites' && empty($articles)) {
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
            ?>

            <?php if (empty($articles)): ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #ccc; margin-top: 2rem;">
                    <p><?= htmlspecialchars($message) ?></p>
                    <?php if ($message !== "Aucune actualité trouvée pour ce filtre."): ?>
                        <br>
                        <a href="selection-favoris.php" class="btn btn-primary">Gérer mes favoris</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <article class="news-card">
                        <?php if ($article['image_url']): ?>
                            <div class="news-image">
                                <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="">
                            </div>
                        <?php endif; ?>
                        <div class="news-content">
                            <span class="news-source">
                                <?= htmlspecialchars($article['source']) ?> • <?= date('d/m H:i', strtotime($article['date_publication'])) ?>
                            </span>
                            <a href="<?= htmlspecialchars($article['lien']) ?>" target="_blank" class="news-title">
                                <?= htmlspecialchars($article['titre']) ?>
                            </a>
                            <div class="news-desc">
                                <?= substr(strip_tags($article['description']), 0, 100) ?>...
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
