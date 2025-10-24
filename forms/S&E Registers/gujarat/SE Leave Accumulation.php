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
$currentState = 'Gujarat'; // Hardcoded for this state template

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
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .act-reference {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .notice-title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin: 15px 0;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .info-row {
            font-weight: bold;
        }
        .address {
            margin: 10px 0;
            padding-left: 20px;
        }
        .notice-text {
            margin: 15px 0;
            text-align: justify;
        }
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid black;
            margin-top: 50px;
            width: 100%;
        }
        .empty-row {
            height: 20px;
        }
        *{
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
    <table>
        <thead>
           <tr>
                <th class="form-header" colspan="5">
                    Form - O (See Rule 18)<br>
                    The Gujarat Shops and Establishments (Regulation of Employment and Conditions of Service)<br>
                    Act, 2019 and Rules, 2020
                </th>
            </tr>
            <tr>         
                <th class="form-header" colspan="5">NOTICE OF MAXIMUM LEAVE ACCUMULATED</th>
            </tr>
            <tr>  
                <th style="text-align: left;" colspan="2">Name and address of the establishment. Name of the Authorized person / Manager</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars ($client_name . ' , ' . $branch_address )?></td>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="2">Wage Period</th>
                <td style="text-align: left;" colspan="3"><?= htmlspecialchars($month .' - '.$year)?></td>
            </tr>
            <tr>
                <th class="form-header" colspan="5">Notice</th>
            </tr>  
            <tr>
                <th class="form-header" colspan="5">As per section 18 (5) of the Gujarat Shops and Establishments (Regulation of Employment and Conditions of Service) Act, 2019 the maximum leave that can be accumulated is for 45 days. The following workers whose names are mentioned below have maximum leave of 45 days accumulated at their credit. Hence, no further leave due to them but not availed by them will be accumulated and it shall lapse if unveiled</th>
            </tr>
            <tr>    
                <th  class="form-header" colspan="5">Details of workers</th>
            <tr>
                <th rowspan="2">Sr.No.</th>
                <th rowspan="2">Name of workers</th>
                <th rowspan="2">Number of Accumulated leave</th>
                <th colspan="2">Period for which leave Is accumulated</th>
            </tr>
            <tr>
                <th>From</th>
                <th>Till</th>
            </tr>
        </thead>        
        <tbody>
            <tr>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
            </tr>
            <tr>  
                <th>Date</th>
                <td></td>
                <th>Copy to Workers</th>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="5">Name and Signature of Authorized representative /Manager</th>
            </tr>
        </tbody>
    </table>    
</body>
</html>