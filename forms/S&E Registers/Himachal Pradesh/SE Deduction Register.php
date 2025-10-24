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
$currentState = 'Himachal Pradesh'; // Hardcoded for this state template

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
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            font-weight: bold;
        }
        .header {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="13" class="header">
                FORM No. 10<br>
                Rule 14 of the Himachal Pradesh Shops and Commercial Establishments Rules, 1972 (See Rule 14)<br>
                Register of Deduction
            </td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name and address of establishment:</th>
            <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Month & Year:</th>
            <td colspan="7" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th colspan="13" style="text-align: left;">Acts and Omissions approved by the authorities:</th>
        </tr>
        <tr>
            <th>Sl. No.</th>
            <th>Name of employee</th>
            <th>Parentage</th>
            <th>Wage period</th>
            <th>Wages payable</th>
            <th>Amount deducted</th>
            <th>Fault for which deduction made</th>
            <th>Date of deduction</th>
            <th>Whether employee showed cause against deduction</th>
            <th>Amount of deduction and purpose for which utilisation</th>
            <th>Balance with the employer</th>
            <th>Signature of employee Signature of employer</th>
            <th>Remarks</th>
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
            <td>NIL</td>
            <td></td>
            <td></td>
        </tr>
    </table>
</body>
</html>