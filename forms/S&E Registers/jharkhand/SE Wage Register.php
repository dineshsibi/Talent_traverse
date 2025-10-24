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
$currentState = 'Jharkhand'; // Hardcoded for this state template

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
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .empty-row {
            height: 25px;
        }
        .left-align {
            text-align: left;
        }
        .colspan-full {
            width: 100%;
        }
        .signature-cell {
            height: 30px;
            vertical-align: bottom;
        }
        .sub-header {
            font-weight: normal;
        }
        
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <td class="form-header" colspan="14">
                   FORM X <br>
                    The Jharkhand Shops and Establishments Rules, 2001 [ Rule 17] <br>
                    Register of Wages and Overtime payment										

                </td>
            </tr>
            <tr>
                <td style="text-align: left;" colspan="6"><b>Name and Address of the Establishment :</td> 
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address)?></td>
            </tr>
            <tr>
                <td style="text-align: left;" colspan="6"><b>Month & Year :</td>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year)?></td>
            </tr>
            <tr>
                <th>S. No</th>
                <th>Name of the Employee</th>
                <th>Date on Which <br>Overtime Worked</th>
                <th>Extend of Overtime<br>on each Occasion</th>
                <th>Total Overtime<br>Worked</th>
                <th>Wage Period</th>
                <th>Rate of Wages<br>Payable</th>
                <th>Total Overtime<br>earning during<br>the Wage Period</th>
                <th>Gross Wage<br>Payable</th>
                <th>Deductions,<br>if any</th>
                <th>Actual Wages<br>Paid</th>
                <th>Signature or <br>thumb impression<br> of employee</th>
                <th>Signature of<br> employer or any<br> person authorized <br>by him</th>
            </tr>
        </thead>
        <tbody>
             <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): 
                // Get all dates where overtime was worked (hours > 8)
                $overtimeDates = [];
                for ($day = 1; $day <= 31; $day++) {
                    $dayColumn = 'day' . $day;
                    if (isset($row[$dayColumn]) && $row[$dayColumn] > 8) {
                        $overtimeDates[] = $day; // Store day number
                    }
                }
                $datesString = implode(', ', $overtimeDates);

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
                    <td class="left-align"> <?= htmlspecialchars($overtimeDaysStr) ?></td>
                    <td class="left-align"><?= htmlspecialchars($row['extent_ot_on_which_occasion']?? '')?></td>
                    <td><?= htmlspecialchars($row['ot_hours']?? '')?></td>
                    <td><?= htmlspecialchars(($row['month'] ?? '') . ' & ' . ($row['year'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($row['date_of_leaving']?? '')?></td>
                    <td><?= htmlspecialchars($row['over_time_allowance']?? '')?></td>
                    <td><?= htmlspecialchars($row['gross_wages']?? '')?></td>
                    <td><?= htmlspecialchars($row['total_deduction']?? '')?></td>
                    <td><?= htmlspecialchars($row['net_pay']?? '')?></td>
                    <td class="signature-cell"></td>
                    <td class="signature-cell"></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="no-data">No contractor data available for Jharkhand</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>