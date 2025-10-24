<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__.'/../../../includes/config.php';
if (!file_exists($configPath)) {
    die("Database configuration not found");
}
$pdo = require($configPath); // This gets the PDO connection from config.php

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Chandigarh'; // Hardcoded for this state template

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM input 
            WHERE client_name = :client_name
            AND state LIKE :state
            AND location_code = :location_code";
    
    // Add month/year filter if specified
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }
    
    // Prepare and execute query
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindValue(':client_name', $filters['client_name']);
    $stmt->bindValue(':state', "%$currentState%");
    $stmt->bindValue(':location_code', $currentLocation);
    
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $stmt->bindValue(':month', $filters['month']);
        $stmt->bindValue(':year', $filters['year']);
    }
    
    $stmt->execute();
    $stateData = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $first_row = !empty($stateData) ? reset($stateData) : [];

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');

$branch_address = safe($first_row['branch_address'] ?? '');
$location_code = $first_row['location_code'] ?? '';

} catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 10px;
            font-size: 12px;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
            overflow: hidden;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .label-cell {
            font-weight: bold;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
        .nill-row td {
            text-align: center;
        }
    </style>
</head>
<body>

<table>
    <thead>
        <tr>
            <th class="form-header" colspan="11">
                Form II A <br>
                The Punjab Minimum Wages Rules, 1950 [See Rule 17] <br>
                Register of advances made to employed person
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <th colspan="3" style="text-align: left;">Name and Address of the Factory/ Establishment</th>
            <td colspan="8" style="text-align: left;"><?= $client_name ?><?= $branch_address ? ' , ' . $branch_address : '' ?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">Month & Year</th>
            <td colspan="8" style="text-align: left;"><?= $month ?><?= $year ? ' - ' . $year : '' ?></td>
        </tr>

        <!-- Table Headers -->
        <tr>
            <th>Sl. No.</th>
            <th>Employee Code</th>
            <th>Name</th>
            <th>Father's Name</th>
            <th>Department</th>
            <th>Date and amount of advance made</th>
            <th>Purpose(s) for which advance made</th>
            <th>No. of instalments</th>
            <th>Postponements granted</th>
            <th>Instalments repaid</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <td>1</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td></td>
        </tr>
    </tbody>
</table>

</body>
</html>
