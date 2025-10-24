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
$currentState = 'Andhra Pradesh'; // Hardcoded for this state template

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
        .form-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
        }
        .form-title {
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
                <th colspan="12" class="form-header">
                    Form XXIII<br>
                    The Andhra Pradesh Shops and Establishments Act, 1988 and Rules, 1990 [See Rule 29(2)]<br>
                    Register of Wages
                </th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name of the Establishment/Shop</td>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name)?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Address </th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($branch_address)?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Registration No</th>
                <td colspan="6" style="text-align: left;">-</td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Wage period</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month .' - ' . $year)?></td>
            </tr>
            <tr>
                <th>Sl No</th>
                <th>Employee Code</th>
                <th>Name of the Employee</th>
                <th>Date of appointment</th>
                <th>Rate of wages</th>
                <th>Normal wages earned</th>
                <th>Wages earned for overtime work</th>
                <th>Gross wage payable</th>
                <th>Deductions if any and reasons therof</th>
                <th>Actual wages paid</th>
                <th>Date of Payment</th>
                <th>Signature or thumb impression of the employee</th>
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
                <td><?= htmlspecialchars($row['fixed_other_allowance'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                <td></td>
            </tr>
             <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No data available for Andhra Pradesh</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>