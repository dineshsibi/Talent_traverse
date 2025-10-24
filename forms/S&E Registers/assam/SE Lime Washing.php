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
$currentState = 'Assam'; // Hardcoded for this state template

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .header-info {
            margin-bottom: 15px;
        }
        .header-info div {
            margin-bottom: 5px;
        }
        .text-center {
            text-align: center;
        }

    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th class="title" colspan="7">Form H<br>
                Record Of Lime Washing, Painting, Etc<br>
                The Assam Shops and Establishments Act, 1971 with Rules, 1976 [See Rule 24(7)]</th>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Name and Address of the Establishment</th>
                <td colspan="4" style="text-align: left;"><?= htmlspecialchars($client_name .' , '. $branch_address)?></td>
            </tr>
            <tr>    
                <th colspan="3" style="text-align: left;">Month/Year</th>
                <td colspan="4" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
            </tr>
            <tr>
                <th>Sl. No.</th>
                <th>Description of part <br>of the establishment<br> i.e., name of room etc.</th>
                <th>Parts lime washed<br> colour washed, <br>painted or varnished, <br>e.g. walls ceilings <br>wood works, etc.</th>
                <th>Treatment whether lime<br> washed or colour washed,<br> painted or varnished</th>
                <th>Date on which lime washing,<br> colour washing, <br>painting or varnishing <br>was carried out<br> according to the <br>English Calendar</th>
                <th>Signature of the <br>employer</th>
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
                    <td></td>
                </tr>
        </tbody>
    </table>
</body>
</html>