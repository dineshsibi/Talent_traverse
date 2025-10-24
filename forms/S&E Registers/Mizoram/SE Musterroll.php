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

    $stmt = $pdo->prepare($sql);
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
    $branch_address = safe($first_row['branch_address'] ?? '');
    $employer = safe($first_row['employer_name'] ?? '');

    // Function to generate weekdays in order starting from the first day of the month
    function getOrderedWeekdays($month, $year)
    {
        $firstDate = sprintf("%04d-%02d-01", $year, $month);
        $firstDay = date('l', strtotime($firstDate)); // e.g. Wednesday
        $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Rotate array to start from first day
        $index = array_search($firstDay, $weekdays);
        return array_merge(array_slice($weekdays, $index), array_slice($weekdays, 0, $index));
    }

    // Function to get mapping of weekday => list of (dayNum => formattedDate)
    function getWeekDates($month, $year)
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $weekDates = [
            'Sunday' => [],
            'Monday' => [],
            'Tuesday' => [],
            'Wednesday' => [],
            'Thursday' => [],
            'Friday' => [],
            'Saturday' => []
        ];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $dayOfWeek = date('l', strtotime($dateStr));
            $formattedDate = date('d.m.Y', strtotime($dateStr));
            $weekDates[$dayOfWeek][$day] = $formattedDate; // store with day number key
        }
        return $weekDates;
    }

    $orderedDays = getOrderedWeekdays($filters['month'], $filters['year']);
    $weekDates = getWeekDates($filters['month'], $filters['year']);
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
            font-family: "Times New Roman", serif;
            font-size: 12px;
            margin: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: top;
        }

        th {
            font-weight: bold;
        }

        .main-heading {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 6px;
        }

        .date-list {
            font-size: 9px;
            line-height: 1.2;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <table>
            <tr>
                <td colspan="32" class="main-heading">
                    FORM 'G'<br>
                    The Mizoram Shops and Establishments Act, 2010 and Rules, 2011, [See Rule 16]<br>
                    Register of hours of work and rest
                </td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name of the Establishment:</th>
                <td colspan="28" style="text-align: left;"><?= htmlspecialchars($client_name) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name of the Employer:</th>
                <td colspan="28" style="text-align: left;"><?= htmlspecialchars($employer) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Address:</th>
                <td colspan="28" style="text-align: left;"><?= htmlspecialchars($branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Month & Year</th>
                <td colspan="28" style="text-align: left;"><?= htmlspecialchars($month . '-' . $year) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Registration No:</th>
                <td colspan="28" style="text-align: left;">-</td>
            </tr>

            <tr>
                <th rowspan="3">Name</th>
                <th rowspan="3">Code</th>
                <th rowspan="3">Sex</th>
                <th rowspan="3">Age</th>
                <?php foreach ($orderedDays as $day): ?>
                    <th colspan="4">Hours worked on <?= $day ?></th>
                <?php endforeach; ?>
            </tr>

            <tr>
                <?php foreach ($orderedDays as $day): ?>
                    <td colspan="4" class="date-list"><?= implode(', ', $weekDates[$day]) ?></td>
                <?php endforeach; ?>
            </tr>

            <tr>
                <?php foreach ($orderedDays as $day): ?>
                    <th>Time at which employment commences</th>
                    <th>Time at which employment ceases</th>
                    <th>Interval for rest</th>
                    <th>Signature of the employee</th>
                <?php endforeach; ?>
            </tr>

            <?php if (!empty($stateData)): foreach ($stateData as $row): ?>
                 <?php
                $dob = $row['date_of_birth'] ?? '';
                $age = '';

                if (!empty($dob)) {
                    // Create DOB from your DB format (d-M-y, e.g., 20-Jan-04)
                    $dobDate = DateTime::createFromFormat('d-M-y', $dob);

                    if ($dobDate) {
                        // ✅ Get last day of the selected month & year
                        $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year); 
                        $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);

                        if ($referenceDate) {
                            $age = $dobDate->diff($referenceDate)->y;
                        }
                    }
                }
            ?>

                    <tr>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($age) ?></td>
                        <?php foreach ($orderedDays as $day): ?>
                            <?php
                            // Collect values for this weekday from day_1..day_31
                            $dayValues = [];
                            foreach ($weekDates[$day] as $dayNum => $dateText) {
                                $col = 'day_' . $dayNum;
                                $dayValues[] = htmlspecialchars($row[$col] ?? '');
                            }
                            $displayVal = implode(' | ', array_filter($dayValues));
                            ?>
                            <td colspan="3"><?= $displayVal ?></td>
                            <td></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="32" style="text-align:center;">No data available</td>
                </tr>
            <?php endif; ?>
            <!-- Notes section -->
            <tr>
                <td colspan="32" class="note">
                    Note:
                </td>
            </tr>
            <tr>
                <td colspan="32" class="note">
                    (i) The mark 'H' shall be made in the column relating to any day on which a holiday is given in accordance with the Notices referred to in Rule 19(1) or 20(1)
                </td>
            </tr>
            <tr>
                <td colspan="32" class="note">
                    (ii) The mark 'A' shall be made if an employee is absent on any day.
                </td>
            </tr>
            <tr>
                <td colspan="32" class="note">
                    (iii) The entries under the heading 'intervals for rest' shall be actual hours at which the intervals began and ended.
                </td>
            </tr>
            <tr>
                <td colspan="32" class="note">
                    (iv) The mark 'SL' shall be in the column relating to any day on which the employer is allowed special leave of absence in the year for the purpose of attending religious ceremonies or functions.
                </td>
            </tr>
            <tr>
                <td colspan="32" class="note">
                    (v) The mark 'CH' shall be made in the column relating in any day on which the employee was allowed Compensatory Holiday as per Government orders, if any.
                </td>
            </tr>
        </table>
    </div>
</body>

</html>