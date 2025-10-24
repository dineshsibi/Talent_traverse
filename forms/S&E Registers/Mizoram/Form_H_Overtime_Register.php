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
$currentState = 'Mizoram'; // Hardcoded for this state template

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
            background-color: #ffffffff;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .main-heading {
            text-align: center;
            font-size: 14px;
            padding: 6px;
            border: 1px solid #000;
            background-color: #ffffffff;
        }

        .info-row {
            text-align: left;
            font-weight: bold;
        }

        .info-data {
            text-align: left;
        }

        .note {
            font-style: italic;
            font-size: 11px;
            text-align: left;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <th colspan="11" class="main-heading">
                FORM 'H'<br>
                The Mizoram Shops and Establishments Act, 2010 and Rules, 2011, [See Rule 17]<br>
                Register of overtime work and payment of overtime wages
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="5" class="info-row">Name of the Establishment :</td>
            <td colspan="6" class="info-data"><?= htmlspecialchars($client_name) ?></td>
        </tr>
        <tr>
            <td colspan="5" class="info-row">Name of the Employer :</td>
            <td colspan="6" class="info-data"><?= htmlspecialchars($employer_name) ?></td>
        </tr>
        <tr>
            <td colspan="5" class="info-row">Address:</td>
            <td colspan="6" class="info-data"><?= htmlspecialchars($branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="5" class="info-row">Month & Year</td>
            <td colspan="6" class="info-data"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <td colspan="5" class="info-row">Registration No:</td>
            <td colspan="6" class="info-data">-</td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th>Sl No</th>
            <th>Name of the Employee</th>
            <th>Employee Code</th>
            <th>Rate of Wages</th>
            <th>Money value of meals and concessional supply of food grains etc., if any</th>
            <th>Overtime rate of wages per hour</th>
            <th>Dates on which overtime work was done</th>
            <th>Extent of overtime work done on each day (in hours)</th>
            <th>Total amount of overtime wages the employee entitled to</th>
            <th>Total amount of overtime wages paid</th>
            <th>Signature of the employee</th>
        </tr>

        <!-- Column Numbers -->
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
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $fixed_gross = (float)($row['fixed_gross'] ?? 0);
                    $overtime =  $fixed_gross / 31 / 8 * 2;

                    $overtimeDates = [];
                    $overtimeHours = [];

                    // Loop through day_1 to day_31
                    for ($d = 1; $d <= 31; $d++) {
                        $col = "day_" . $d;
                        $value = $row[$col] ?? '';

                        // Extract numeric value (ignore alphabets)
                        $numeric = (int)preg_replace('/[^0-9]/', '', $value);

                        if ($numeric > 8) {
                            $overtimeDates[] = $d; // store the day number
                            $overtimeHours[] = $numeric - 8; // store overtime hours
                        }
                    }

                    $datesString = !empty($overtimeDates) ? implode(', ', $overtimeDates) : '-';
                    $hoursString = !empty($overtimeHours) ? implode(', ', $overtimeHours) : '-';;
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                        <td>Nil</td>
                        <td><?= htmlspecialchars(round($overtime ?? '')) ?></td>
                        <td><?= $datesString ?></td> <!-- Dates with OT -->
                        <td><?= $hoursString ?></td> <!-- Overtime hours -->
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="18" class="no-data">No contractor data available for Mizoram</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="5" style="text-align: left;">Date:</th>
                <th colspan="6" style="text-align: right;">Signature of the Employer:</th>
            </tr>
        </tbody>
    </table>
</body>

</html>