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
$currentState = 'Sikkim'; // Hardcoded for this state template

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
            margin: 10px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            word-wrap: break-word;
            vertical-align: top;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>

<body>

    <table>
        <thead>
            <tr>
                <th colspan="10" class="form-header">
                    Form X <br>
                    The Sikkim Minimum Wages Rules, 2005, [See Rule 26 (1)] <br>
                    Register of Wages
                </th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name and Address of the Factory/ Establishment</th>
                <td colspan="6" style="text-align: left;">
                    <?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?>
                </td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Place</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($first_row['location_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Month & Year</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>

            <!-- Table Headers -->
            <tr>
                <th>Sl. No</th>
                <th>Employee Code</th>
                <th>Name of Worker</th>
                <th>Wage Period</th>
                <th>Minimum Rate of Wages Payable</th>
                <th>Date on which overtime worked</th>
                <th>Gross wages payable</th>
                <th>Deductions, if any</th>
                <th>Actual wages paid</th>
                <th>Signature or thumb impression of the employee</th>
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
            </tr>
    </thead>
    <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                    $normalHours = 8;
                    $normalRate = (float) ($row['fixed_gross'] ?? 0);
                    $overtimeRate = $normalRate * 2; // Assuming overtime is double the normal rate
                    $normalEarnings = (float) ($row['gross_wages'] ?? 0);
                    $overtimeEarnings = (float) ($row['overtime_allowance'] ?? 0);
                    $totalEarnings = $normalEarnings + $overtimeEarnings;

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
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['month'] ?? '') ?></td>
                <td>As per Act</td>
                <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                <td></td>
            </tr>
        </tbody>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" style="text-align:center;">No data found for Sikkim</td>
            </tr>
        <?php endif; ?>
    </table>

</body>

</html>