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
$currentState = 'Maharashtra'; // Hardcoded for this state template

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

    //Employee details
    $employer_name = safe($first_row['employer_name'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');

} catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .notice-text {
            font-size: 11px;
            text-align: justify;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="4" class="title">Form - 'P'<br>NOTICE OF MAXIMUM LEAVE ACCUMULATED<br>The Maharashtra Shops and Establishments (Regulation of Employment and Conditions of Service) Rules, 2018. (See rule 20)</th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name and address of the establishment:</th>
            <td colspan="2" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' .$branch_address)?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name of the Authorised person / Manager:</th>
            <td colspan="2" style="text-align: left;"><?= htmlspecialchars($employer_name)?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">To,</th>
            <td colspan="2" style="text-align: left;">Nil</td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Address:</th>
            <td colspan="2" style="text-align: left;">Nil</td>
        </tr>
        <tr>
            <th colspan="4" class="notice-text">
                It is hereby informed that as per section 18 (5) of the Maharashtra Shops and Establishments (Regulation of Employment and Conditions of Service) Act, 2017 (Mah.LXI of 2017) the maximum leave that can be accumulated is for 45 days. Maximum leave of 45 days has been accumulated at your credit. Hence, no further leave due to you, but not availed by you will not be accumulated and it shall lapse, if unavailed.
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">Details of the leave accumulated.</th>
        </tr>
        <tr>
            <th rowspan="2">Sr. No.</th>
            <th rowspan="2">Number of accumulated leave</th>
            <th colspan="2" style="text-align: center;">Period for which leave is accumulated</th>
        </tr>
        <tr>
            <th>From</th>
            <th>Till</th>
        </tr>
        <tr>
            <td>1</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: right;"><br><br>Name and Signature of Authorised Person/Manager</th>
        </tr>
    </table>
</body>
</html>