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
$currentState = 'Dadra and Nagra Haveli'; // Hardcoded for this state template

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
            font-family: "Times New Roman", Times, Serif;
            ;
            margin: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #0c0000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffffff;
        }

        .title {
            text-align: center;
            font-weight: bold;
        }

        * {
            font-family: "Times New Roman", Times, Serif;
        }

        .form-header {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="16">
                    FORM-VIII <br>
                    The Goa, Daman and Diu Minimum Wages Rules, 1975, [See rule 28 (2)] <br>
                    Register of Overtime Workers
                </th>
            </tr>
            <tr>
                <th colspan="8" style="text-align: left;">Name and Address of the Establishment /Factory </th>
                <td colspan="8" style="text-align: left;">
                    <?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?>
                </td>
            </tr>
            <tr>
                <th colspan="8" style="text-align: left;">Month & Year</th>
                <td colspan="8" style="text-align: left;">
                    <?= htmlspecialchars($month . ' - ' . $year) ?>
                </td>
            </tr>
            <tr>
                <th>Sl No</th>
                <th>Name</th>
                <th>Employee Code</th>
                <th>Father's/Husband's Name</th>
                <th>Sex</th>
                <th>Designation and Department</th>
                <th>Dates on which over-time worked</th>
                <th>Extent of overtime on each occasion</th>
                <th>Total overtime worked of production in case of piece workers</th>
                <th>Normal Hours</th>
                <th>Normal rate</th>
                <th>Overtime rate</th>
                <th>Normal earnings</th>
                <th>Overtime earnings</th>
                <th>Total Earnings</th>
                <th>Date on which over time payments made </th>
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
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1;
                foreach ($stateData as $row): ?>
            <?php
                    $fixed_gross = (float)($row['fixed_gross'] ?? 0);
                    $overtime_rate = (($fixed_gross / 31) / 8) * 2;


                    $Gross = (float)($row['gross_wages'] ?? 0);
                    $Overtime = (float)($row['over_time_allowance'] ?? 0);
                    $normal_earnings =  $Gross - $Overtime;

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
                <td>
                    <?= $i++ ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['employee_name'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['employee_code'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['father_name'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['gender'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['designation'] ?? '') ?>
                </td>
                <td>
                    NIL
                </td>
                <td>
                    <?= htmlspecialchars($row['extent_ot_on_which_occasion'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['ot_hours'] ?? '') ?>
                </td>
                <td>8</td>
                <td>
                    <?= htmlspecialchars($row['fixed_gross'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars(round($overtime_rate ?? '')) ?>
                </td>
                <td>
                    <?= htmlspecialchars($normal_earnings ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['over_time_allowance'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['gross_wages'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($overtimeDaysStr  ?? '') ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="16" style="text-align:center;">No data available for Dadra and Nagra Haveli</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>