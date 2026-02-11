<?php
/**
 * Database Configuration
 * No Bed Syndrome - Hospital Bed Availability System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change this to your database username
define('DB_PASS', '');      // Change this to your database password
define('DB_NAME', 'no_bed_syndrome');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Security function: Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Function to send JSON response
function sendJSON($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper function: Calculate bed availability status
function get_bed_status($available, $total) {
    if ($total <= 0) return 'unknown';
    
    $percentage = ($available / $total) * 100;
    
    if ($percentage >= 30) {
        return 'available';
    } elseif ($percentage >= 10) {
        return 'limited';
    } else {
        return 'full';
    }
}

// Helper function: Calculate bed availability percentage
function get_availability_percentage($available, $total) {
    if ($total <= 0) return 0;
    return round(($available / $total) * 100, 1);
}
?>
