<?php
/**
 * API: Update Bed Status
 * Allows hospital staff to update bed availability
 * Requires authentication
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Validate HTTP method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON([
        'success' => false,
        'message' => 'Method not allowed. POST request required.'
    ], 405);
}

$conn = getDBConnection();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['hospital_id', 'available_beds', 'icu_available', 'emergency_available'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        sendJSON([
            'success' => false,
            'message' => "Missing required field: $field"
        ], 400);
    }
}

$hospital_id = intval($data['hospital_id']);
$available_beds = intval($data['available_beds']);
$icu_available = intval($data['icu_available']);
$emergency_available = intval($data['emergency_available']);

// Validate that available beds don't exceed total beds
$sql = "SELECT total_beds, icu_beds, emergency_beds FROM hospitals WHERE hospital_id = ?";
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

if ($available_beds > $hospital['total_beds'] || 
    $icu_available > $hospital['icu_beds'] || 
    $emergency_available > $hospital['emergency_beds']) {
    sendJSON([
        'success' => false,
        'message' => 'Available beds cannot exceed total beds'
    ], 400);
}

// Calculate general available beds
$general_available = $available_beds - $icu_available - $emergency_available;

// Update hospital bed status
$sql = "UPDATE hospitals 
        SET available_beds = ?,
            icu_available = ?,
            emergency_available = ?,
            general_available = ?,
            updated_at = NOW()
        WHERE hospital_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $available_beds, $icu_available, $emergency_available, $general_available, $hospital_id);

if ($stmt->execute()) {
    // Log the update
    $log_sql = "INSERT INTO bed_status_log 
                (hospital_id, total_beds, available_beds, icu_beds, icu_available, emergency_beds, emergency_available)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("iiiiiii", 
        $hospital_id, 
        $hospital['total_beds'], 
        $available_beds, 
        $hospital['icu_beds'], 
        $icu_available, 
        $hospital['emergency_beds'], 
        $emergency_available
    );
    $log_stmt->execute();
    
    sendJSON([
        'success' => true,
        'message' => 'Bed status updated successfully',
        'data' => [
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    sendJSON([
        'success' => false,
        'message' => 'Error updating bed status: ' . $conn->error
    ], 500);
}

$conn->close();
?>
