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


$client_name = safe($first_row['client_name'] ?? '');
$branch_address = safe($first_row['branch_address'] ?? '');

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
            font-size: 12px;
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
            word-wrap: break-word;
            vertical-align: top;
        }
        th {
            background-color: #ffffffff;
            font-weight: bold;
            text-align: center;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>

<table>
    <colgroup>
         <col style="width: 5%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 7%">
    <col style="width: 7%">
    <col style="width: 7%">
    <col style="width: 7%">
    <col style="width: 6%">
    <col style="width: 6%">
    <col style="width: 7%">
    <col style="width: 7%">
    <col style="width: 7%">
    <col style="width: 7%">
    <col style="width: 5%">
    <col style="width: 5%">
    <col style="width: 5%">
    <col style="width: 5%">
    </colgroup>
    <tbody>
        <!-- Form Title -->
        <tr>
            <td colspan="19" class="form-title">
                Form A <br>
                The Punjab Labour Welfare Fund Act, 1965 & Rules, 1966 [Rule 22] <br>
                Register Of Wages
            </td>
        </tr>

        <!-- Establishment Info -->
        <tr>
            <td colspan="3" style="font-weight:bold;" style="text-align: left;">Name and Address of the Factory/ Establishment</td>
            <td colspan="16" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold;" style="text-align: left;">Month & Year</td>
            <td colspan="16" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th rowspan="2">Serial No.</th>
            <th rowspan="2">Name of the employees</th>
            <th rowspan="2">Ticket and badge No.</th>
            <th rowspan="2">Occupation</th>
            <th colspan="4">Amount payable during the month</th>
            <th colspan="2">Amount Deducted during the month</th>
            <th colspan="4">Amount Actually paid during the month</th>
            <th colspan="4">Balance due to the Employee</th>
        </tr>
        <tr>
            <th>Basic Wages</th>
            <th>Overtime</th>
            <th>DA & Other</th>
            <th>Bonus</th>
            <th>Fine</th>
            <th>Other</th>
            <th>Basic Wages</th>
            <th>Overtime</th>
            <th>DA & Other</th>
            <th>Bonus</th>
            <th>Basic</th>
            <th>OT</th>
            <th>DA</th>
            <th>Bonus</th>
        </tr>

        <!-- Data Rows -->
        <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                    $basic = (float) ($row['basic'] ?? 0);
                    $ot = (float) ($row['overtime_allowance'] ?? 0);
                    $bonus = (float) ($row['statutory_bonus'] ?? 0);
                    $fines = (float) ($row['fines_damage_losses'] ?? 0);
                    $deduction = (float) ($row['total_deduction'] ?? 0);
                    $gross = (float) ($row['gross_wages'] ?? 0);
                    $other_allowances = $gross - $basic - $ot - $bonus;
                    $actual_paid = $gross;
                    $other_deductions = $deduction - $fines;
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                    <td><?= number_format($basic, 2) ?></td>
                    <td><?= number_format($ot, 2) ?></td>
                    <td><?= number_format($other_allowances, 2) ?></td>
                    <td><?= number_format($bonus, 2) ?></td>
                    <td><?= number_format($fines, 2) ?></td>
                    <td><?= number_format($other_deductions, 2) ?></td>
                    <td><?= number_format($basic, 2) ?></td>
                    <td><?= number_format($ot, 2) ?></td>
                    <td><?= number_format($other_allowances, 2) ?></td>
                    <td><?= number_format($bonus, 2) ?></td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td>Nil</td>
                   
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="19" style="text-align:center;">No employee data found for Chandigarh</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
