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
$currentState = 'Meghalaya'; // Changed to Mizoram for this form

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

    $employer_name = $first_row['employer_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';

    // Function to get day names for a given month and year
    function getDayNames($month, $year)
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dayNames = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $timestamp = mktime(0, 0, 0, $month, $day, $year);
            $dayNames[$day] = date('l', $timestamp);
        }

        return $dayNames;
    }

    $dayNames = getDayNames($month, $year);

    // Get dates for each day of the week
    $sundays = [];
    $mondays = [];
    $tuesdays = [];
    $wednesdays = [];
    $thursdays = [];
    $fridays = [];
    $saturdays = [];

    foreach ($dayNames as $day => $name) {
        $dateStr = sprintf("%02d.%02d.%04d", $day, $month, $year);

        switch ($name) {
            case 'Sunday':
                $sundays[] = $dateStr;
                break;
            case 'Monday':
                $mondays[] = $dateStr;
                break;
            case 'Tuesday':
                $tuesdays[] = $dateStr;
                break;
            case 'Wednesday':
                $wednesdays[] = $dateStr;
                break;
            case 'Thursday':
                $thursdays[] = $dateStr;
                break;
            case 'Friday':
                $fridays[] = $dateStr;
                break;
            case 'Saturday':
                $saturdays[] = $dateStr;
                break;
        }
    }

    // Determine the first day of the month to reorder columns
    $firstDayOfMonth = date('l', mktime(0, 0, 0, $month, 1, $year));

    // Create ordered days array based on the first day of the month
    $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Find the index of the first day in the standard week
    $firstDayIndex = array_search($firstDayOfMonth, $daysOfWeek);

    // Reorder the days array starting from the first day of the month
    $orderedDays = array_merge(
        array_slice($daysOfWeek, $firstDayIndex),
        array_slice($daysOfWeek, 0, $firstDayIndex)
    );
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Function to get day numbers for a specific day of the week
function getDayNumbersForDay($dayName, $month, $year)
{
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $dayNumbers = [];

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        if (date('l', $timestamp) === $dayName) {
            $dayNumbers[] = $day;
        }
    }

    return $dayNumbers;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }

        .form-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
        }

        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-header {
            margin-bottom: 10px;
        }

        .form-header div {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .col-small {
            width: 40px;
        }

        .col-medium {
            width: 60px;
        }

        .col-large {
            width: 80px;
        }

        .col-employee {
            width: 100px;
        }

        .notes {
            margin-top: 15px;
            font-size: 11px;
        }

        .text-left {
            text-align: left;
        }

        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
        }

        .combined-cell {
            text-align: left;
            padding: 2px;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-title" colspan="33">
                    FORM Q <br>
                    The Meghalaya Shops and Establishments Rules, 2004, [See Rule 51] <br>
                    Register of Hours of Work and Rest
                </th>
            </tr>
            <tr>
                <th class="text-left" colspan="13">Name of the Establishment</th>
                <td class="text-left" colspan="20"><?= htmlspecialchars($client_name) ?></td>
            </tr>
            <tr>
                <th class="text-left" colspan="13">Name of the Employer</th>
                <td class="text-left" colspan="20"><?= htmlspecialchars($employer_name) ?></td>
            </tr>
            <tr>
                <th class="text-left" colspan="13">Address</th>
                <td class="text-left" colspan="20"><?= htmlspecialchars($branch_address) ?></td>
            </tr>
            <tr>
                <th class="text-left" colspan="13">Month & Year</th>
                <td class="text-left" colspan="20"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th class="text-left" colspan="13">Registration No</th>
                <td class="text-left" colspan="20">-</td>
            </tr>
            <thead>
                <tr>
                    <th rowspan="3" class="col-small">Sl No</th>
                    <th rowspan="3" class="col-small">Name of the Employee</th>
                    <th rowspan="3" class="col-small">Employee Code</th>
                    <th rowspan="3" class="col-small">Sex</th>
                    <th rowspan="3" class="col-small">Age</th>

                    <?php
                    // Generate day headers in the correct order
                    foreach ($orderedDays as $day):
                        $dayVar = strtolower($day) . 's';
                        $datesList = implode(', ', $$dayVar);
                    ?>
                        <th colspan="4">Hours worked on <?= strtoupper($day) ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php
                    // Generate date lists in the correct order
                    foreach ($orderedDays as $day):
                        $dayVar = strtolower($day) . 's';
                        $datesList = implode(', ', $$dayVar);
                    ?>
                        <td colspan="4"><?= $datesList ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php
                    // Generate column headers for each day (3 time columns + 1 signature column)
                    for ($i = 0; $i < 7; $i++):
                    ?>
                        <th>Time at which employment commences</th>
                        <th>Time at which employment ceases</th>
                        <th>Interval for rest</th>
                        <th>Signature of the employee</th>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <!-- Column numbers -->
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <?php
                    // Generate column numbers for each day's fields
                    $colNum = 5;
                    for ($i = 0; $i < 7; $i++):
                    ?>
                        <th><?= $colNum++ ?></th>
                        <th><?= $colNum++ ?></th>
                        <th><?= $colNum++ ?></th>
                        <th><?= $colNum++ ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
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
                    } ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($age) ?></td>

                        <?php
                        // Generate data cells in the correct order
                        foreach ($orderedDays as $day):
                            $dayNumbers = getDayNumbersForDay($day, $month, $year);
                            $workDetails = [];

                            foreach ($dayNumbers as $dayNum) {
                                $dayColumn = 'day_' . $dayNum;
                                if (!empty($row[$dayColumn])) {
                                    $workDetails[] = $row[$dayColumn];
                                }
                            }

                            $workDetailsStr = implode(', ', $workDetails);
                        ?>
                            <!-- Combined data cell spanning 3 columns -->
                            <td colspan="3" class="combined-cell"><?= htmlspecialchars($workDetailsStr) ?></td>
                            <td></td> <!-- Signature column -->
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="33" class="text-left">Notes:</th>
                </tr>
                <tr>
                    <th colspan="33" class="text-left">(i) The mark 'H' shall be made in the column relating to any day on which a holiday is given in accordance with the Notices referred to in Rule 19(1) or 20(1)</th>
                </tr>
                <tr>
                    <th colspan="33" class="text-left">(ii) The mark 'A' shall be made if an employee is absent on any day.</th>
                </tr>
                <tr>
                    <th colspan="33" class="text-left">(iii) The entries under the heading 'intervals for rest' shall be actual hours at which the intervals began and ended.</th>
                </tr>
                <tr>
                    <th colspan="33" class="text-left">(iv) The mark 'SL' shall be in the column relating to any day on which the employer is allowed special leave of absence in the year for the purpose of attending religious ceremonies or functions.</th>
                </tr>
                <tr>
                    <th colspan="33" class="text-left">(v) The mark 'CH' shall be made in the column relating in any day on which the employee was allowed Compensatory Holiday as per Government orders, if any.</th>
                </tr>
                <tr>
                    <th class="text-left" colspan="33">Signature of the employer:</th>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="33" style="text-align:center;">No data available for Meghalaya</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>