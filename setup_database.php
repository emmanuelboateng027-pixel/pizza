<?php
/**
 * Database Setup Script
 * Run this to update the database with new hospital data and images
 */

require_once 'config/database.php';

$conn = getDBConnection();

// Read and execute the SQL file
$sql = file_get_contents('database/database_schema.sql');

if ($sql === false) {
    die("Error reading SQL file\n");
}

// Split the SQL file into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;

    if ($conn->query($statement) === TRUE) {
        $successCount++;
    } else {
        echo "Error executing statement: " . $conn->error . "\n";
        $errorCount++;
    }
}

echo "Database update complete!\n";
echo "Successful statements: $successCount\n";
echo "Errors: $errorCount\n";

$conn->close();
?>