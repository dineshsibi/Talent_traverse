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
$currentState = 'Punjab'; // Hardcoded for this state template

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
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffffff;
            font-weight: bold;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .left-align {
            text-align: left;
        }

        .label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="16" class="title">
                Form IV<br>
                The Punjab Minimum Wages Rules, 1950 [Rule 25 (3)]<br>
                Overtime Register for Workers
            </td>
        </tr>
        <tr>
            <td colspan="5" class="label" style="text-align: left;">Name and Address of the Establishment / Factory</td>
            <td colspan="11" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <td colspan="5" class="label" style="text-align: left;">Month & Year</td>
            <td colspan="11" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th style="width: 4%">Sl No</th>
            <th style="width: 7%">Employee Code</th>
            <th style="width: 10%">Name</th>
            <th style="width: 10%">Father's/Husband's Name</th>
            <th style="width: 4%">Sex</th>
            <th style="width: 10%">Designation and Department</th>
            <th style="width: 8%">Dates on which overtime worked</th>
            <th style="width: 8%">Extent of overtime on each occasion</th>
            <th style="width: 8%">Total overtime worked</th>
            <th style="width: 5%">Normal Hours</th>
            <th style="width: 6%">Normal Rate</th>
            <th style="width: 6%">Overtime Rate</th>
            <th style="width: 7%">Normal Earnings</th>
            <th style="width: 7%">Overtime Earnings</th>
            <th style="width: 7%">Total Earnings</th>
            <th style="width: 7%">Date of payment</th>
        </tr>

        <?php if (!empty($stateData)): ?>
            <?php $i = 1;
            foreach ($stateData as $row): ?>
                <?php
                $normalHours = 8;

                $fixed = (float)($row['fixed_gross'] ?? 0);
                $overtime_rate = (($fixed / 31) / 8) * 2;

                $gross = (float) ($row['gross_wages'] ?? 0);
                $overtime = (float) ($row['over_time_allowance'] ?? 0);
                $normalEarnings=$gross-$overtime;

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
                    <td class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                    <td class="left-align"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                    <td><?= htmlspecialchars($row['extent_ot_on_which_occasion'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['ot_hours'] ?? '0') ?></td>
                    <td><?= $normalHours ?></td>
                    <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                    <td><?= htmlspecialchars(round($overtime_rate)) ?></td>
                    <td><?= htmlspecialchars($normalEarnings) ?></td>
                    <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="16" style="text-align:center;">No data found for Punjab</td>
            </tr>
        <?php endif; ?>
    </table>
</body>

</html>