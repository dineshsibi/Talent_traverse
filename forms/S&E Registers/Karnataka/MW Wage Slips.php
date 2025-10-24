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
    $location=safe($first_row['location_name']??'');

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
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="10" class="title">FORM VI<br>The Karnataka Minimum Wages Rules, 1958 [See Rule 29(2)]<br>Wage Slips</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name & Address of the establishment</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Place</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($location) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month & Year :</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month .' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>S. No</th>
            <th>Employee Code</th>
            <th>Name of the worker</th>
            <th>Wage period</th>
            <th>Minimum rates of wages payable</th>
            <th>Dates on which overtime worked</th>
            <th>Gross wages payable</th>
            <th>Deductions, if any</th>
            <th>Actual wages paid</th>
            <th>Signature of the employee</th>
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
        </tr>
        <tbody>
             <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>

            <?php
                // Create array to store days with overtime
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
            <td><?= htmlspecialchars(($row['month'] ?? '') . ' - ' . ($row['year'] ?? '')) ?></td>
            <td>As Per Act</td>
            <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td></td>
        </tr>
         <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <th colspan="19" style="text-align:center;">No data available for Karnataka</th>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>