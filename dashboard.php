<?php
require_once 'config.php';
requireLogin();

$stmt = $pdo->prepare("
    SELECT p.* 
    FROM users_pilotes_favoris upf
    JOIN pilotes p ON upf.pilote_id = p.id
    WHERE upf.user_id = ?
    ORDER BY p.nom
");
$stmt->execute([$_SESSION['user_id']]);
$favoris_pilotes = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT e.* 
    FROM users_ecuries_favorites uef
    JOIN ecuries e ON uef.ecurie_id = e.id
    WHERE uef.user_id = ?
    ORDER BY e.nom
");
$stmt->execute([$_SESSION['user_id']]);
$favoris_ecuries = $stmt->fetchAll();

if (empty($favoris_pilotes) && empty($favoris_ecuries)) {
    
    
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
    <style>
        .mini-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .mini-card {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        .mini-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            object-position: top; 
            margin-bottom: 0.5rem;
            border: 2px solid var(--primary-color);
            background-color: #f0f0f0; 
        }
        .mini-card h4 {
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
            color: var(--text-color);
        }
        .mini-card span {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>Bienvenue, <?= htmlspecialchars($_SESSION['user_prenom']) ?> !</h1>
            <a href="parametres.php#mes-pilotes" class="btn btn-secondary">⚙️ Modifier mes préférences</a>
        </div>

        <div class="dashboard-grid">
            <section class="dashboard-card favorites-card" style="grid-column: 1 / -1;">
                <h2>⭐ Mes Favoris</h2>
                
                <?php if ($favoris_pilotes): ?>
                    <h3>Pilotes suivis</h3>
                    <div class="mini-grid">
                        <?php foreach ($favoris_pilotes as $pilote): ?>
                            <div class="mini-card">
                                <?php if ($pilote['image_url']): ?>
                                    <img src="<?= htmlspecialchars($pilote['image_url']) ?>" alt="<?= htmlspecialchars($pilote['nom']) ?>">
                                <?php endif; ?>
                                <h4><?= htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']) ?></h4>
                                <span>#<?= $pilote['numero'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($favoris_ecuries): ?>
                    <h3 style="margin-top: 1.5rem;">Écuries suivies</h3>
                    <div class="mini-grid">
                        <?php foreach ($favoris_ecuries as $ecurie): 
                            
                            $teamColor = !empty($ecurie['couleur']) ? $ecurie['couleur'] : '#f0f0f0';
                        ?>
                            <div class="mini-card">
                                <?php if ($ecurie['image_url']): ?>
                                    <img src="<?= htmlspecialchars($ecurie['image_url']) ?>" alt="<?= htmlspecialchars($ecurie['nom']) ?>" style="border-radius: 10px; object-fit: contain; object-position: center; background-color: <?= $teamColor ?>; padding: 10px; width: 100%; height: 100px; border: none;">
                                <?php endif; ?>
                                <h4><?= htmlspecialchars($ecurie['nom']) ?></h4>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($favoris_pilotes) && empty($favoris_ecuries)): ?>
                    <p style="text-align: center; color: rgba(255,255,255,0.6);">Aucun favoris sélectionné. <a href="parametres.php#mes-pilotes" class="link">Ajoutez-en maintenant !</a></p>
                <?php endif; ?>
            </section>

            <section class="dashboard-card news-feed" style="grid-column: 1 / -1;">
                <h2>📰 Flux d'actualités personnalisé</h2>
                <div class="news-list">
                    <?php 
                    if ($favoris_pilotes || $favoris_ecuries): 
                        
                        $newsSql = "
                            SELECT DISTINCT a.* 
                            FROM articles a
                            LEFT JOIN article_tags_pilotes atp ON a.id = atp.article_id
                            LEFT JOIN users_pilotes_favoris upf ON atp.pilote_id = upf.pilote_id AND upf.user_id = ?
                            LEFT JOIN article_tags_ecuries ate ON a.id = ate.article_id
                            LEFT JOIN users_ecuries_favorites uef ON ate.ecurie_id = uef.ecurie_id AND uef.user_id = ?
                            WHERE (upf.user_id IS NOT NULL OR uef.user_id IS NOT NULL)
                            ORDER BY a.date_publication DESC
                            LIMIT 5;
                        ";
                        $newsStmt = $pdo->prepare($newsSql);
                        $newsStmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
                        $dashboardNews = $newsStmt->fetchAll();

                        if ($dashboardNews):
                            foreach ($dashboardNews as $news):
                    ?>
                            <div class="favorite-news-item horizontal-card" onclick="window.open('<?= htmlspecialchars($news['lien']) ?>', '_blank')">
                                <?php if (!empty($news['image_url'])): ?>
                                    <div class="news-img-wrapper">
                                        <img src="<?= htmlspecialchars($news['image_url']) ?>" alt="News" loading="lazy">
                                    </div>
                                <?php endif; ?>
                                <div class="news-content">
                                    <span class="news-title"><?= htmlspecialchars($news['titre']) ?></span>
                                    <span class="news-date">
                                        <?php 
                                            // Simple "Time Ago" logic
                                            $time = strtotime($news['date_publication']);
                                            $diff = time() - $time;
                                            if ($diff < 3600) {
                                                echo floor($diff / 60) . ' min ago';
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . ' hours ago';
                                            } else {
                                                echo date('d M Y', $time);
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                    <?php 
                            endforeach;
                            echo '<div style="margin-top:1rem; text-align:center;"><a href="actualites.php?filter=favorites" class="link">Voir toutes mes actualités</a></div>';
                        else:
                    ?>
                            <p class="placeholder-text">Aucune actualité récente pour vos favoris.</p>
                            <div style="margin-top:1rem; text-align:center;"><a href="actualites.php" class="link">Voir toutes les news F1</a></div>
                    <?php 
                        endif;
                    else: 
                    ?>
                        <p class="placeholder-text">Sélectionnez des favoris pour voir leurs actualités.</p>
                        <div style="margin-top:1rem; text-align:center;"><a href="actualites.php" class="link">Voir les actualités générales</a></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-card quick-links" style="grid-column: 1 / -1;">
                <h2>🔗 Accès Rapide</h2>
                <div class="quick-links-grid" style="grid-template-columns: repeat(4, 1fr) !important;">
                    <a href="actualites.php" class="quick-link">
                        <span class="quick-link-icon">📰</span>
                        <span>Actualités</span>
                    </a>
                    <a href="calendrier.php" class="quick-link">
                        <span class="quick-link-icon">🏁</span>
                        <span>Calendrier</span>
                    </a>
                    <a href="statistiques.php" class="quick-link">
                        <span class="quick-link-icon">📊</span>
                        <span>Statistiques</span>
                    </a>
                    <a href="parametres.php" class="quick-link">
                        <span class="quick-link-icon">⚙️</span>
                        <span>Paramètres</span>
                    </a>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
