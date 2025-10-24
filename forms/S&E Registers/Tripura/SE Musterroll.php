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

    // Calculate total hours for each employee
    foreach ($stateData as &$row) {
        $totalHours = 0;
        // Sum values from day_1 to day_31
        for ($day = 1; $day <= 31; $day++) {
            $dayColumn = 'day_' . $day;
            if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                $totalHours += (float)$row[$dayColumn];
            }
        }
        $row['total_hours'] = $totalHours;
    }
    unset($row); // Break the reference

    $first_row = !empty($stateData) ? reset($stateData) : [];

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');


    $location_name = $first_row['location_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';
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
        }
        .form-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
        }
        .form-tittle {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-header {
            margin-bottom: 20px;
        }
        .form-header div {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffff;
            text-align: center;
        }
        .address {
            margin-left: 20px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="9" class="form-tittle">
                    FORM І <br>
                    The Tripura Shops and Establishments Rules, 1970, [See Rule 13] <br>
                    Register of daily hours of work and rest intervals of persons employed
                </th>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Name of shop/establishment</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , '. ($first_row['branch_address'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Name of shop-keeper/employer</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Address in full</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($first_row['employer_address'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;"> Registration No</th>
                <td colspan="2" style="text-align: left;">-</td>
                <th style="text-align: left;" colspan="2">Month</th>
                <td style="text-align: left;" colspan="2"><?= htmlspecialchars($month . ' - ' . $year)?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;"> Shop/establishment opens at</th>
                <td colspan="2" style="text-align: left;">-</td>
                <th style="text-align: left;" colspan="2">closes at</th>
                <td style="text-align: left;" colspan="2">-</td>
            </tr>
            <tr>
                <th rowspan="2">Sl. No</th>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Names of persons employed</th>
                <th rowspan="2">Employment commences</th>
                <th colspan="2">Interval for rest</th>
                <th rowspan="2">Employment ceases</th>
                <th rowspan="2">Total  hours work</th>
                <th rowspan="2">Signature of the person employed</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th colspan="2">5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td colspan="2"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
            <td>8</td>
            <td></td>
        </tr>
            <?php endforeach; ?>
                <?php else: ?>
        <tr>
            <td colspan="9" style="text-align:center;">No data available for Tripura</td>
        </tr>
            <?php endif; ?>
        <tr>
            <th colspan="9" style="text-align: right;">Signature of the shop-keeper/employer</th>
        </tr>
    </tbody>
    </table>
</body>
</html>