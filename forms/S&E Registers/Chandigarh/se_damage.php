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
            margin: 20px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            word-wrap: break-word;
            text-align: center;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
        .signature-cell {
            height: 30px;
            vertical-align: bottom;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <th colspan="16" class="form-header">
            Form-E <br>
            Register Of Deductions <br>
            The Punjab Shops And Commercial Establishments Rules, 1958 (Rule 5)
        </th>
    </tr>
    <tr>
        <th colspan="5" style="text-align: left;">Name of the establishment</th>
        <td colspan="11" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
    </tr>
    <tr>
        <th colspan="5" style="text-align: left;">Month & Year</th>
        <td colspan="11" style="text-align: left;"><?= htmlspecialchars($month . ' , ' . $year) ?></td>
    </tr>
    <tr>
        <th colspan="5" style="text-align: left;">Acts and omission approved by the authorities</th>
        <td colspan="11"> - </td>
    </tr>

    <tr>
        <th style="width: 4%;">S. No</th>
        <th style="width: 7%;">Employee code</th>
        <th style="width: 10%;">Name of Employee</th>
        <th style="width: 10%;">Parentage</th>
        <th style="width: 6%;">Wage period</th>
        <th style="width: 7%;">Wages payable</th>
        <th style="width: 6%;">Amount deducted</th>
        <th style="width: 10%;">Fault for which deductions made</th>
        <th style="width: 6%;">Date of deduction</th>
        <th style="width: 10%;">Whether employee showed cause</th>
        <th style="width: 10%;">Deductions and purpose utilised</th>
        <th style="width: 6%;">Date of utilization</th>
        <th style="width: 6%;">Balance with employer</th>
        <th style="width: 6%;">Signature of employee</th>
        <th style="width: 6%;">Signature of employer</th>
        <th style="width: 6%;">Remarks</th>
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
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td class="signature-cell"></td>
            <td class="signature-cell"></td>
            <td></td>
        </tr>
</table>

</body>
</html>
