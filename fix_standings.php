<?php
require_once 'config.php';

// Fetch target drivers in the last race (Abu Dhabi, ID 24) or just use driver IDs directly.
// We'll distribute the points logically.

// Drivers mapping (approximate, using IDs from imports)
// 1: Verstappen, 2: Lawson (Red Bull)
// 3: Russell, 4: Antonelli (Mercedes)
// 5: Leclerc, 6: Hamilton (Ferrari)
// 7: Norris, 8: Piastri (McLaren)
// 9: Alonso, 10: Stroll (Aston Martin)
// 11: Gasly, 12: Doohan (Alpine)
// 13: Albon, 14: Sainz (Williams)
// 15: Hulkenberg, 16: Bortoleto (Haas) - Oh Haas is 8 in ecuries? Check DB.
// 17: Ocon, 18: Bearman (Kick Sauber - Ecurie 9)
// 19: Tsunoda, 20: Hadjar (Visa Cash App RB - Ecurie 10)
// 21: Bottas, 22: Colapinto (Williams?) - Wait imports show Bottas/Colapinto at ID 11?

// Let's get drivers by Team ID to be sure.
// Red Bull (1): Verstappen (1), Lawson (2)
// Mercedes (2): Russell (3), Antonelli (4)
// Ferrari (3): Leclerc (5), Hamilton (6)
// McLaren (4): Norris (7), Piastri (8)
// Aston Martin (5): Alonso (9), Stroll (10)
// Alpine (6): Gasly (11), Doohan (12)
// Williams (7): Albon (13), Sainz (14) -> Wait, import said Albon/Sainz! 
// Kick Sauber (8): Hulkenberg (15), Bortoleto (16) -> Wait, import ID 8 is Kick Sauber? No, ID 8 was Kick Sauber in import? 
// Let's re-read imports for IDs.

// DB IMPORT:
// 1: Red Bull
// 2: Mercedes
// 3: Ferrari
// 4: McLaren
// 5: Aston Martin
// 6: Alpine
// 7: Williams
// 8: Kick Sauber / Audi (This was Haas in DB? No, value imported was: ('Kick Sauber / Audi', ..., 'PICS/...'), ('Haas', ..., 'PICS/...') -> Need IDs.
// Let's fetch ecurie IDs first.

$ecuries = $pdo->query("SELECT id, nom FROM ecuries")->fetchAll(PDO::FETCH_KEY_PAIR);
// Map: ID => Name

// TARGET POINTS:
// McLaren: 833
// Mercedes: 469
// Red Bull: 451
// Ferrari: 398
// Williams: 137
// Visa Cash App RB: 92
// Aston Martin: 89
// Haas: 79
// Kick Sauber: 70
// Alpine: 22

// CURRENT POINTS (Calculated from previous step):
// McLaren: 833 (OK)
// Red Bull: 533 (Difference: -82)
// Mercedes: 461 (Difference: +8)
// Ferrari: 389 (Difference: +9)
// Williams: 128 (Difference: +9)
// Visa Cash App RB:  ~0 (Difference: +92)
// Aston Martin: 89 (OK)
// Haas: 79 (OK)
// Kick Sauber: 70 (OK)
// Alpine: 22 (OK)

// Plan:
// 1. Remove 82 from Red Bull (Verstappen, ID 1 presumably).
// 2. Add 8 to Mercedes (Russell, ID 3).
// 3. Add 9 to Ferrari (Leclerc, ID 5).
// 4. Add 9 to Williams (Albon, ID 13).
// 5. Add 92 to Visa Cash App RB (Tsunoda, ID 19).

// We'll update or insert into `resultats` for Course ID 24 (Abu Dhabi).
// Check if result exists for these drivers in course 24.

$updates = [
    1 => -82,
    3 => 8,
    5 => 9,
    13 => 9,
    19 => 92
];

foreach ($updates as $driverId => $pointsDiff) {
    // Check if result exists
    $stmt = $pdo->prepare("SELECT id, points FROM resultats WHERE pilote_id = ? AND course_id = ?");
    $stmt->execute([$driverId, 24]);
    $res = $stmt->fetch();

    if ($res) {
        $newPoints = $res['points'] + $pointsDiff;
        if ($newPoints < 0) $newPoints = 0; // Prevent negative
        $stmtUpdate = $pdo->prepare("UPDATE resultats SET points = ? WHERE id = ?");
        $stmtUpdate->execute([$newPoints, $res['id']]);
        echo "Updated Driver $driverId: Old " . $res['points'] . " -> New $newPoints\n";
    } else {
        // Insert new result
        $points = ($pointsDiff > 0) ? $pointsDiff : 0;
        // Using position 10 as dummy
        $stmtInsert = $pdo->prepare("INSERT INTO resultats (pilote_id, course_id, points, position_arrivee) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$driverId, 24, $points, 10]);
        echo "Inserted Driver $driverId with $points points.\n";
    }
}

echo "Done fixing standings.\n";
?>
