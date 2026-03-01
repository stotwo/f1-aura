<?php
require_once 'config.php';
include 'includes/header.php';

// Fetch all teams
$stmt = $pdo->query("SELECT * FROM ecuries ORDER BY nom");
$ecuries = $stmt->fetchAll();
?>

<div class="container" style="padding: 100px 20px 40px; text-align: center;">
    <h1 style="margin-bottom: 40px; font-size: 2.5rem;">Plateau des Constructeurs 2025</h1>
    
    <div class="teams-grid" style="
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        padding: 0 20px;
    ">
        <?php foreach ($ecuries as $team): ?>
            <div class="team-card" style="
                background: linear-gradient(135deg, rgba(20,20,30,0.9), rgba(40,40,50,0.8));
                border-radius: 16px;
                overflow: hidden;
                border: 2px solid <?= htmlspecialchars($team['couleur']) ?>;
                position: relative;
                transition: transform 0.4s ease, box-shadow 0.4s ease;
            ">
                <div class="team-header" style="height: 180px; overflow: hidden; background: <?= htmlspecialchars($team['couleur']) ?>;">
                    <div style="background: rgba(0,0,0,0.3); height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: rgba(255,255,255,0.2);">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                </div>
                
                <div class="team-logo-container" style="
                    margin-top: -60px; 
                    display: flex; 
                    justify-content: center;
                    position: relative;
                    z-index: 10;
                ">
                    <div class="team-logo-wrapper" style="
                        background: #15151e;
                        padding: 10px;
                        border-radius: 50%;
                        border: 3px solid <?= htmlspecialchars($team['couleur']) ?>;
                        width: 120px;
                        height: 120px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                         <?php if (!empty($team['image_url'])): ?>
                            <img src="<?= htmlspecialchars($team['image_url']) ?>" alt="<?= htmlspecialchars($team['nom']) ?>" style="width: 80%; height: auto; object-fit: contain;">
                        <?php else: ?>
                            <i class="fa-solid fa-flag-checkered" style="font-size: 2rem;"></i>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="team-details" style="padding: 30px 20px;">
                    <h2 style="font-size: 1.5rem; text-transform: uppercase; margin-bottom: 10px; min-height: 60px;">
                        <?= htmlspecialchars($team['nom']) ?>
                    </h2>
                    <div class="team-country" style="margin-bottom: 15px; opacity: 0.7;">
                        <i class="fa-solid fa-map-marker-alt"></i> <?= htmlspecialchars($team['pays']) ?>
                    </div>
                </div>
                
                <div class="team-actions" style="padding: 0 20px 30px;">
                    <a href="#" class="team-btn" style="
                        display: inline-block;
                        padding: 10px 25px;
                        border: 1px solid <?= htmlspecialchars($team['couleur']) ?>;
                        color: <?= htmlspecialchars($team['couleur']) ?>;
                        text-decoration: none;
                        border-radius: 30px;
                        text-transform: uppercase;
                        font-weight: 600;
                        transition: all 0.2s ease;
                    ">Profil Complet</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .team-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 50px rgba(0,0,0,0.6) !important;
    }
    .team-btn:hover {
        background: rgba(255,255,255,0.1); 
        box-shadow: 0 0 15px currentColor;
    }
</style>

<?php include 'includes/footer.php'; ?>
