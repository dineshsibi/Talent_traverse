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
$pdo = require($configPath);

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Assam';

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

    // DIFFERENT function names but SAME functionality as Meghalaya
    function getAssamDayNames($month, $year)
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dayNames = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $timestamp = mktime(0, 0, 0, $month, $day, $year);
            $dayNames[$day] = date('l', $timestamp);
        }

        return $dayNames;
    }

    $assamDayNames = getAssamDayNames($month, $year);

    // Get dates for each day of the week (DIFFERENT variable names)
    $assamSundays = [];
    $assamMondays = [];
    $assamTuesdays = [];
    $assamWednesdays = [];
    $assamThursdays = [];
    $assamFridays = [];
    $assamSaturdays = [];

    foreach ($assamDayNames as $day => $name) {
        $dateStr = sprintf("%02d.%02d.%04d", $day, $month, $year);

        switch ($name) {
            case 'Sunday': $assamSundays[] = $dateStr; break;
            case 'Monday': $assamMondays[] = $dateStr; break;
            case 'Tuesday': $assamTuesdays[] = $dateStr; break;
            case 'Wednesday': $assamWednesdays[] = $dateStr; break;
            case 'Thursday': $assamThursdays[] = $dateStr; break;
            case 'Friday': $assamFridays[] = $dateStr; break;
            case 'Saturday': $assamSaturdays[] = $dateStr; break;
        }
    }

    // Determine the first day of the month to reorder columns (DIFFERENT function name)
    function getAssamOrderedDays($month, $year)
    {
        $firstDayOfMonth = date('l', mktime(0, 0, 0, $month, 1, $year));
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $firstDayIndex = array_search($firstDayOfMonth, $daysOfWeek);
        return array_merge(
            array_slice($daysOfWeek, $firstDayIndex),
            array_slice($daysOfWeek, 0, $firstDayIndex)
        );
    }

    $assamOrderedDays = getAssamOrderedDays($month, $year);

    // Function to get day numbers for a specific day of the week (DIFFERENT function name)
    function getAssamDayNumbers($dayName, $month, $year)
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
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            word-wrap: break-word;
        }
        th {
            font-weight: bold;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 6px;
        }
        .text-left {
            text-align: left;
        }
        .date-list {
            font-size: 9px;
            line-height: 1.2;
        }
        .combined-cell {
            text-align: left;
            padding: 2px;
        }
        .col-small { 
            width: 40px; 
        }
    </style>
</head>
<body>
    <table>
        <!-- Main Title -->
        <tr>
            <th colspan="33" class="form-title">
                Form K <br>
                The Assam Shops and Establishments Act, 1971 and Rules, 1976. [See Rule 39] <br>
                Register of Hours or Work and Interval for Rest
            </th>
        </tr>
        
        <!-- Establishment Info -->
        <tr>
            <th class="text-left" colspan="13">Name of the Establishment</th>
            <td class="text-left" colspan="20"><?= htmlspecialchars($client_name) ?></td>
        </tr>
        <tr>
            <th class="text-left" colspan="13">Name of the Employer</th>
            <td class="text-left" colspan="20"><?= htmlspecialchars($employer) ?></td>
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

        <!-- Table Headers -->
        <tr>
            <th rowspan="3" class="col-small">Sl No</th>
            <th rowspan="3" class="col-small">Name of the Employee</th>
            <th rowspan="3" class="col-small">Employee Code</th>
            <th rowspan="3" class="col-small">Sex</th>
            <th rowspan="3" class="col-small">Age</th>

            <?php foreach ($assamOrderedDays as $day): ?>
                <th colspan="4">Hours worked on <?= $day ?></th>
            <?php endforeach; ?>
        </tr>
        
        <tr>
            <?php foreach ($assamOrderedDays as $day): ?>
                <?php
                $dayVar = 'assam' . ucfirst(strtolower($day)) . 's'; // Different variable names
                $datesList = implode(', ', $$dayVar);
                ?>
                <td colspan="4" class="date-list"><?= $datesList ?></td>
            <?php endforeach; ?>
        </tr>
        
        <tr>
            <?php for ($i = 0; $i < 7; $i++): ?>
                <th>Time at which employment commences</th>
                <th>Time at which employment ceases</th>
                <th>Interval for rest</th>
                <th>Signature of the employee</th>
            <?php endfor; ?>
        </tr>
        
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <?php
            $colNum = 5;
            for ($i = 0; $i < 7; $i++):
            ?>
                <th><?= $colNum++ ?></th>
                <th><?= $colNum++ ?></th>
                <th><?= $colNum++ ?></th>
                <th><?= $colNum++ ?></th>
            <?php endfor; ?>
        </tr>

        <!-- Data Rows -->
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1; foreach ($stateData as $row): ?>
                    <?php
                    $dob = $row['date_of_birth'] ?? '';
                    $age = '';
                    if (!empty($dob)) {
                        $dobDate = DateTime::createFromFormat('d-M-y', $dob);
                        if ($dobDate) {
                            $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                            $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);
                            if ($referenceDate) {
                                $age = $dobDate->diff($referenceDate)->y;
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($age) ?></td>

                        <?php foreach ($assamOrderedDays as $day): ?>
                            <?php
                            $dayNumbers = getAssamDayNumbers($day, $month, $year); // Different function name
                            $workDetails = [];
                            foreach ($dayNumbers as $dayNum) {
                                $dayColumn = 'day_' . $dayNum;
                                if (!empty($row[$dayColumn])) {
                                    $workDetails[] = $row[$dayColumn];
                                }
                            }
                            $workDetailsStr = implode(', ', $workDetails);
                            ?>
                            <td colspan="3" class="combined-cell"><?= htmlspecialchars($workDetailsStr) ?></td>
                            <td></td> <!-- Signature column -->
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                
                <!-- Notes Section -->
                <tr>
                    <th colspan="33" class="text-left">Notes:</th>
                </tr>
                <tr>
                    <td colspan="33" class="text-left">(i) The mark 'H' shall be made in the column relating to any day on which a holiday is given in accordance with the Notices referred to in Rule 19(1) or 20(1)</td>
                </tr>
                <tr>
                    <td colspan="33" class="text-left">(ii) The mark 'A' shall be made if an employee is absent on any day.</td>
                </tr>
                <tr>
                    <td colspan="33" class="text-left">(iii) The entries under the heading 'intervals for rest' shall be actual hours at which the intervals began and ended.</td>
                </tr>
                <tr>
                    <td colspan="33" class="text-left">(iv) The mark 'SL' shall be in the column relating to any day on which the employer is allowed special leave of absence in the year for the purpose of attending religious ceremonies or functions.</td>
                </tr>
                <tr>
                    <td colspan="33" class="text-left">(v) The mark 'CH' shall be made in the column relating in any day on which the employee was allowed Compensatory Holiday as per Government orders, if any.</td>
                </tr>
                <tr>
                    <th class="text-left" colspan="33">Signature of the employer:</th>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="33" style="text-align:center;">No data available for <?= $currentState ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>