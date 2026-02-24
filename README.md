# F1 Aura 🏎️

Bienvenue sur le dépôt **F1 Aura**, une plateforme web dédiée aux passionnés de Formule 1. Ce projet permet de suivre l'actualité, le calendrier, les résultats des courses et bien plus encore pour la saison 2025 et au-delà.

![F1 Aura Logo](PICS/logo.png)

## 📌 Fonctionnalités

*   **Actualités F1** : Un fil d'actualités mis à jour régulièrement.
*   **Calendrier & Saison 2025** : Visualisation des dates et circuits de la saison à venir.
*   **Résultats de Courses** : Suivi des classements et résultats des Grands Prix.
*   **Statistiques** : Données détaillées sur les pilotes et les écuries.
*   **Espace Membre** :
    *   Inscription et Connexion sécurisées.
    *   Dashboard personnalisé.
    *   Sélection de pilotes et écuries favoris pour un flux personnalisé.
*   **Scraping Automatisé** : Scripts intégrés pour récupérer les derniers résultats (via `scrape_f1_results.php`).

## 🛠️ Stack Technique

*   **Backend** : PHP (Native)
*   **Base de données** : MySQL
*   **Frontend** : HTML5, CSS3, JavaScript
*   **Déploiement** : GitHub Actions (CI/CD) vers AlwaysData via LFTP

## 🚀 Installation en local

1.  **Cloner le dépôt** :
    ```bash
    git clone https://github.com/votre-utilisateur/f1-aura.git
    cd f1-aura
    ```

2.  **Base de données** :
    *   Créez une base de données MySQL.
    *   Importez le fichier `full_import.sql` situé à la racine du projet pour créer les tables et insérer les données initiales.

3.  **Configuration** :
    *   Assurez-vous d'avoir un fichier `config.php` à la racine (souvent ignoré par Git pour la sécurité) contenant vos identifiants de base de données :
    ```php
    <?php
    $host = 'localhost';
    $dbname = 'votre_bdd';
    $username = 'votre_user';
    $password = 'votre_password';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Erreur de connexion : " + $e->getMessage());
    }
    ?>
    ```

4.  **Lancer le projet** :
    *   Utilisez XAMPP/WAMP ou un serveur PHP intégré :
    ```bash
    php -S localhost:8000
    ```
    *   Accédez à `http://localhost:8000` dans votre navigateur.

## ⚙️ Déploiement

Le projet intègre un workflow GitHub Actions (`.github/workflows/deploy.yml`) qui déploie automatiquement les modifications sur un serveur FTP (AlwaysData dans ce cas) lors d'un `push` sur la branche `main`.

---
*Projet développé dans le cadre de F1-Aura.*
