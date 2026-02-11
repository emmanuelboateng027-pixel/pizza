<?php
require_once 'backend/config/database.php';

$conn = getDBConnection();

// Check for Unsplash URLs
$result = $conn->query('SELECT name, logo_url FROM hospitals WHERE logo_url LIKE "https://%" LIMIT 3');
echo 'Hospitals with Unsplash logos:' . PHP_EOL;
while ($row = $result->fetch_assoc()) {
    echo '- ' . $row['name'] . ': ' . substr($row['logo_url'], 0, 60) . '...' . PHP_EOL;
}

// Check images count
$result = $conn->query('SELECT COUNT(*) as count FROM images');
$row = $result->fetch_assoc();
echo 'Images in database: ' . $row['count'] . PHP_EOL;

$conn->close();
?>