<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config/database.php';

session_start();
session_destroy();

sendJSON([
    'success' => true,
    'message' => 'Logged out successfully',
    'data' => []
], 200);
?>