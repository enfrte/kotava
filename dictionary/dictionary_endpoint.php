<?php 

header('Content-Type: application/json');

// Get search parameters
$searchEnglish = isset($_GET['searchEnglish']) ? trim($_GET['searchEnglish']) : '';
$searchKotava = isset($_GET['searchKotava']) ? trim($_GET['searchKotava']) : '';

if (strlen($searchEnglish) === 0 && strlen($searchKotava) === 0) {
    echo json_encode([]);
    exit;
}

// Query SQLite kotava_dictionary_production.db
$db = new PDO('sqlite:kotava_dictionary_production.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Prepare SQL query
$sql = "SELECT * FROM dictionary WHERE 1=1";
$params = [];

// Add search conditions
if ($searchEnglish) {
    if (strlen($searchEnglish) < 3) {
        $sql .= " AND english = :searchEnglish";
        $params[':searchEnglish'] = $searchEnglish;
    } else {
        $sql .= " AND english LIKE :searchEnglish";
        $params[':searchEnglish'] = "%$searchEnglish%";
    }
}
if ($searchKotava) {
    if (strlen($searchKotava) < 3) {
        $sql .= " AND kotava = :searchKotava";
        $params[':searchKotava'] = $searchKotava;
    } else {
        $sql .= " AND kotava LIKE :searchKotava";
        $params[':searchKotava'] = "%$searchKotava%";
    }
}

// Execute query
$stmt = $db->prepare($sql);
$stmt->execute($params);
$filteredEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return filtered entries as JSON
echo json_encode(array_values($filteredEntries));
