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

        .text-left {
            text-align: left;
        }

        .main-heading {
            text-align: center;
            font-size: 14px;
            padding: 6px;
            border: 1px solid #000;
            background-color: #ffffff;
        }

        .sub-heading {
            text-align: left;
            font-weight: bold;
            background-color: #ffffff;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <th colspan="12" class="main-heading">
                Form 12<br>
                The Orissa Shops and Commercial Establishments (Amendment) Rules, 2009, [See Rule-12 (4) ]<br>
                COMBINED REGISTER OF OVERTIME WORKING AND PAYMENT
            </th>
        </tr>

        <!-- Appendix -->
        <tr>
            <th colspan="12" class="text-left" style="text-align: center; font-weight: bold;">
                Appendix-2(c)
            </th>
        </tr>

        <!-- In lieu of section -->
        <tr>
            <th colspan="12" class="text-left">
                In lieu of:<br>
                1. Form No. 10 of Rule 79 of Orissa Factories Rules, 1950 (N.B.: Rule 80 & Form 11 may be annulled)<br>
                2. Form No. IV of Rule 25(2) of Orissa Minimum Wages Rules, 1954.<br>
                3. Form No. XIX of Rule 77(2)(e) of Orissa Contract Labour (R&A) Rules, 1975.<br>
                4. Form No. 12 of Rule 12(4) & Rule 15(3) of Orissa Shops & commercial Establishment Rules, 1956.<br>
                5. Form No. XIV of Rules 33(5) of Orissa B.C.W.(COE) Rules, 1969.<br>
                6. Form No. XI of Rule 37 of Orissa M.T. Workers Rules, 1966.<br>
                7. Form No. XVII of Rule 52(2)(a) of Orissa ISMW (RE & CS) Rules, 1980.<br>
                8. Form No. XXII of Rule 239(1)(c) of Orissa Building and other Construction Workers (Regulation of Employment & Condition of Service) Rules, 2002.
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="4" class="sub-heading">Name and Address of the Establishment:</td>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="sub-heading">Month & year</td>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th rowspan="2">SL. No</th>
            <th rowspan="2">Name of the Employee/ Father's/ Husband's Name</th>
            <th rowspan="2">Sex</th>
            <th rowspan="2">Designation</th>
            <th rowspan="2">Emp No/ Sl. No. in register of employees</th>
            <th colspan="2">Particulars of OT work</th>
            <th rowspan="2">Normal rate of the wages per hour</th>
            <th rowspan="2">Overtime rate of wages per hour</th>
            <th rowspan="2">Total OT earnings</th>
            <th rowspan="2">Signature of the employee</th>
            <th rowspan="2">Signature of the paying Authority</th>
        </tr>
        <tr>
            <th>Date</th>
            <th>hours</th>
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
            <th>12</th>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                        $fixed_gross = (float)($row['fixed_gross'] ?? 0);

                        // Apply formula: {[(Input AI % 30) % 8] × 2}
                        $overtimerate = (($fixed_gross / 31) / 8) * 2;


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
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                        <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                        <td>As per Act</td>
                        <td><?= htmlspecialchars(round($overtimerate ?? '')) ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td></td>
                        <td></td>
                    </tr>
        </tbody>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="25" class="no-data">No contractor data available for Odisha</td>
    </tr>
<?php endif; ?>
    </table>
</body>

</html>