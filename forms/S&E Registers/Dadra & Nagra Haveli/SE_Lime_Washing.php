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
$currentState = 'Dadra and Nagra Haveli'; // Hardcoded for this state template

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
    $employer = safe($first_row['employer_name'] ?? '');

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
            font-family: "Times New Roman", Times, serif;
            margin: 15px;
            background-color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            background-color: white;
            font-size: 12px;
        }
        th {
            font-weight: bold;
        }
        .main-heading {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 8px;
        }
        .input-field {
            display: block;
            min-height: 18px;
            border-bottom: 1px dotted #999;
            margin-top: 3px;
        }
        .section-heading {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="8" class="main-heading">
                FORM VIII<br>
                The Goa, Daman and Dieu Shops and Establishments Act, 1973<br>
                The Dadra and Nagar Haveli Shops and Establishments Rules, 2000, [See Rule 11(1)(c)]<br>
                Record of Limewashing etc.
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of the Establishment:</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($client_name) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of the Employer:</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($employer) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month & Year</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Address</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($branch_address) ?></td>
        </tr>
        
        <!-- Column headers -->
        <tr>
            <th rowspan="2">Part of Establishment e.g. name of room</th>
            <th rowspan="2">Parts lime/colour washed, painted or varnished e.g walls, wood work , ceilings , etc.</th>
            <th rowspan="2">Treatment (whether lime/colour washed , painted or varnished)</th>
            <th colspan="3">Date on which lime/colour washing , painting or varnishing was carried out (according to English Calendar)</th>
            <th rowspan="2">Signature of employer</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>Day</th>
            <th>Month</th>
            <th>Year</th>
        </tr>
        
        <!-- Column numbers -->
        <tr class="section-heading">
            <td>1</td>
            <td>2</td>
            <td>3</td>
            <td colspan="3">4</td>
            <td>5</td>
            <td>6</td>
        </tr>
        <tr>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
            <td></td>
        </tr>
        
       
    </table>
</body>
</html>