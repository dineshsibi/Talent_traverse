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
$currentState = 'Chandigarh'; // Hardcoded for this state template

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            word-wrap: break-word;
            text-align: center;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
        .left-align {
            text-align: left;
        }
        .signature-cell {
            height: 30px;
            vertical-align: bottom;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <th colspan="12" class="form-header">
            Form D<br>
            (Rule 5 Of The Punjab Shops And Commercial Establishments Rules,1958) <br>
            Register Of Wages Of Employees
        </th>
    </tr>
    <tr>
        <th colspan="3" style="text-align: left;">Name and Address of the Establishment</th>
        <td colspan="9" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '-')) ?></td>
    </tr>
    <tr>
        <th colspan="3" style="text-align: left;">Month & Year</th>
        <td colspan="9" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
    </tr>
    <tr>
        <th colspan="3" style="text-align: left;">Wages Fixed</th>
        <td colspan="9" style="text-align: left;">Monthly</td>
    </tr>

    <!-- Column Headers -->
    <tr>
        <th rowspan="2" style="width: 5%;">S. No</th>
        <th rowspan="2" style="width: 8%;">Employee Code</th>
        <th rowspan="2" style="width: 12%;">Name of the Employee</th>
        <th rowspan="2" style="width: 12%;">Father's/Husband's name</th>
        <th rowspan="2" style="width: 8%;">Arrears from last month</th>
        <th rowspan="2" style="width: 8%;">Wages earned during the month</th>
        <th colspan="2" style="width: 16%;">Wages Due</th>
        <th rowspan="2" style="width: 8%;">Deductions shown in Register - E</th>
        <th rowspan="2" style="width: 8%;">Advances made on (date)</th>
        <th rowspan="2" style="width: 8%;">Payment made</th>
        <th rowspan="2" style="width: 7%;">Signature of employee</th>
    </tr>
    <tr>
        <th style="width: 8%;">Ordinary</th>
        <th style="width: 8%;">Overtime</th>
    </tr>

    <!-- Data Rows -->
    <?php if (!empty($stateData)): ?>
        <?php $i = 1; foreach ($stateData as $row): ?>
            <?php
                $grossWages = (float) ($row['gross_wages'] ?? 0);
                $overtimeAllowance = (float) ($row['overtime_allowance'] ?? 0);
                $totalDeduction = (float) ($row['total_deduction'] ?? 0);
                $totalWagesDue = $grossWages + $overtimeAllowance;
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td class="left-align"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                <td>Nil</td>
                <td><?= number_format($grossWages, 2) ?></td>
                <td><?= number_format($grossWages, 2) ?></td>
                <td><?= number_format($overtimeAllowance, 2) ?></td>
                <td><?= number_format($totalDeduction, 2) ?></td>
                <td>Nil</td>
                <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                <td class="signature-cell"></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
            <tr>
                <th colspan="12" style="text-align:center;">No employee data found for Chandigarh</th>
            </tr>
    <?php endif; ?>
</table>

</body>
</html>
