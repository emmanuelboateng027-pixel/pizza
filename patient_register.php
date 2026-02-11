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
    $full_name = isset($_POST['full_name']) ? sanitize_input($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        sendJSON([
            'success' => false,
            'message' => 'All fields are required'
        ], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJSON([
            'success' => false,
            'message' => 'Invalid email format'
        ], 400);
    }

    if (strlen($password) < 6) {
        sendJSON([
            'success' => false,
            'message' => 'Password must be at least 6 characters long'
        ], 400);
    }

    if ($password !== $confirm_password) {
        sendJSON([
            'success' => false,
            'message' => 'Passwords do not match'
        ], 400);
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        sendJSON([
            'success' => false,
            'message' => 'Email already registered'
        ], 400);
    }
    $stmt->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert patient
    $stmt = $conn->prepare("INSERT INTO patients (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $phone, $password_hash);
    
    if ($stmt->execute()) {
        sendJSON([
            'success' => true,
            'message' => 'Account created successfully! You can now sign in.',
            'data' => [
                'patient_id' => $conn->insert_id
            ]
        ], 201);
    } else {
        sendJSON([
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ], 500);
    }
    
    $stmt->close();

} catch (Exception $e) {
    error_log('Patient registration error: ' . $e->getMessage());
    sendJSON([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ], 500);
}

$conn->close();