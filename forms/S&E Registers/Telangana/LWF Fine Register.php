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
$currentState = 'Telangana'; // Hardcoded for this state template

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
        * {
            font-family: "Times New Roman", Times, serif;
        }
        body {
            margin: 0;
            padding: 10px;
            font-size: 12px;font-family: 'Times New Roman', Times, serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;font-family: 'Times New Roman', Times, serif;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
            vertical-align: top;
            word-wrap: break-word;font-family: 'Times New Roman', Times, serif;
        }
        th {
            background-color: #ffffffff;
            font-weight: bold;
            text-align: center;font-family: 'Times New Roman', Times, serif;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;font-family: 'Times New Roman', Times, serif;
        }
        .label-cell {
            font-weight: bold;font-family: 'Times New Roman', Times, serif;
            text-align: center;
        }
        .indent {
            padding-left: 20px;font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>

<table>
    <colgroup>
        <col style="width: 10%">
        <col style="width: 30%">
        <col style="width: 15%">
        <col style="width: 15%">
        <col style="width: 15%">
        <col style="width: 15%">
    </colgroup>
    <tbody>
        <!-- Form Title -->
        <tr>
            <td colspan="6" class="form-title">
            FORM-E <br>
            A.P. Labour Welfare Fund Act, 1987 & Rules, 1988 [See sub-rule (2) of Rule 4] <br>
            Register of Fines and unpaid Accumulation for the year 2024					
            </td>
        </tr>

        <!-- Name and Address Row -->
        <tr>
            <td colspan="2" class="label-cell" style="text-align: left;">Name and address of the establishment</td>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
         <tr>
            <td colspan="2" class="label-cell" style="text-align: left;">Wage Period</td>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Main Table Headers -->
        <tr>
            <th>S. No</th>
            <th>Details of fine and unpaid accumulations</th>
            <th>Quarter Ending<br>31st March</th>
            <th>Quarter Ending<br>30th June</th>
            <th>Quarter Ending<br>30th Sept</th>
            <th>Quarter Ending<br>31st Dec</th>
        </tr>
        <tr>
            <th>(1)</th>
            <th>(2)</th>
            <th>(3)</th>
            <th>(4)</th>
            <th>(5)</th>
            <th>(6)</th>
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
            <th>(i)</th>
            <th class="indent">Basic wages</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>(ii)</th>
            <th class="indent">Overtime</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>(iii)</th>
            <th class="indent">Dearness Allowance</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>(iv)</th>
            <th class="indent">Dearness Allowance</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>(v)</th>
            <th class="indent">Gratuity, and</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <th>(vi)</th>
            <th class="indent">Any other item</th>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
        <tr>
            <td colspan="2" class="label-cell">Total of 1 and 2</td>
            <td>Nil</td><td>Nil</td><td>Nil</td><td>Nil</td>
        </tr>
    </tbody>
</table>


</body>
</html>
