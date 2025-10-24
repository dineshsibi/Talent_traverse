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
$currentState = 'Pondicherry'; // Hardcoded for this state template

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

    $location_name = $first_row['location_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';

    // Calculate totals for each day column
    $dayTotals = array_fill(1, 31, 0);
    $grandTotal = 0;
    
    foreach ($stateData as $row) {
        for ($day = 1; $day <= 31; $day++) {
            $dayColumn = 'day_' . $day;
            if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                $dayTotals[$day] += (float)$row[$dayColumn];
            }
        }
        
        // Add to grand total if available
        if (isset($row['total_worked_days']) && is_numeric($row['total_worked_days'])) {
            $grandTotal += (float)$row['total_worked_days'];
        }
    }

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
        th, td {
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
        .left-align {
            text-align: left;
        }
        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            width: 20px;
        }
        .small-text {
            font-size: 10px;
        }
        @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <?php if (!empty($stateData)): ?>
        <table>
            <!-- Title Row -->
            <tr>
                <th colspan="50" class="title">
                    FORM VIII <br>
                    The Puducherry Shops and Establishments (Amendment) Rules, 2010, [See sub-rule (1) of rule 22] <br>
                    REGISTER OF EMPLOYMENT
                </th>
            </tr>
            
            <!-- Establishment Information -->
            <tr>
                <th colspan="10" class="left-align">Name and Address of the Establishment/Shop</th>
                <td colspan="15" class="left-align"><?= htmlspecialchars($client_name . ' , ' . $branch_address)?></td>
                <th colspan="10" class="left-align">For the month of</th>
                <td colspan="15" class="left-align"><?= htmlspecialchars($month . ' - ' . $year)?></td>
            </tr>
            
            <!-- Main Header Row -->
            <tr>
                <th rowspan="2">S.No</th>
                <th rowspan="2">Name of the employee</th>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Date of entry into service</th>
                <th rowspan="2">Age/date of birth</th>
                <th rowspan="2">Designation</th>
                <th colspan="3">Leave credit to the beginning of the month</th>
                <th colspan="3">Leave availed during the month</th>
                <th colspan="3">Leave balance</th>
                <th colspan="31" style="text-align:center;">ATTENDANCE ( Please mention the date of suspension of employees, if any)</th>
                <th rowspan="2">Total hours of overtime worked</th>
                <th rowspan="2">Total hours of work done during the month</th>
                <th rowspan="2">Total number of maternity leave availed</th>
            </tr>
            <tr>
                <th>Sick leave</th>
                <th>Casual leave</th>
                <th>Holidays with wages</th>
                <th>Sick leave</th>
                <th>Casual leave</th>
                <th>Holidays with wages</th>
                <th>Sick leave</th>
                <th>Casual leave</th>
                <th>Holidays with wages</th>
                <th>Day 1</th>
                <th>Day 2</th>
                <th>Day 3</th>
                <th>Day 4</th>
                <th>Day 5</th>
                <th>Day 6</th>
                <th>Day 7</th>
                <th>Day 8</th>
                <th>Day 9</th>
                <th>Day 10</th>
                <th>Day 11</th>
                <th>Day 12</th>
                <th>Day 13</th>
                <th>Day 14</th>
                <th>Day 15</th>
                <th>Day 16</th>
                <th>Day 17</th>
                <th>Day 18</th>
                <th>Day 19</th>
                <th>Day 20</th>
                <th>Day 21</th>
                <th>Day 22</th>
                <th>Day 23</th>
                <th>Day 24</th>
                <th>Day 25</th>
                <th>Day 26</th>
                <th>Day 27</th>
                <th>Day 28</th>
                <th>Day 29</th>
                <th>Day 30</th>
                <th>Day 31</th>
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
                <th colspan="31" style= "text-align:center;">16</th>
                <th>17</th>
                <th>18</th>
                <th>19</th>
            </tr>
            
            <?php 
            $i = 1; 
            foreach ($stateData as $row): 
                // Calculate total hours worked for this employee
                $totalHoursWorked = 0;
                for ($day = 1; $day <= 31; $day++) {
                    $dayColumn = 'day_' . $day;
                    if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                        $totalHoursWorked += (float)$row[$dayColumn];
                    }
                }
                
                $ot_hours = (float)($row['ot_hours'] ?? 0);
                $normal_rate = $ot_hours/26;
                $overtime_rate=  $ot_hours/26/ 4*2;
                
                $Gross= (float)($row['gross_wages'] ?? 0);
                $Overtime = (float)($row['over_time_allowance'] ?? 0);
                $normal_earnings=  $Gross - $Overtime;

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
                
                // Process leave data to find PL, SL, and CL days
                $plDays = [];
                $slDays = [];
                $clDays = [];
                $mlDays = [];
                
                // Check all day fields (day_1 to day_31) for leave types
                for ($day = 1; $day <= 31; $day++) {
                    $dayKey = 'day_' . $day;
                    if (isset($row[$dayKey])) {
                        if ($row[$dayKey] == 'PL') {
                            $plDays[] = $day;
                        } elseif ($row[$dayKey] == 'SL') {
                            $slDays[] = $day;
                        } elseif ($row[$dayKey] == 'CL') {
                            $clDays[] = $day;
                        } elseif ($row[$dayKey] == 'ML') {
                            $mlDays[] = $day;
                        }
                    }
                }
                
                // Calculate leave counts
                $mlCount = count($mlDays);
            ?>
                
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                    <td><?= htmlspecialchars($age) ?></td>
                    <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['sl_opening'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['cl_opening'] ?? '') ?></td>
                    <td>-</td>
                    <td><?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
                    <td>-</td>
                    <td><?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
                    <td>-</td>
                    
                    <?php for ($day = 1; $day <= 31; $day++): ?>
                        <td><?= htmlspecialchars($row['day_' . $day] ?? '') ?></td>
                    <?php endfor; ?>
                    
                    <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                    <td><?= $totalHoursWorked ?></td>
                    <td><?= $mlCount ?></td>
                </tr>
            <?php endforeach; ?>
                <tr>
                    <th colspan="25" style="text-align: left;">Date:</th>
                    <th colspan="25" style="text-align: right;">Authorised Signatory</th>
                </tr>
            </table>
        <?php else: ?>
                <tr>
                    <td colspan="50" style="text-align: center;">No data available for Puducherry</td>
                </tr>
        <?php endif; ?>
</body>
</html>