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
$currentState = 'Meghalaya'; // Hardcoded for this state template

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
    $employer_name = safe($first_row['employer_name'] ?? '');
    $employer_address = safe($first_row['employer_address'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');
    $location = safe($first_row['location_name'] ?? '');
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
            font-family: "Times New Roman", Times, serif;
            margin: 20px;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
            background-color: white;
        }

        th {
            font-weight: bold;
        }

        .header-row {
            font-weight: bold;
        }

        .main-heading {
            text-align: center;
            font-weight: bold;
        }

        .input-field {
            min-height: 20px;
            display: block;
            border-bottom: 1px dotted #ccc;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <th colspan="10" class="main-heading">
                FORM R<br>
                The Meghalaya Shops and Establishments Rules, 2004, [See Rule 52]<br>
                Register of Overtime Work and Payment of Overtime Wages
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name of the establishment</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name) ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name of employer</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($employer_name) ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Address</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Registration No.</th>
            <td colspan="5" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Month & Year</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr class="header-row">
            <th>Sl No</th>
            <th>Name of the employee</th>
            <th>Rate of wages</th>
            <th>Money value of meals and concessional Supply of food gains, etc, if any</th>
            <th>Overtime rate of wages per hour</th>
            <th>Date on which overtime work has done</th>
            <th>Extent of overtime work done on each day (in hours)</th>
            <th>Total amount of overtime wages the employee is entitled to</th>
            <th>Total amount of overtime wages paid</th>
            <th>Signature of the employee</th>
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
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>

                    <?php
                    $fixed = (float)($row['fixed_gross'] ?? 0);
                    $overtime_rate = (($fixed / 30) / 8) * 2;

                    // ✅ Reset overtime days for each employee
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
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                        <td>Nil</td>
                        <td><?= htmlspecialchars(round($overtime_rate ?? 0)) ?></td>
                        <td><?= htmlspecialchars($overtimeDaysStr  ?? '') ?></td>
                        <td><?= htmlspecialchars($row['extent_ot_on_which_occasion'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="38" class="no-data">No data available for Meghalaya</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="10" style="text-align: left;">Signature of the Employer</th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left;">Date</th>
            </tr>
        </tbody>
    </table>
</body>

</html>