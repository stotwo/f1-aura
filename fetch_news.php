<?php
require_once 'config.php';

set_time_limit(300);

echo "<h1>Actualisation des News F1</h1>";

$keywords = [];

$stmt = $pdo->query("SELECT id, nom, prenom FROM pilotes");
while ($row = $stmt->fetch()) {
    $keywords['pilotes'][strtolower($row['nom'])] = $row['id'];
}

$stmt = $pdo->query("SELECT id, nom FROM ecuries");
while ($row = $stmt->fetch()) {
    $simpleName = strtolower($row['nom']);
    $keywords['ecuries'][$simpleName] = $row['id'];
    
    if (strpos($simpleName, 'ferrari') !== false) $keywords['ecuries']['ferrari'] = $row['id'];
    if (strpos($simpleName, 'red bull') !== false) $keywords['ecuries']['red bull'] = $row['id'];
    if (strpos($simpleName, 'mercedes') !== false) $keywords['ecuries']['mercedes'] = $row['id'];
    if (strpos($simpleName, 'mclaren') !== false) $keywords['ecuries']['mclaren'] = $row['id'];
    if (strpos($simpleName, 'aston martin') !== false) $keywords['ecuries']['aston martin'] = $row['id'];
    if (strpos($simpleName, 'alpine') !== false) $keywords['ecuries']['alpine'] = $row['id'];
    if (strpos($simpleName, 'williams') !== false) $keywords['ecuries']['williams'] = $row['id'];
    if (strpos($simpleName, 'haas') !== false) $keywords['ecuries']['haas'] = $row['id'];
    if (strpos($simpleName, 'sauber') !== false) $keywords['ecuries']['sauber'] = $row['id'];
    if (strpos($simpleName, 'rb') !== false || strpos($simpleName, 'visa') !== false) {
        $keywords['ecuries']['rb'] = $row['id'];
        $keywords['ecuries']['visa cash app'] = $row['id'];
    }
}

$rss_feeds = [
    'Motorsport FR' => 'https://fr.motorsport.com/rss/f1/news/',
    'F1.com' => 'https://www.formula1.com/content/fom-website/en/latest/all.xml'
];

$articles_adds = 0;

function tagArticle($pdo, $article_id, $text_to_scan, $keywords) {
    foreach ($keywords['pilotes'] as $name => $id) {
        if (preg_match('/\b' . preg_quote($name, '/') . '\b/i', $text_to_scan)) {
            $pdo->prepare("INSERT IGNORE INTO article_tags_pilotes (article_id, pilote_id) VALUES (?, ?)")->execute([$article_id, $id]);
        }
    }

    foreach ($keywords['ecuries'] as $name => $id) {
        if (preg_match('/\b' . preg_quote($name, '/') . '\b/i', $text_to_scan)) {
            $pdo->prepare("INSERT IGNORE INTO article_tags_ecuries (article_id, ecurie_id) VALUES (?, ?)")->execute([$article_id, $id]);
        }
    }
}

$pdo->exec("TRUNCATE TABLE article_tags_pilotes");
$pdo->exec("TRUNCATE TABLE article_tags_ecuries");
echo "🧹 Tags nettoyés pour re-scan complet.<br>";

$existing_articles = $pdo->query("SELECT id, titre, description FROM articles")->fetchAll();
foreach ($existing_articles as $art) {
    tagArticle($pdo, $art['id'], $art['titre'], $keywords);
}
echo "🔄 Articles existants re-taggés (Titre uniquement).<br>";

foreach ($rss_feeds as $source_name => $url) {
    echo "<h3>Lecture de $source_name...</h3>";
    
    $rss = @simplexml_load_file($url);
    if (!$rss) {
        echo "❌ Impossible de lire le flux : $url<br>";
        continue;
    }

    foreach ($rss->channel->item as $item) {
        $titre = (string)$item->title;
        $description = (string)$item->description;
        $link = (string)$item->link;
        $pubDate = date('Y-m-d H:i:s', strtotime((string)$item->pubDate));
        
        $image_url = '';
        if (isset($item->enclosure)) {
            $image_url = (string)$item->enclosure['url'];
        }

        $check = $pdo->prepare("SELECT id FROM articles WHERE lien = ?");
        $check->execute([$link]);
        $existing = $check->fetch();

        if ($existing) {
            continue; 
        }

        $ins = $pdo->prepare("INSERT INTO articles (titre, description, lien, image_url, source, date_publication) VALUES (?, ?, ?, ?, ?, ?)");
        if ($ins->execute([$titre, $description, $link, $image_url, $source_name, $pubDate])) {
            $article_id = $pdo->lastInsertId();
            $articles_adds++;
            echo "✅ Ajout : $titre<br>";
            tagArticle($pdo, $article_id, $titre, $keywords);
        }
    }
}

echo "<br><strong>Terminé ! $articles_adds nouveaux articles ajoutés.</strong>";
echo '<br><a href="dashboard.php">Retour au Dashboard</a>';
?>
