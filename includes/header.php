<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// isLoggedIn() is defined in config.php which should be included before this file.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Aura - Le Paddock Numérique</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="PICS/logo.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <!-- Logo -->
        <a href="./" class="brand-logo">
            <img src="PICS/logo.png" alt="F1 Aura" class="logo-img">
        </a>

        <!-- Desktop Navigation CENTERED -->
        <nav class="desktop-nav">
            <a href="actualites.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'actualites.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-regular fa-newspaper"></i></span>
                <span class="nav-text">Actualités</span>
            </a>
            <a href="calendrier.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'calendrier.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-regular fa-calendar-days"></i></span>
                <span class="nav-text">Calendrier</span>
            </a>
            <a href="classements.php?year=2025" class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'classement') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-trophy"></i></span>
                <span class="nav-text">Classement</span>
            </a>
            <a href="statistiques.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                <span class="nav-text">Statistiques</span>
            </a>
        </nav>

        <!-- Use Actions RIGHT -->
        <div class="header-actions">
            <?php if (isLoggedIn()): ?>
                <div class="user-menu-container">
                    <button class="user-avatar-btn" id="userMenuToggle">
                        <?php 
                        $initials = strtoupper(substr($_SESSION['user_prenom'] ?? 'U', 0, 1) . substr($_SESSION['user_nom'] ?? 'S', 0, 1));
                        echo htmlspecialchars($initials);
                        ?>
                    </button>
                    <div class="user-dropdown-menu" id="userMenu">
                        <div class="user-header">
                            <span class="user-name"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
                            <span class="user-email"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></span>
                        </div>
                        <ul class="user-links">
                            <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Tableau de bord</a></li>
                            <li><a href="parametres.php"><i class="fa-solid fa-sliders"></i> Paramètres</a></li>
                            <li class="separator"></li>
                            <li><a href="deconnexion.php" class="logout"><i class="fa-solid fa-power-off"></i> Déconnexion</a></li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="btn-login">Connexion</a>
                <a href="inscription.php" class="btn-signup">Inscription</a>
            <?php endif; ?>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-toggle" id="mobileMenuToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation Overlay -->
<div class="mobile-nav-overlay" id="mobileNav">
    <div class="mobile-nav-header">
        <span class="brand-text">F1<span class="accent">AURA</span></span>
        <button class="close-mobile" id="closeMobileNav">&times;</button>
    </div>
    <div class="mobile-links">
        <a href="calendrier.php"><i class="fa-regular fa-calendar-days"></i> Calendrier</a>
        <a href="classements.php?year=2025"><i class="fa-solid fa-trophy"></i> Classement</a>
        <a href="pilotes.php"><i class="fa-solid fa-user-astronaut"></i> Pilotes</a>
        <a href="ecuries.php"><i class="fa-solid fa-people-group"></i> Écuries</a>
        <a href="actualites.php"><i class="fa-regular fa-newspaper"></i> Actualités</a>
        <a href="statistiques.php"><i class="fa-solid fa-chart-pie"></i> Statistiques</a>
    </div>
    <?php if (!isLoggedIn()): ?>
        <div class="mobile-auth">
            <a href="connexion.php" class="btn-login">Connexion</a>
            <a href="inscription.php" class="btn-signup">Inscription</a>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // User Dropdown
        const userBtn = document.getElementById('userMenuToggle');
        const userMenu = document.getElementById('userMenu');
        
        if(userBtn && userMenu) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.classList.toggle('active');
            });
            
            document.addEventListener('click', (e) => {
                if (!userMenu.contains(e.target) && !userBtn.contains(e.target)) {
                    userMenu.classList.remove('active');
                }
            });
        }

        // Mobile Menu
        const mobileBtn = document.getElementById('mobileMenuToggle');
        const mobileNav = document.getElementById('mobileNav');
        const closeBtn = document.getElementById('closeMobileNav');
        
        if(mobileBtn && mobileNav) {
            mobileBtn.addEventListener('click', () => {
                mobileNav.classList.add('open');
                document.body.style.overflow = 'hidden';
            });
        }
        
        if(closeBtn) {
            closeBtn.addEventListener('click', () => {
                mobileNav.classList.remove('open');
                document.body.style.overflow = '';
            });
        }
    });
</script>