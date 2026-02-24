<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires';
    } else {
        $stmt = $pdo->prepare("SELECT id, email, password, nom, prenom FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users_pilotes_favoris WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $hasFavorites = $stmt->fetchColumn() > 0;

            if (!$hasFavorites) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users_ecuries_favorites WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $hasFavorites = $stmt->fetchColumn() > 0;
            }

            if ($hasFavorites) {
                header('Location: dashboard.php');
            } else {
                header('Location: selection-favoris.php');
            }
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - F1 Aura</title>
    <link rel="icon" type="image/png" href="PICS/logo.png">
    <link rel="stylesheet" href="CSS/style.css?v=<?php echo time(); ?>">
</head>
<body class="auth-body">
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-container">
        <div class="auth-card">
            <h1>Se connecter</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
            </form>

            <p class="text-center">
                Pas encore de compte ? <a href="inscription.php" class="link">S'inscrire</a>
            </p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
