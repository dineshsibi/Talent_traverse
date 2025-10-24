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
$currentState = 'Tripura'; // Hardcoded for this state template

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 10px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            word-wrap: break-word;
            vertical-align: top;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>

<table>
    <thead>
        <tr>
            <th colspan="6" class="form-header">
                FORM S <br>
                The Tripura Shops and Establishments Rules, 1970, (See rule 32) <br>
                REGISTER OF OVERTIME WORK 											
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name and Address of Shop/Establishment, if any</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name of Shop-keeper/employer</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Registration. No</th>
            <td colspan="4" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">For The Month of</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>Sl. No</th>
            <th>Name of person employed</th>
            <th>Employee Code</th>
            <th>Dates on which overtime work is done</th>
            <th>Extent of overtime work on each such date</th>
            <th>Total of overtime work done during the month</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
        </tr>
    </thead>
    <?php if (!empty($stateData)): ?>
        <?php $i = 1; foreach ($stateData as $row): ?>
            <?php  // ✅ Reset overtime days for each employee
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
    <tbody>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
            <td><?= htmlspecialchars($row['extent_ot_on_which_occasion'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align:center;">No employee data found for Tripura</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
