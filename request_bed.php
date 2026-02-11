<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON([
        'success' => false,
        'message' => 'Method not allowed. POST request required.'
    ], 405);
}

$hospital_id = isset($_POST['hospital_id']) ? intval($_POST['hospital_id']) : 0;
$patient_name = isset($_POST['patient_name']) ? sanitize_input($_POST['patient_name']) : '';
$patient_email = isset($_POST['patient_email']) ? sanitize_input($_POST['patient_email']) : '';
$patient_phone = isset($_POST['patient_phone']) ? sanitize_input($_POST['patient_phone']) : '';
$bed_type = $_POST['bed_type'] ?? '';
$urgency_level = $_POST['urgency_level'] ?? '';
$reason = isset($_POST['reason']) ? sanitize_input($_POST['reason']) : '';

if (empty($hospital_id) || empty($patient_name) || empty($patient_email) || empty($patient_phone) || empty($bed_type) || empty($urgency_level)) {
    sendJSON([
        'success' => false,
        'message' => 'All required fields must be filled'
    ], 400);
}

if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
    sendJSON([
        'success' => false,
        'message' => 'Invalid email address'
    ], 400);
}

if (!in_array($bed_type, ['general', 'icu', 'emergency'])) {
    sendJSON([
        'success' => false,
        'message' => 'Invalid bed type'
    ], 400);
}

if (!in_array($urgency_level, ['low', 'medium', 'high', 'critical'])) {
    sendJSON([
        'success' => false,
        'message' => 'Invalid urgency level'
    ], 400);
}

// Check if hospital exists
$sql = "SELECT hospital_id FROM hospitals WHERE hospital_id = ?";
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

// Insert bed request
$sql = "INSERT INTO bed_requests (hospital_id, patient_name, patient_email, patient_phone, bed_type, urgency_level, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssss", $hospital_id, $patient_name, $patient_email, $patient_phone, $bed_type, $urgency_level, $reason);

if ($stmt->execute()) {
    $request_id = $conn->insert_id;
    sendJSON([
        'success' => true,
        'message' => 'Bed request submitted successfully. Hospital staff will contact you soon.',
        'data' => [
            'request_id' => $request_id
        ]
    ], 201);
} else {
    sendJSON([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}

$conn->close();