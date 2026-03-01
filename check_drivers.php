<?php
require_once 'config.php';

$stmt = $pdo->query("SELECT p.id, p.nom, p.prenom, p.ecurie_id, e.nom as ecurie_nom FROM pilotes p JOIN ecuries e ON p.ecurie_id = e.id ORDER BY e.id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Driver: " . $row['nom'] . " (ID " . $row['id'] . ") -> Ecurie: " . $row['ecurie_nom'] . " (ID " . $row['ecurie_id'] . ")\n";
}
?>
