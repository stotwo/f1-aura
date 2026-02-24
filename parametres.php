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

$stmt = $pdo->prepare("SELECT pilote_id FROM users_pilotes_favoris WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_pilotes = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT ecurie_id FROM users_ecuries_favorites WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_ecuries = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($prenom) || empty($nom) || empty($email)) {
        $error = 'Le prénom, le nom et l\'email sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = 'Cet email est déjà utilisé par un autre compte.';
            } else {
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = 'Veuillez entrer votre mot de passe actuel pour le modifier.';
                    } else {
                        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                        
                        if (!password_verify($current_password, $user['password'])) {
                            $error = 'Mot de passe actuel incorrect.';
                        } elseif (strlen($new_password) < 6) {
                            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
                        } elseif ($new_password !== $confirm_password) {
                            $error = 'Les mots de passe ne correspondent pas.';
                        } else {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ?, email = ?, password = ? WHERE id = ?");
                            $stmt->execute([$prenom, $nom, $email, $hashed_password, $_SESSION['user_id']]);
                            
                            $_SESSION['user_prenom'] = $prenom;
                            $_SESSION['user_nom'] = $nom;
                            $_SESSION['user_email'] = $email;
                            
                            $success = 'Vos informations et votre mot de passe ont été mis à jour avec succès !';
                        }
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ?, email = ? WHERE id = ?");
                    $stmt->execute([$prenom, $nom, $email, $_SESSION['user_id']]);
                    
                    $_SESSION['user_prenom'] = $prenom;
                    $_SESSION['user_nom'] = $nom;
                    $_SESSION['user_email'] = $email;
                    
                    $success = 'Vos informations ont été mises à jour avec succès !';
                }
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_favorites'])) {
    $selected_pilotes = $_POST['pilotes'] ?? [];
    $selected_ecuries = $_POST['ecuries'] ?? [];

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
        $success = 'Vos préférences ont été mises à jour avec succès !';
        
        $stmt = $pdo->prepare("SELECT pilote_id FROM users_pilotes_favoris WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_pilotes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare("SELECT ecurie_id FROM users_ecuries_favorites WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_ecuries = $stmt->fetchAll(PDO::FETCH_COLUMN);

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Paramètres - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="preferences-container" style="max-width: 1000px;">
            <h1>⚙️ Mes Paramètres</h1>
            <p class="subtitle">Gérez vos informations personnelles et vos favoris</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" class="preferences-form" style="margin-bottom: 3rem;">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="preferences-section">
                    <h2>👤 Informations Personnelles</h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" required 
                                   value="<?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="preferences-section">
                    <h2>🔒 Modifier le Mot de Passe</h2>
                    <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 1rem;">
                        Laissez vide si vous ne souhaitez pas changer votre mot de passe
                    </p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" 
                                   placeholder="Requis pour changer le mot de passe">
                        </div>

                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" 
                                   minlength="6" placeholder="Min. 6 caractères">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   minlength="6" placeholder="Confirmez le nouveau">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">💾 Enregistrer mes informations</button>
                </div>
            </form>

            <form method="POST" class="preferences-form">
                <input type="hidden" name="update_favorites" value="1">
                <div class="preferences-section">
                    <h2>🏎️ Mes Pilotes Favoris</h2>
                    <div class="selection-grid">
                        <?php foreach ($pilotes as $pilote): ?>
                            <?php 
                                $is_selected = in_array($pilote['id'], $user_pilotes); 
                                $selected_class = $is_selected ? 'selected' : '';
                                $checked = $is_selected ? 'checked' : '';

                                $nomClean = $pilote['nom'];
                                if (preg_match('/^(.*)([A-Z]{3})$/', $nomClean, $matches)) {
                                    $nomClean = trim($matches[1]);
                                }
                            ?>
                            <label class="selection-card driver-card <?= $selected_class ?>" 
                                   style="--team-color: <?= htmlspecialchars($pilote['team_color']) ?>;"
                                   data-team-id="<?= $pilote['ecurie_id'] ?>"
                                   data-driver-name="<?= htmlspecialchars($nomClean) ?>"
                                   data-driver-firstname="<?= htmlspecialchars($pilote['prenom']) ?>">
                                <input type="checkbox" name="pilotes[]" value="<?= $pilote['id'] ?>" class="hidden-checkbox" <?= $checked ?>>
                                
                                <div class="driver-card-header">
                                    <div class="driver-rank"><?= $pilote['numero'] ?></div>
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
                    <h2>🏁 Mes Écuries Favorites</h2>
                    <div class="selection-grid">
                        <?php foreach ($ecuries as $ecurie): ?>
                            <?php 
                                $is_selected = in_array($ecurie['id'], $user_ecuries); 
                                $selected_class = $is_selected ? 'selected' : '';
                                $checked = $is_selected ? 'checked' : '';
                            ?>
                            <label class="selection-card team-card <?= $selected_class ?>" 
                                   style="--team-color: <?= htmlspecialchars($ecurie['couleur']) ?>;"
                                   data-team-name="<?= htmlspecialchars($ecurie['nom']) ?>">
                                <input type="checkbox" name="ecuries[]" value="<?= $ecurie['id'] ?>" class="hidden-checkbox" <?= $checked ?>>
                                <div class="selection-image-container">
                                    <?php if ($ecurie['image_url']): ?>
                                        <img src="<?= htmlspecialchars($ecurie['image_url']) ?>" alt="<?= htmlspecialchars($ecurie['nom']) ?>" class="selection-image team-logo" style="background-color: var(--team-color);">
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
                    <a href="dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
                    <button type="submit" class="btn btn-primary btn-large">⭐ Enregistrer mes favoris</button>
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
