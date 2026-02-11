<?php
require_once 'backend/config/database.php';

$conn = getDBConnection();
$result = $conn->query('SELECT COUNT(*) as count FROM hospitals');
$row = $result->fetch_assoc();
echo 'Hospitals in database: ' . $row['count'] . PHP_EOL;

// Check if hospitals have logo_url
$result = $conn->query('SELECT name, logo_url FROM hospitals WHERE logo_url IS NOT NULL LIMIT 3');
echo 'Sample hospitals with logos:' . PHP_EOL;
while ($row = $result->fetch_assoc()) {
    echo '- ' . $row['name'] . ': ' . (strlen($row['logo_url']) > 50 ? substr($row['logo_url'], 0, 50) . '...' : $row['logo_url']) . PHP_EOL;
}

$conn->close();
?>