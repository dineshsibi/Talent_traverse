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
$currentState = 'West Bengal'; // Hardcoded for this state template

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


    // Employer data with array handling
    $nature=safe($first_row['nature_of_business'] ?? '');
    $employer_name = safe($first_row['employer_name'] ?? '');
    $employer_address = safe($first_row['employer_address'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');

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
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffffff;
        }
        .empty-row {
            height: 30px;
        }
        .label{
            font-weight:bold;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr class="form-header">
                <th style="text-align: center;" colspan="11">
                    Form A <br>
                    The West Bengal Child Labour (Prohibition and Regulation) Rules, 1995 [See Rule 10 (1)] <br>
                    Child Labour Register										
				</th>
            </tr>
            <tr>
                <td colspan="5" class="label" style="text-align: left;">Name and address of Establishment</td>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' .$branch_address)?></td>
            </tr>
            <tr>
                <td colspan="5" class="label" style="text-align: left;">Name and address of Employer</td>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' .$employer_address)?></td>
            </tr>
            <tr>
                <td colspan="5" class="label" style="text-align: left;">Nature of work done by the Establishment</td>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($nature)?></td>
            </tr>
            <tr>
                <td colspan="5" class="label" style="text-align: left;">Month & Year</td>
                 <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' .$year)?></td>
            </tr>
        </thead>  
        
            <tr>
                <th>Sl. No.</th>
                <th>Name of child</th>
                <th>Father's name</th>
                <th>Date of birth</th>
                <th>Permanent address</th>
                <th>Date of joining in the establishment</th>
                <th>Nature of work employed</th>
                <th>Daily hours of work</th>
                <th>Intervals of rest</th>
                <th>Wages paid</th>
                <th>Remarks</th>
            </tr>
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
                <td>NIL</td>
                <td></td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="11"> Signature of Employer / Manager / Contractor / Authorised Person </th>
            </tr>    
        </tbody>
    </table>
</body>
</html>