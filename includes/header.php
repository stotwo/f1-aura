<header class="main-header">
    <div class="header-container">
        <a href="./" class="logo">
            <img src="PICS/logo.png" alt="F1 Aura" class="logo-img" style="height: 50px; width: auto; max-width: 100%;">
        </a>

        <div class="header-right" style="display: flex; align-items: center; gap: 1rem;">
            <?php if (isLoggedIn()): ?>
                <div class="user-menu" style="position: relative;">
                    <button class="user-avatar" aria-label="Menu utilisateur">
                        <span class="avatar-initials">
                            <?php 
                            $initials = strtoupper(substr($_SESSION['user_prenom'] ?? 'U', 0, 1) . substr($_SESSION['user_nom'] ?? 'S', 0, 1));
                            echo htmlspecialchars($initials);
                            ?>
                        </span>
                    </button>
                    
                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></div>
                                <div class="user-email"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="dashboard.php" class="dropdown-item">
                            <span class="dropdown-icon">📊</span>
                            Tableau de Bord
                        </a>
                        <a href="parametres.php" class="dropdown-item">
                            <span class="dropdown-icon">⚙️</span>
                            Paramètres
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="deconnexion.php" class="dropdown-item logout-item">
                            <span class="dropdown-icon">🚪</span>
                            Déconnexion
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <button class="hamburger" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <nav class="main-nav">
            <a href="./" class="nav-link">Accueil</a>
            <a href="actualites" class="nav-link">Actualités</a>
            <a href="calendrier" class="nav-link">Calendrier 2026</a>
            <a href="saison_2025" class="nav-link">Saison 2025</a>
            <a href="statistiques" class="nav-link">Statistiques</a>
            
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="nav-link desktop-only" style="display: none;">Tableau de Bord</a>
            <?php else: ?>
                <a href="connexion.php" class="nav-link btn-login">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const nav = document.querySelector('.main-nav');
    
    if(hamburger) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            nav.classList.toggle('active');
        });
    }

    const userAvatar = document.querySelector('.user-avatar');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if(userAvatar && userDropdown) {
        userAvatar.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-menu')) {
                userDropdown.classList.remove('active');
            }
        });
    }
});
</script>
