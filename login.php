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

$username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    sendJSON([
        'success' => false,
        'message' => 'Username and password are required'
    ], 400);
}

// Get user from database
$sql = "SELECT user_id, hospital_id, username, password_hash, email, role, is_active FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendJSON([
        'success' => false,
        'message' => 'Invalid username or password'
    ], 401);
}

$user = $result->fetch_assoc();

if (!$user['is_active']) {
    sendJSON([
        'success' => false,
        'message' => 'Account is deactivated'
    ], 401);
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    sendJSON([
        'success' => false,
        'message' => 'Invalid username or password'
    ], 401);
}

// Update last login
$sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();

// Start session
session_start();
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['hospital_id'] = $user['hospital_id'];
sendJSON([
    'success' => true,
    'message' => 'Login successful',
    'data' => [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'hospital_id' => $user['hospital_id']
    ]
], 200);

$conn->close();
?>