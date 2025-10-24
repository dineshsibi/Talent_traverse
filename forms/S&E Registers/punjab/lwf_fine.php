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
$currentState = 'Punjab'; // Hardcoded for this state template

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

$branch_address = $first_row['branch_address'] ?? '';

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
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
            vertical-align: top;
            word-wrap: break-word;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
        .label-cell {
            font-weight: bold;
        }
        .indent {
            padding-left: 20px;
        }
    </style>
</head>
<body>

<table>
    <tbody>
        <!-- Form Title -->
        <tr>
            <th colspan="6" class="form-title">
                Form B<br>
                The Punjab Labour Welfare Fund Act, 1965 & Rules, 1966 (See Rule 22)<br>
                Register Of Fines Realised And Unpaid Accumulation<br>
            </th>
        </tr>

        <!-- Name and Address Row -->
        <tr>
            <th colspan="2" style="text-align: left;">Name and address of the establishment</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Wage Period</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Main Table Headers -->
        <tr>
            <th>S. No</th>
            <th>Particular</th>
            <th>Quarter Ending<br>31st March</th>
            <th>Quarter Ending<br>30th June</th>
            <th>Quarter Ending<br>30th Sept</th>
            <th>Quarter Ending<br>31st Dec</th>
        </tr>

        <!-- Data Rows -->
        <tr>
            <th>1</th>
            <th>Total realisation under fines</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>2</th>
            <th>Total amount becoming unpaid accumulation of</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>3 (I)</th>
            <th class="indent">Basic wages</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>(II)</th>
            <th class="indent">Overtime</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>(III)</th>
            <th class="indent">Dearness allowance and other allowance</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th colspan="2">Total of (1)(2)</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
    </tbody>
</table>
</body>
</html>
