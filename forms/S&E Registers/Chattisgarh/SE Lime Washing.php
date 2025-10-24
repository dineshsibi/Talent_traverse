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
$currentState = 'Chattisgarh'; // Hardcoded for this state template

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


    // Get one sample row to extract address
    $branch_address = $first_row['branch_address'] ?? '';

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
            margin: 0;
            padding: 20px;
        }
        .form-container {
            width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 12px;
            word-wrap: break-word;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .header-info {
            text-align: left;
            padding-left: 10px;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left top 0;
            white-space: nowrap;
            display: block;
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 100px;
            height: 20px;
        }
        .vertical-header {
            position: relative;
            height: 120px;
        }
        .vertical-header span {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%) rotate(-90deg);
            transform-origin: center;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="6" class="title">
                Form L<br>
                The Chattisgarh Shops And Establishment Act, 1958 and Rules, 1959 [See Rule 15 (1)]<br>
                Register Showing Dates of Lime-Washing, etc.
            </th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">Name of the Establishment and Address </th>
            <td colspan="3"><?= htmlspecialchars($client_name . ' , '. ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">Month/Year </th>
            <td colspan="3"><?= htmlspecialchars($month . ' - ' . $year)?></td>
        </tr>
        <tr>
            <th>Parts of the establishment i.e. name of room</th>
            <th>Parts lime-washed, colour-washed, painted or varnished e.g. wall, ceilings, wood-work etc.</th>
            <th>Treatment (whether lime-washed, colour washed, painted or varnished)</th>
            <th>Date on which time washing, colour washing or varnishing was carried out (according to English Calendar)</th>
            <th>Signature of the employer or Manager</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
        </tr>
        <tr>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td></td>
            <td></td>
        </tr>
    </table>
</body>
</html>