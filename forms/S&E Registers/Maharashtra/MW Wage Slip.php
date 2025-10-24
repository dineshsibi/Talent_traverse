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
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <?php 
    $totalEmployees = count($stateData);
    $currentEmployee = 0;
    
    foreach ($stateData as $row): 
        $currentEmployee++; 

        $fixed_gross = (float)($row['fixed_gross'] ?? 0);
        $calculation= $fixed_gross/8;
    ?>
    <table>
        <tr>
            <th colspan="3" class="title">Attendance card-cum-wage slip<br>The Maharashtra Minimum Wages Rules, 1963 Rule 27(2)</th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name and Address of Establishment of Employer :</th>
            <td  style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Nature and location of work :</th>
            <td  style="text-align: left;"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Employee Code</th>
            <td  style="text-align: left;"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name and Father's/Husband's name of the workman :</th>
            <td  style="text-align: left;"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">For the Week/Fortnight/Month ending :</th>
            <td  style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>(1)</th>
            <th style="text-align: left;">No. of days worked</th>
            <td style="text-align: left;"><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
        </tr>
        <tr>
            <th>(2)</th>
            <th style="text-align: left;">No. of units worked in case of piece-rate workers</th>
            <td style="text-align: left;"><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
        </tr>
        <tr>
            <th>(3)</th>
            <th style="text-align: left;">Rate of daily wages/piece-rate</th>
            <td style="text-align: left;"><?= number_format($calculation ?? 0, 2) ?></td>
        </tr>
        <tr>
            <th>(4)</th>
            <th style="text-align: left;">Amount of overtime wages:</th>
            <td style="text-align: left;"><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
        </tr>
        <tr>
            <th>(5)</th>
            <th style="text-align: left;">Gross wages payable</th>
            <td style="text-align: left;"><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
        </tr>
        <tr>
            <th>(6)</th>
            <th style="text-align: left;">Deductions, if any</th>
            <td style="text-align: left;"><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
        </tr>
        <tr>
            <th>(7)</th>
            <th style="text-align: left;">Net amount of wages paid</th>
            <td style="text-align: left;"><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2">Signature of the Employer/ Manager/or any other Authorised Person.</th>
            <th>Initials of the Contractor or his Representative</th>
        </tr>
    </table>
    
    <?php 
    // Add page break except for last employee
    if ($currentEmployee < $totalEmployees): 
    ?>
    <div class="page-break"></div>
    <?php endif; ?>
    
    <?php endforeach; ?>
</body>
</html>