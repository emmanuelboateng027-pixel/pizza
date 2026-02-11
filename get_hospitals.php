<?php
/**
 * API: Get All Hospitals
 * Returns list of all hospitals with basic information
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once '../config/database.php';

$conn = getDBConnection();

$sql = "SELECT 
            hospital_id,
            name,
            address,
            region,
            phone,
            email,
            logo_url,
            total_beds,
            available_beds,
            icu_beds,
            icu_available,
            emergency_beds,
            emergency_available,
            general_beds,
            general_available,
            updated_at
        FROM hospitals
        ORDER BY name ASC";

$result = $conn->query($sql);

if ($result) {
    $hospitals = array();
    
    while ($row = $result->fetch_assoc()) {
        // Calculate status
        $availability_percentage = ($row['available_beds'] / $row['total_beds']) * 100;
        
        if ($availability_percentage >= 30) {
            $status = 'available';
        } elseif ($availability_percentage >= 10) {
            $status = 'limited';
        } else {
            $status = 'full';
        }
        
        $row['status'] = $status;
        $row['availability_percentage'] = round($availability_percentage, 1);
        
        $hospitals[] = $row;
    }
    
    sendJSON([
        'success' => true,
        'message' => 'Hospitals retrieved successfully',
        'data' => [
            'count' => count($hospitals),
            'hospitals' => $hospitals
        ]
    ]);
} else {
    sendJSON([
        'success' => false,
        'message' => 'Error fetching hospitals: ' . $conn->error
    ], 500);
}

$conn->close();
?>
