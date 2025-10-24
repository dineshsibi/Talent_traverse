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
            text-align: center;
            word-wrap: break-word;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .form-header {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
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
        <th colspan="23" class="form-header">
            Form X <br>
            Register of Wages <br>
            The Punjab Minimum Wages Rules, 1950 [See Rule 26 (1)]
        </th>
    </tr>
    <tr>
        <th colspan="10" style="text-align: left;">Name and Address of the Establishment</th>
        <td colspan="13" style="text-align: left;"> <?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
    </tr>
    <tr>
        <th colspan="10" style="text-align: left;">Place</th>
        <td colspan="13" style="text-align: left;"><?= htmlspecialchars($first_row['location_name'] ?? '') ?></td>
    </tr>
    <tr>
        <th colspan="10" style="text-align: left;">Wage Period</th>
        <td colspan="13" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
    </tr>
    <tr>
        <th rowspan="2">S.No</th>
        <th rowspan="2">Employee Code</th>
        <th rowspan="2">Name of the Employee<br>Father's/Husband's Name</th>
        <th rowspan="2">Designation</th>
        <th rowspan="2">Total attendance<br>or Unit Work</th>
        <th rowspan="2">Minimum Rate<br>of Wages</th>
        <th rowspan="2">Date on which over time worked</th>
        <th colspan="5">Wages Actually Paid</th>
        <th rowspan="2">Gross<br>Wages<br>Payable</th>
        <th colspan="7">Deductions</th>
        <th rowspan="2">Actual Wages Paid Rs. Ps.</th>
        <th rowspan="2">Date of Payment</th>
        <th rowspan="2">Signature or thumb impression of employee</th>
    </tr>
    <tr>
        <th>Basic</th>
        <th>HRA</th>
        <th>Conveyance</th>
        <th>Special Allowance</th>
        <th>Other Allowance</th>
        <th>Employee's contribution to P.F</th>
        <th>E.S.I</th>
        <th>Ptax</th>
        <th>TDS</th>
        <th>LWF</th>
        <th>Other deduction</th>
        <th>Total deduction</th>
    </tr>
    <tbody>
        <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                    $basic = (float) ($row['basic'] ?? 0);
                    $hra = (float) ($row['hra'] ?? 0);
                    $conveyance = (float) ($row['conveyance_allowance'] ?? 0);
                    $special = (float) ($row['special_allowance'] ?? 0);
                    $other_allowance = (float) ($row['other_allowance'] ?? 0);
                    $gross = (float) ($row['gross_wages'] ?? 0);
                    $pf = (float) ($row['epf'] ?? 0) + (float) ($row['vpf'] ?? 0);
                    $esi = (float) ($row['esi'] ?? 0);
                    $ptax = (float) ($row['ptax'] ?? 0);
                    $tds = (float) ($row['it_tds'] ?? 0);
                    $lwf = (float) ($row['lwf'] ?? 0);
                    $other_deduction = (float) ($row['other_deductions'] ?? 0);
                    $total_deduction = (float) ($row['total_deduction'] ?? 0);
                    $net_pay = (float) ($row['net_pay'] ?? 0);

                    $overtimeDays = [];
                
                // Check each day (day_1 to day_31) for overtime
                for ($day = 1; $day <= 31; $day++) {
                    $dayColumn = 'day_' . $day;
                    if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                        $hours = (float)$row[$dayColumn];
                        if ($hours > 8.0) {
                            $overtimeDays[] = $day; // Store the day number if hours > 8
                        }
                    }
                }
                
                // Convert array of days to comma-separated string
                $overtimeDaysStr = !empty($overtimeDays) ? implode(',', $overtimeDays) : '-';
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                    <td class="left-align">
                        <?= htmlspecialchars($row['employee_name'] ?? '') ?><br>
                        <?= htmlspecialchars($row['father_name'] ?? '') ?>
                    </td>
                    <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                    <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                    <td><?= number_format($basic, 2) ?></td>
                    <td><?= number_format($hra, 2) ?></td>
                    <td><?= number_format($conveyance, 2) ?></td>
                    <td><?= number_format($special, 2) ?></td>
                    <td><?= number_format($other_allowance, 2) ?></td>
                    <td><?= number_format($gross, 2) ?></td>
                    <td><?= number_format($pf, 2) ?></td>
                    <td><?= number_format($esi, 2) ?></td>
                    <td><?= number_format($ptax, 2) ?></td>
                    <td><?= number_format($tds, 2) ?></td>
                    <td><?= number_format($lwf, 2) ?></td>
                    <td><?= number_format($other_deduction, 2) ?></td>
                    <td><?= number_format($total_deduction, 2) ?></td>
                    <td><?= number_format($net_pay, 2) ?></td>
                    <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                    <td class="signature-cell"></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
            <td colspan="23" style="text-align:center;">No employee data found for Chandigarh</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
