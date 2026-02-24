<footer class="main-footer">
    <div class="footer-container">
        <!-- Section Marque -->
        <div class="footer-column footer-brand">
            <h3 class="footer-logo">F1 AURA</h3>
            <p class="footer-desc">Vivez la passion de la Formule 1 en temps réel. Actualités, résultats et analyses au cœur de la course.</p>
            <div class="footer-social">
                <a href="https://github.com/stotwo/" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
            </div>
        </div>

        <!-- Section Navigation -->
        <div class="footer-column footer-links">
            <h4>Navigation</h4>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="actualites.php">Actualités</a></li>
                <li><a href="calendrier.php">Calendrier</a></li>
                <li><a href="statistiques.php">Statistiques</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Mon Compte</a></li>
                <?php else: ?>
                    <li><a href="inscription.php">Rejoindre la communauté</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="footer-bottom" style="border-top: none; padding-top: 1rem; margin-top: 0;">
        <p>&copy; 2026 F1 Aura. Fait avec passion pour les fans.</p>
    </div>
</footer>
