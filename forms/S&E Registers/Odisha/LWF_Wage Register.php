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
$currentState = 'Odisha'; // Hardcoded for this state template

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
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.2;
        }

        .page {
            width: 100%;
            page-break-after: always;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .subheading {
            font-weight: bold;
            background-color: #ffffff;
        }

        .note {
            font-style: italic;
            margin-top: 5px;
        }

        .text-left {
            text-align: left;
        }

        .col-small {
            width: 5%;
        }

        .col-medium {
            width: 8%;
        }

        .col-large {
            width: 12%;
        }
    </style>
</head>

<body>
    <div class="page">
        <table>
            <tr>
                <th colspan="18" style="text-align: center; font-size: 16px; padding: 8px;">
                    FORM D<br>
                    The Odisha Labour Welfare Fund Rules, 2015, See sub-rule (1) of rule 4<br>
                    Register of Wages
                </th>
            </tr>
            <tr>
                <th colspan="4" class="text-left">Name of the establishment</th>
                <td colspan="14" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" class="text-left">For the month of</th>
                <td colspan="14" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">Sl No.</th>
                <th rowspan="2">Name of the employee</th>
                <th rowspan="2">Ticket No and badge No.</th>
                <th rowspan="2">Occupation</th>
                <th colspan="4">Amount payable during the month Jun-2025</th>
                <th colspan="2">Amount Deducted</th>
                <th colspan="4">Amount Actually paid during the month</th>
                <th colspan="4">Balance due to the Employee</th>
            </tr>
            <tr>
                <th>Basic Wage</th>
                <th>Overtime</th>
                <th>Dearness allowance & other allowance</th>
                <th>Bonus</th>
                <th>Fines</th>
                <th>Other Deductions</th>
                <th>Basic Wages</th>
                <th>Over time</th>
                <th>Dearness allowance & other allowances.</th>
                <th>Bonus</th>
                <th>Basic wage</th>
                <th>Overtime</th>
                <th>Dearness allowance and other allowance</th>
                <th>Bonus</th>
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
                <th>12</th>
                <th>13</th>
                <th>14</th>
                <th>15</th>
                <th>16</th>
                <th>17</th>
                <th>18</th>
            </tr>
            <tbody>
                <?php if (!empty($stateData)): ?>
                    <?php $i = 1;
                    foreach ($stateData as $row): ?>
                        <?php
                        $Gross = (float)($row['gross_wages'] ?? 0);
                        $basic = (float)($row['basic'] ?? 0);
                        $Overtime = (float)($row['over_time_allowance'] ?? 0);
                        $statutory = (float)($row['statutory_bonus'] ?? 0);
                        $dearness_allowance =  $Gross - ($basic + $Overtime + $statutory);

                        $net = (float)($row['net_pay'] ?? 0);
                        $overtime =  $net - ($basic + $Overtime + $statutory);
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                            <td><?= htmlspecialchars($dearness_allowance ?? '') ?></td>
                            <td><?= htmlspecialchars($row['statutory_bonus'] ?? '') ?></td>
                            <td>Nil</td>
                            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                            <td><?= htmlspecialchars($overtime ?? '') ?></td>
                            <td><?= htmlspecialchars($row['statutory_bonus'] ?? '') ?></td>
                            <td>Nil</td>
                            <td>Nil</td>
                            <td>Nil</td>
                            <td>Nil</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="18" class="no-data">No contractor data available for Odisha</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>