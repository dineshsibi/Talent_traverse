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
$currentState = 'Delhi'; // Hardcoded for this state template

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

    $location=$first_row['location_name'] ?? '';

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
        .total-row {
            font-weight: bold;
        }
        
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="10">
                    Form X <br>
                    The Delhi Minimum Wages Rules, 1950 [See Rule 26(1) ]<br>
                    Register of Wages
                </th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Name and address of the Establishment</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Place </th>
                <td colspan="5" style="text-align: left;"><?= $location ?></td>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Month & Year </th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>S.No</th>
                <th>Employee Code</th>
                <th>Name of the Worker</th>
                <th>Wages Period</th>
                <th>Minimum Rates of Wages Payable</th>
                <th>Dates on Which<br>Overtime Worked</th>
                <th>Gross<br>Wages<br>Payable</th>
                <th>Deductions if any</th>
                <th>Actual Wages Paid</th>
                <th>Signature or<br>thumb impression<br>of employee</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1; foreach ($stateData as $row): ?>
                    <?php
                
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
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['month'] ?? '') ?> - <?= htmlspecialchars($row['year'] ?? '') ?></td>
                <td>As Per Act</td>
                <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                <td class="signature-cell"></td>
            </tr> <?php endforeach; ?>
        <?php else: ?>
             <tr>
                <td class="no-data" colspan="7">No employee data available for Delhi</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>