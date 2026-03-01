<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';

$stmt = $pdo->query("SELECT p.id, p.nom, p.prenom, p.numero, p.image_url, p.ecurie_id, e.couleur AS team_color, e.nom AS team_name 
                      FROM pilotes p 
                      JOIN ecuries e ON p.ecurie_id = e.id 
                      WHERE p.prenom != '' AND p.prenom IS NOT NULL
                      ORDER BY p.nom");
$pilotes = $stmt->fetchAll();

$stmt = $pdo->query("SELECT id, nom, image_url, couleur FROM ecuries WHERE nom NOT REGEXP '^[0-9]+$' ORDER BY nom");
$ecuries = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_pilotes = $_POST['pilotes'] ?? [];
    $selected_ecuries = $_POST['ecuries'] ?? [];

    if (empty($selected_pilotes) && empty($selected_ecuries)) {
        $error = 'Veuillez sélectionner au moins un pilote ou une écurie.';
    } else {
        // Verify user exists before proceeding
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkUser->execute([$_SESSION['user_id']]);
        if (!$checkUser->fetch()) {
            // User no longer exists in DB - force logout
            session_destroy();
            header('Location: connexion.php?error=session_expired');
            exit;
        }

        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users_pilotes_favoris WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            $stmt = $pdo->prepare("DELETE FROM users_ecuries_favorites WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            $stmt = $pdo->prepare("INSERT INTO users_pilotes_favoris (user_id, pilote_id) VALUES (?, ?)");
            foreach ($selected_pilotes as $pilote_id) {
                $stmt->execute([$_SESSION['user_id'], $pilote_id]);
            }

            $stmt = $pdo->prepare("INSERT INTO users_ecuries_favorites (user_id, ecurie_id) VALUES (?, ?)");
            foreach ($selected_ecuries as $ecurie_id) {
                $stmt->execute([$_SESSION['user_id'], $ecurie_id]);
            }

            $pdo->commit();
            header('Location: dashboard.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erreur lors de l\'enregistrement des préférences : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélection des Favoris - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="preferences-container" style="max-width: 1000px;">
            <h1>Sélectionnez vos favoris</h1>
            <p class="subtitle">Cliquez sur les pilotes et écuries que vous souhaitez suivre</p>

            <?php if (isset($_SESSION['registration_success'])): ?>
                <div class="alert alert-success">
                    🎉 Bienvenue sur F1 Aura ! Votre compte a été créé avec succès.<br>
                    Sélectionnez maintenant vos pilotes et écuries favoris pour personnaliser votre expérience.
                </div>
                <?php unset($_SESSION['registration_success']); ?>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="preferences-form">
                <div class="preferences-section">
                    <h2>🏎️ Pilotes</h2>
                    <div class="selection-grid">
                        <?php foreach ($pilotes as $pilote): ?>
                            <?php 
                                $nomClean = $pilote['nom'];
                                if (preg_match('/^(.*)([A-Z]{3})$/', $nomClean, $matches)) {
                                    $nomClean = trim($matches[1]);
                                }
                            ?>
                            <label class="selection-card driver-card" 
                                   style="--team-color: <?= htmlspecialchars($pilote['team_color']) ?>;"
                                   data-team-id="<?= $pilote['ecurie_id'] ?>"
                                   data-driver-name="<?= htmlspecialchars($nomClean) ?>"
                                   data-driver-firstname="<?= htmlspecialchars($pilote['prenom']) ?>">
                                <input type="checkbox" name="pilotes[]" value="<?= $pilote['id'] ?>" class="hidden-checkbox">
                                
                                <div class="driver-card-header">
                                    <div class="driver-rank"><?= $pilote['numero'] ?></div>
                                    <div class="driver-flag">
                                        
                                    </div>
                                </div>

                                <div class="selection-image-container">
                                    <?php if ($pilote['image_url']): ?>
                                        <img src="<?= htmlspecialchars($pilote['image_url']) ?>" alt="<?= htmlspecialchars($nomClean) ?>" class="selection-image driver-photo">
                                    <?php else: ?>
                                        <div class="placeholder-text">Photo non disponible</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="selection-info driver-info">
                                    <div class="driver-name">
                                        <span class="firstname"><?= htmlspecialchars($pilote['prenom']) ?></span>
                                        <span class="lastname"><?= htmlspecialchars($nomClean) ?></span>
                                    </div>
                                    <div class="team-line"></div>
                                    <div class="team-name"><?= htmlspecialchars($pilote['team_name'] ?? '') ?></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="preferences-section">
                    <h2>🏁 Écuries</h2>
                    <div class="selection-grid">
                        <?php foreach ($ecuries as $ecurie): ?>
                            <label class="selection-card team-card" 
                                   style="--team-color: <?= htmlspecialchars($ecurie['couleur']) ?>;"
                                   data-team-name="<?= htmlspecialchars($ecurie['nom']) ?>">
                                <input type="checkbox" name="ecuries[]" value="<?= $ecurie['id'] ?>" class="hidden-checkbox">
                                <div class="selection-image-container">
                                    <?php if ($ecurie['image_url']): ?>
                                        <img src="<?= htmlspecialchars($ecurie['image_url']) ?>" alt="<?= htmlspecialchars($ecurie['nom']) ?>" class="selection-image team-logo">
                                    <?php else: ?>
                                        <div class="placeholder-text">Logo non disponible</div>
                                    <?php endif; ?>
                                </div>
                                <div class="selection-info">
                                    <div class="selection-name"><?= htmlspecialchars($ecurie['nom']) ?></div>
                                    <div class="team-line"></div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">Enregistrer mes préférences</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.querySelectorAll('.hidden-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.selection-card');
                if (this.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
        });
    </script>
    <?php include 'includes/preference_modal.php'; ?>
    <script src="js/preferences.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
