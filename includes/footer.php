<footer class="main-footer">
    <div class="footer-container">
        <!-- Section Marque -->
        <div class="footer-column footer-brand">
            <h3 class="footer-logo">F1 AURA</h3>
            <p class="footer-desc">Vivez la passion de la Formule 1 en temps réel. Actualités, résultats et analyses au cœur de la course.</p>
            <div class="footer-social">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
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

        <!-- Section Newsletter (Nouveau) -->
        <div class="footer-column footer-newsletter">
            <h4>Restez informé</h4>
            <p>Recevez les dernières news F1 directement dans votre boîte mail.</p>
            <form class="newsletter-form" onsubmit="event.preventDefault();">
                <input type="email" placeholder="Votre email..." required>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> F1 Aura. Fait avec passion pour les fans.</p>
        </div>
        <div class="footer-legal">
            <a href="#">Mentions Légales</a>
            <a href="#">Confidentialité</a>
            <a href="#">Cookies</a>
        </div>
    </div>
</footer>
