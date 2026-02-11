<?php
/**
 * API: Get Hero Images
 * Returns images for homepage marquee slider
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once '../config/database.php';

$conn = getDBConnection();

$sql = "SELECT 
            image_id,
            image_url,
            caption,
            display_order
        FROM images
        WHERE type = 'hero' AND is_active = 1
        ORDER BY display_order ASC";

$result = $conn->query($sql);

if ($result) {
    $images = $result->fetch_all(MYSQLI_ASSOC);
    
    sendJSON([
        'success' => true,
        'message' => 'Hero images retrieved successfully',
        'data' => [
            'count' => count($images),
            'images' => $images
        ]
    ]);
} else {
    sendJSON([
        'success' => false,
        'message' => 'Error fetching images: ' . $conn->error
    ], 500);
}

$conn->close();
?>
