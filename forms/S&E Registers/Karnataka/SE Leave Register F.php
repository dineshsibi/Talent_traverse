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
$currentState = 'Karnataka'; // Hardcoded for this state template

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


    $client_name = safe($first_row['client_name'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');
    $location = safe($first_row['location_name'] ?? '');
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
            text-align: center;
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

        .part-header {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;

        $monthNum = date('m', strtotime($month)); // Convert month name to number
        $firstDay = date('d-M-Y', strtotime("$year-$monthNum-01"));
        $lastDay = date('d-M-Y', strtotime("last day of $year-$monthNum"));

        // Query to get PL (Paid Leave) data
        $leaveSql = "SELECT day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10,
                        day_11, day_12, day_13, day_14, day_15, day_16, day_17, day_18, day_19, day_20,
                        day_21, day_22, day_23, day_24, day_25, day_26, day_27, day_28, day_29, day_30, day_31
                 FROM input
                 WHERE employee_code = :emp_code 
                 AND month = :month 
                 AND year = :year";

        $leaveStmt = $pdo->prepare($leaveSql);
        $leaveStmt->bindValue(':emp_code', $row['employee_code']);
        $leaveStmt->bindValue(':month', $month);
        $leaveStmt->bindValue(':year', $year);
        $leaveStmt->execute();
        $leaveData = $leaveStmt->fetch(PDO::FETCH_ASSOC);

        // Process leave data to find PL days
        $plDays = [];
        $plCount = 0;

        if ($leaveData) {
            for ($day = 1; $day <= 31; $day++) {
                $dayKey = 'day_' . $day;
                if (isset($leaveData[$dayKey]) && $leaveData[$dayKey] == 'PL') {
                    $plDays[] = $day; // Store just the day number
                    $plCount++;
                }
            }
        }

        // Format PL days for display (e.g., "5,6")
        $plDaysDisplay = implode(',', $plDays);

    ?>
        <table>
            <tr>
                <th colspan="16" class="title">FORM 'F' <br>The Karnataka Shops and Commercial Establishments Rules, 1963, [See Rule 8 ]<br>REGISTER OF LEAVE WITH WAGES</th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name & Address of the Establishment</th>
                <td colspan="12" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;"> Sl. No. in the Register of adult/young person :</th>
                <td colspan="12" style="text-align: left;"><?= htmlspecialchars(($row['employee_code'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;"> Date of entry into service :</th>
                <td colspan="12" style="text-align: left;"><?= htmlspecialchars(($row['date_of_joining'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;"> Name of the person :</th>
                <td colspan="12" style="text-align: left;"><?= htmlspecialchars(($row['employee_name'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Father's/Husband's name :</th>
                <td colspan="12" style="text-align: left;"><?= htmlspecialchars(($row['father_name'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;"> Address :</th>
                <td colspan="12" style="text-align: left;">Nil</td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;"> For the month of :</th>
                <td colspan="12" style="text-align: left;"><?= htmlspecialchars(($row['month'] ?? '') . ' - ' . ($row['year'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="12">Part-I Earned Leave</th>
                <th colspan="4">Part- II Sick/Accident Leave (with pay)</td>
            </tr>
            <tr>
                <th rowspan="2">S. No</th>
                <th colspan="3">No of Days worked</th>
                <th rowspan="2">Leave earned</th>
                <th rowspan="2">Leave at credit(including balance, if any, on return from leave on last occasion)</th>
                <th colspan="3">Leave Taken</th>
                <th rowspan="2">Balance on return from leave</th>
                <th rowspan="2">Date on which wages for leave paid and amount paid</th>
                <th rowspan="2">Remarks</th>
                <th rowspan="2">Year</th>
                <th colspan="2">Sick/Accident Leave</th>
                <th rowspan="2">Balance at the end of the year</th>
            </tr>
            <tr>
                <th>From</th>
                <th>TO</th>
                <th>Total days worked</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>of Credit</th>
                <th>Availed</th>
            </tr>
            <tr>
                <th>1</th>
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
            </tr>
            <tr>
                <td><?= $currentEmployee ?></td>
                <td><?= htmlspecialchars($firstDay) ?></td>
                <td><?= htmlspecialchars($lastDay) ?></td>
                <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_credit'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_credit'] ?? '') ?></td>
                <td colspan="2"><?= htmlspecialchars($plDaysDisplay) ?></td> <!-- Combined From-To showing PL days -->
                <td><?= htmlspecialchars($plCount) ?></td> <!-- Count of PL days -->
                <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                <td></td>
                <td><?= htmlspecialchars($row['year'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sl_credit'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
            </tr>
        </table>
    <?php endforeach; ?>
</body>

</html>