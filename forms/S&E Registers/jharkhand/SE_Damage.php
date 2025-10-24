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
$currentState = 'Jharkhand'; // Hardcoded for this state template

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
$employer_name = $first_row['employer_name'] ?? '';

} catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .empty-row {
            height: 25px;
        }
        .left-align {
            text-align: left;
        }
        .colspan-full {
            width: 100%;
        }
        .signature-cell {
            height: 30px;
            vertical-align: bottom;
        }
        .sub-header {
            font-weight: normal;
        }
        
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="10">
                    FORM XI<br>
                    The Jharkhand Shops and Establishments Rules, 2001 [Rule 19]<br>
                    Register of Fines and Deduction
                </th>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="5">Name and Address of the Establishment</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name .' , '. $branch_address)?></td>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="5">Name of the Employer</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($employer_name)?></td>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="5">Month & Year</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
            </tr>
            <tr>
                <th>S. No</th>
                <th>Employee code</th>
                <th>Name of Employee</th>
                <th>Gender</th>
                <th>Nature and date of the<br>
                    offence/damage or loss<br>
                    for which the fine is <br>
                    imposed/ deduction is made</th>
                <th>Whether worker showed cause<br>
                    against fine /deduction <br>
                    if so enter date</th>
                <th>Rate of wages</th>
                <th>Date and amount of <br>
                    fine /Deduction imposed</th>
                <th>Date/s on which fine/deduction<br>
                    imposed is realised</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>