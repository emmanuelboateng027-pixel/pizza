<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config/database.php';

session_start();

$response = [
    'logged_in' => false,
    'patient_name' => null,
    'patient_email' => null
];

if (isset($_SESSION['patient_id'])) {
    $response['logged_in'] = true;
    $response['patient_name'] = $_SESSION['patient_name'] ?? null;
    $response['patient_email'] = $_SESSION['patient_email'] ?? null;
}

sendJSON([
    'success' => true,
    'data' => $response
], 200);
?>