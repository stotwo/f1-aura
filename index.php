<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Aura - Accueil</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Bienvenue sur F1 Aura</h1>
            <p class="hero-subtitle">Votre portail complet pour suivre la Formule 1</p>
            
            <div class="hero-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-primary">Mon Tableau de Bord</a>
                <?php else: ?>
                    <a href="inscription.php" class="btn btn-primary">S'inscrire</a>
                    <a href="connexion.php" class="btn btn-secondary">Se connecter</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Découvrez nos fonctionnalités</h2>
            
            <div class="features-grid">
                <a href="actualites.php" class="feature-card">
                    <div class="feature-icon">📰</div>
                    <h3>Actualités Générales</h3>
                    <p>Restez informé des dernières nouvelles de la F1</p>
                </a>

                <a href="calendrier.php" class="feature-card">
                    <div class="feature-icon">🏁</div>
                    <h3>Calendrier & Résultats</h3>
                    <p>Consultez le calendrier et les résultats des courses</p>
                </a>

                <a href="statistiques.php" class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Statistiques Interactives</h3>
                    <p>Explorez les graphiques et courbes des performances</p>
                </a>

                <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="feature-card highlight">
                    <div class="feature-icon">⭐</div>
                    <h3>Tableau Personnalisé</h3>
                    <p>Suivez vos pilotes et écuries favoris</p>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
