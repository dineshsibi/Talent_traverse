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
$currentState = 'West Bengal'; // Hardcoded for this state template

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
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
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
        }
        .empty-row {
            height: 30px;
        }
        .colspan-10 {
            width: 100%;
        }
        *{
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
     <table>
        <thead>
            <tr>
                <td class="form-header" colspan="11">
                    Form J <br>
                    The West Bengal Workmen's House-rent Allowance Act, 1974 and Rules, 1975, See Rules 20 (1) and 21 (2) <br>
                    Register of House-rent Allowance
                </td>
            </tr>
            <tr>
                <th colspan="4">Name of the Industry</th>
                <td colspan="7"><?= htmlspecialchars($first_row['client_name'] ?? '') ?></td>
            </tr>
           
            <tr>
                <th colspan="4">Address</th>
                <td colspan="7"><?= htmlspecialchars($first_row['branch_address'] ?? '') ?></td>
            </tr>
       
            <tr>
                <th>Sl. No</th>
                <th>Names of workmen</th>
                <th>Dates of appointment</th>
                <th>Post held or nature of work performed</th>
                <th>Scale of pay, if any</th>
                <th>Dearness allowance</th>
                <th>Others allowance</th>
                <th>Mode of payment daily/weekly/monthly etc.</th>
                <th>Dates of each month on which the workman worked and/or earned wages</th>
                <th>Signature of workmen</th>
                <th>Remarks</th>
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
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
        <?php $i = 1; foreach ($stateData as $row): ?>
            <?php
                $da = (float)($row['da'] ?? 0);
                $gross = (float)($row['gross_wages'] ?? 0);
                $other_allowance = $gross - $da
                ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
                <td>Nil</td>
                <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                <td><?= htmlspecialchars($other_allowance) ?></td>
                <td>Monthly</td>
                <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                <td></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="11" style="text-align:center;">No employee data found for West Bengal</td>
        </tr>
    <?php endif; ?>
        </tbody>
    </table>
</body>
</html>