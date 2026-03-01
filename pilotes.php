<?php
require_once 'config.php';
include 'includes/header.php';

// Fetch all drivers with their team info
$stmt = $pdo->query("
    SELECT p.*, e.nom as team_name, e.couleur as team_color 
    FROM pilotes p 
    LEFT JOIN ecuries e ON p.ecurie_id = e.id 
    ORDER BY e.nom, p.nom
");
$pilotes = $stmt->fetchAll();
?>

<div class="container" style="padding: 100px 20px 40px;">
    <h1 style="text-align: center; margin-bottom: 40px; font-size: 2.5rem;">Grille des Pilotes 2025</h1>
    
    <div class="drivers-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
        <?php foreach ($pilotes as $pilote): ?>
            <div class="driver-card" style="
                background: linear-gradient(135deg, rgba(20,20,25,0.9), rgba(30,30,40,0.8)); 
                border-radius: 12px; 
                overflow: hidden; 
                border-top: 4px solid <?= htmlspecialchars($pilote['team_color']) ?>;
                transition: transform 0.3s ease;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            ">
                <div class="driver-image" style="height: 300px; overflow: hidden; position: relative; background: radial-gradient(circle at center, rgba(60,60,70,0.4), transparent);">
                    <?php if (!empty($pilote['image_url'])): ?>
                        <img src="<?= htmlspecialchars($pilote['image_url']) ?>" alt="<?= htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: top center;">
                    <?php else: ?>
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #555; font-size: 4rem;">
                            <i class="fa-solid fa-user-astronaut"></i>
                        </div>
                    <?php endif; ?>
                    <div class="driver-number" style="position: absolute; top: 10px; right: 10px; font-size: 3rem; font-weight: 900; color: rgba(255,255,255,0.1); font-family: 'F1', sans-serif;">
                        <?= htmlspecialchars($pilote['numero']) ?>
                    </div>
                </div>
                <div class="driver-info" style="padding: 20px;">
                    <div class="driver-team" style="color: <?= htmlspecialchars($pilote['team_color']) ?>; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 5px;">
                        <?= htmlspecialchars($pilote['team_name']) ?>
                    </div>
                    <h2 class="driver-name" style="margin: 0; font-size: 1.5rem; line-height: 1.2;">
                        <span style="font-weight: 300; display: block; font-size: 1rem;"><?= htmlspecialchars($pilote['prenom']) ?></span>
                        <span style="font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($pilote['nom']) ?></span>
                    </h2>
                    <div class="driver-country" style="margin-top: 10px; display: flex; align-items: center; opacity: 0.7; font-size: 0.9rem;">
                        <i class="fa-solid fa-flag" style="margin-right: 8px;"></i> <?= htmlspecialchars($pilote['nationalite']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .driver-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.5) !important;
    }
</style>

<?php include 'includes/footer.php'; ?>
