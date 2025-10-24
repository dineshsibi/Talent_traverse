<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__ . '/../../../includes/config.php';
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
$currentState = 'Haryana'; // Hardcoded for this state template

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .empty-row {
            height: 25px;
        }

        .left-align {
            text-align: left;
        }

        .colspan-full {
            width: 100%;
        }

        .signature-cell {
            height: 30px;
            vertical-align: bottom;
        }

        .sub-header {
            font-weight: normal;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="23">
                    Form D<br>
                    The Punjab Shops and Commercial Establishments Rules, 1958, Rule 5 <br>
                    Register Of Wages Of Employees
                </th>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="11">Name and Address of the Establishment </th>
                <td colspan="12" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '-')) ?></td>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="4">Wages Fixed</th>
                <td colspan="7" style="text-align: left;">Monthly</td>
                <th colspan="5" style="text-align: left;">Month & Year </th>
                <td colspan="7" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="3">S. No</th>
                <th rowspan="3">Employee Code</th>
                <th rowspan="3">Name of the Employee</th>
                <th rowspan="3">Father's name or Husband's name</th>
                <th rowspan="3">Wages Fixed</th>
                <th rowspan="3">Arrears from last month</th>
                <th colspan="6">Wages earned during the month</th>
                <th rowspan="3">Deduction shown in Register - E</th>
                <th colspan="6" rowspan="2">Other Deductions</th>
                <th rowspan="3">Payment made (Netpay)</th>
                <th rowspan="3">Signature of Employee</th>
                <th rowspan="3">Signature of Employer</th>
                <th rowspan="3">Remarks</th>
            </tr>
            <tr>
                <th colspan="6">Ordinary</th>
            <tr>
                <th>Basic</th>
                <th>HRA</th>
                <th>Conveyance</th>
                <th>Overtime</th>
                <th>Other Allowances</th>
                <th>Gross Wages</th>
                <th>EPF</th>
                <th>ESI</th>
                <th>LWF</th>
                <th>Advance Made on (Date)</th>
                <th>other Deductions</th>
                <th>Total Deduction</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $grossWages = (float) ($row['gross_wages'] ?? 0);
                    $basic = (float) ($row['basic'] ?? 0);
                    $hra = (float) ($row['hra'] ?? 0);
                    $conveyance = (float) ($row['conveyance_allowance'] ?? 0);
                    $overtime = (float) ($row['over_time_allowance'] ?? 0);
                    $other_allowances =$grossWages - ($basic + $hra+ $conveyance+ $overtime);

                    $epf = (float) ($row['epf'] ?? 0);
                    $vpf = (float) ($row['vpf'] ?? 0);
                    $EPF =$epf+ $vpf;

                    $total = (float) ($row['total_deduction'] ?? 0);
                    $esi = (float) ($row['esi'] ?? 0);
                    $lwf = (float) ($row['lwf'] ?? 0);
                    $advance = (float) ($row['advance_recovery'] ?? 0);
                    $other_deductions =$total - ($epf + $vpf+ $esi+ $lwf+ $advance);
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td class="left-align"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['conveyance_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($other_allowances ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($EPF ?? '') ?></td>
                        <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['lwf'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['advance_recovery'] ?? '') ?></td>
                        <td><?= htmlspecialchars($other_deductions ?? '') ?></td>
                        <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                        <td></td>
                        <td class="signature-cell"></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="no-data" colspan="23">No contractor data available for Haryana</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>