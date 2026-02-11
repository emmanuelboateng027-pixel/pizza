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

try {
    // Get POST data
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        sendJSON([
            'success' => false,
            'message' => 'Email and password are required'
        ], 400);
    }

    // Get patient
    $stmt = $conn->prepare("SELECT patient_id, full_name, password_hash FROM patients WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $stmt->close();

    if (!$patient || !password_verify($password, $patient['password_hash'])) {
        sendJSON([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }

    // Update last login
    $stmt = $conn->prepare("UPDATE patients SET last_login = NOW() WHERE patient_id = ?");
    $stmt->bind_param("i", $patient['patient_id']);
    $stmt->execute();
    $stmt->close();

    // Start session
    session_start();
    $_SESSION['patient_id'] = $patient['patient_id'];
    $_SESSION['patient_name'] = $patient['full_name'];
    $_SESSION['patient_email'] = $email;

    sendJSON([
        'success' => true,
        'message' => 'Login successful!',
        'data' => [
            'patient_id' => $patient['patient_id'],
            'patient_name' => $patient['full_name'],
            'patient_email' => $email
        ]
    ], 200);

} catch (Exception $e) {
    error_log('Patient login error: ' . $e->getMessage());
    sendJSON([
        'success' => false,
        'message' => 'Login failed. Please try again.'
    ], 500);
}

$conn->close();