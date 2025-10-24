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
$currentState = 'Telangana'; // Hardcoded for this state template

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

    $location_name = $first_row['location_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';

    // Calculate totals for each day column
    $dayTotals = array_fill(1, 31, 0);
    $grandTotal = 0;

    foreach ($stateData as $row) {
        for ($day = 1; $day <= 31; $day++) {
            $dayColumn = 'day_' . $day;
            if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                $dayTotals[$day] += (float)$row[$dayColumn];
            }
        }

        // Add to grand total if available
        if (isset($row['total_worked_days']) && is_numeric($row['total_worked_days'])) {
            $grandTotal += (float)$row['total_worked_days'];
        }
    }
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

        th,
        td {
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

        .left-align {
            text-align: left;
        }
    </style>
</head>

<body>

    <table>
        <!-- Title Row -->
        <tr>
            <th colspan="12" class="title">
                FORM XXIII<br>
                The Andhra Pradesh Shops and Establishments Act, 1988 and Rules, 1990 [See Rule 29(2)]<br>
                Register of Wages
            </th>
        </tr>

        <!-- Establishment Information -->
        <tr>
            <th colspan="4" class="left-align">Name of the Establishment/Shop</th>
            <td colspan="8" class="left-align"><?= htmlspecialchars($first_row['client_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="4" class="left-align">Address :</th>
            <td colspan="8" class="left-align"><?= htmlspecialchars($first_row['branch_address'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="4" class="left-align">Registration No.</th>
            <td colspan="8" class="left-align">-</td>
        </tr>
        <tr>
            <th colspan="4" class="left-align">Wage period</th>
            <td colspan="8" class="left-align"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th>S. No</th>
            <th>Employee ID</th>
            <th>Name of the Employee</th>
            <th>Date of appointment</th>
            <th>Rate of wages</th>
            <th>Normal wages earned</th>
            <th>Wages earned for overtime work</th>
            <th>Gross wage payable</th>
            <th>Deductions if any and reasons thereof</th>
            <th>Actual wages paid</th>
            <th>Date of Payment</th>
            <th>Signature or thumb impression of the employee</th>
        </tr>

        <!-- Numbered Columns -->
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
        </tr>
         <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                <!-- Sample Data Rows -->
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="42" class="no-data" style="text-align: center;">No data available for Telangana</td>
                </tr>
            <?php endif; ?>
    </table>
</body>
</html>