<?php
/**
 * API: Get Hospital Details
 * Returns complete information for a specific hospital
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once '../config/database.php';

$conn = getDBConnection();

// Get hospital ID from query parameter
$hospital_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hospital_id <= 0) {
    sendJSON([
        'success' => false,
        'message' => 'Invalid hospital ID'
    ], 400);
}

// Get hospital basic info
$sql = "SELECT * FROM hospitals WHERE hospital_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendJSON([
        'success' => false,
        'message' => 'Hospital not found'
    ], 404);
}

$hospital = $result->fetch_assoc();

// Calculate status
$availability_percentage = ($hospital['available_beds'] / $hospital['total_beds']) * 100;
if ($availability_percentage >= 30) {
    $hospital['status'] = 'available';
} elseif ($availability_percentage >= 10) {
    $hospital['status'] = 'limited';
} else {
    $hospital['status'] = 'full';
}
$hospital['availability_percentage'] = round($availability_percentage, 1);

// Get doctors
$sql = "SELECT * FROM doctors WHERE hospital_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$hospital['doctors'] = $result->fetch_all(MYSQLI_ASSOC);

// Get departments
$sql = "SELECT * FROM departments WHERE hospital_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$hospital['departments'] = $result->fetch_all(MYSQLI_ASSOC);

// Get services
$sql = "SELECT * FROM services WHERE hospital_id = ? ORDER BY service_name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$hospital['services'] = $result->fetch_all(MYSQLI_ASSOC);

// Get images
$sql = "SELECT * FROM images WHERE hospital_id = ? AND is_active = 1 ORDER BY display_order ASC, type ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$hospital['images'] = $result->fetch_all(MYSQLI_ASSOC);

sendJSON([
    'success' => true,
    'message' => 'Hospital details retrieved successfully',
    'data' => $hospital
]);

$conn->close();
?>
