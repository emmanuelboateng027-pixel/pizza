<?php
/**
 * API: Get Bed Availability Status
 * Returns real-time bed availability across all hospitals
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once '../config/database.php';

$conn = getDBConnection();

$sql = "SELECT 
            hospital_id,
            name,
            region,
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
        ORDER BY region ASC, name ASC";

$result = $conn->query($sql);

if ($result) {
    $bed_status = array();
    $summary = [
        'total_hospitals' => 0,
        'total_beds' => 0,
        'total_available' => 0,
        'icu_total' => 0,
        'icu_available' => 0,
        'emergency_total' => 0,
        'emergency_available' => 0,
        'hospitals_full' => 0,
        'hospitals_limited' => 0,
        'hospitals_available' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        // Calculate status for each bed type
        $availability_percentage = ($row['available_beds'] / $row['total_beds']) * 100;
        $icu_percentage = $row['icu_beds'] > 0 ? ($row['icu_available'] / $row['icu_beds']) * 100 : 0;
        $emergency_percentage = $row['emergency_beds'] > 0 ? ($row['emergency_available'] / $row['emergency_beds']) * 100 : 0;
        
        // Overall status
        if ($availability_percentage >= 30) {
            $status = 'available';
            $summary['hospitals_available']++;
        } elseif ($availability_percentage >= 10) {
            $status = 'limited';
            $summary['hospitals_limited']++;
        } else {
            $status = 'full';
            $summary['hospitals_full']++;
        }
        
        // ICU status
        if ($icu_percentage >= 30) {
            $icu_status = 'available';
        } elseif ($icu_percentage >= 10) {
            $icu_status = 'limited';
        } else {
            $icu_status = 'full';
        }
        
        // Emergency status
        if ($emergency_percentage >= 30) {
            $emergency_status = 'available';
        } elseif ($emergency_percentage >= 10) {
            $emergency_status = 'limited';
        } else {
            $emergency_status = 'full';
        }
        
        $row['status'] = $status;
        $row['icu_status'] = $icu_status;
        $row['emergency_status'] = $emergency_status;
        $row['availability_percentage'] = round($availability_percentage, 1);
        $row['icu_percentage'] = round($icu_percentage, 1);
        $row['emergency_percentage'] = round($emergency_percentage, 1);
        
        // Update summary
        $summary['total_hospitals']++;
        $summary['total_beds'] += $row['total_beds'];
        $summary['total_available'] += $row['available_beds'];
        $summary['icu_total'] += $row['icu_beds'];
        $summary['icu_available'] += $row['icu_available'];
        $summary['emergency_total'] += $row['emergency_beds'];
        $summary['emergency_available'] += $row['emergency_available'];
        
        $bed_status[] = $row;
    }
    
    sendJSON([
        'success' => true,
        'message' => 'Bed status retrieved successfully',
        'data' => [
            'summary' => $summary,
            'hospitals' => $bed_status,
            'last_updated' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    sendJSON([
        'success' => false,
        'message' => 'Error fetching bed status: ' . $conn->error
    ], 500);
}

$conn->close();
?>
