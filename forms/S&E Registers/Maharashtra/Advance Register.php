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
            text-align: center;
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
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="11" class="title">Form-IV<br>The Maharashtra Payment of Wages Rules, 1963 See Rule 18(3)<br>Register of Advances made to employed person</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of Factory or Industrial Establishment</th>
            <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' .$branch_address)?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month & Year :</th>
            <td colspan="7" style="text-align: left;"><?= htmlspecialchars($month . ' - ' .$year)?></td>
        </tr>
        <tr>
            <th>Serial No.</th>
            <th>Employee Code</th>
            <th>Name</th>
            <th>Father's Name or Ticket No.</th>
            <th>Department</th>
            <th>Date and amount of advance given</th>
            <th>Purpose (s) for which advance made</th>
            <th>Number of installments by which advance to be repaid</th>
            <th>Postponements granted</th>
            <th>Date on which total amount repaid</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>9</th>
            <th>10</th>
            <th>11</th>
        </tr>
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
    </table>
</body>
</html>