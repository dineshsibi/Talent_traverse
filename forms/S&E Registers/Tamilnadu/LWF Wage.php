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
$currentState = 'Tamilnadu'; // Hardcoded for this state template

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
    
    // Calculate the required values
    $employeeCount = count($stateData);
    
    $totalGrossWages = 0;
    $totalDeductions = 0;
    $totalNetPay = 0;
    
    foreach ($stateData as $row) {
        $totalGrossWages += floatval($row['gross_wages'] ?? 0);
        $totalDeductions += floatval($row['total_deduction'] ?? 0);
        $totalNetPay += floatval($row['net_pay'] ?? 0);
    }
    
    // Calculate balance due (should be 0 if all payments were made correctly)
    
    $first_row = !empty($stateData) ? reset($stateData) : [];

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');

    $branch_address = $first_row['branch_address'] ?? '';
    $employer_name = $first_row['employer_name'] ?? '';
    $employer_address = $first_row['employer_address'] ?? '';
    $nature_of_business = safe($first_row['nature_of_business'] ?? 'Not specified');

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
            background-color: #ffffffff;
        }
        .empty-row {
            height: 30px;
        }
        .label-cell {
            font-weight: bold;
        }
         * {
            font-family: "Times New Roman", Times, serif;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="6" class="form-header">
                FORM B<br>
                The Tamilnadu Labour Welfare Fund Act, 1972 & Rules, 1973 (See rule 29)<br>
                Register of Wages
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name of the establishment:</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">For the month of:</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th rowspan="2">Total number of employees</th>
            <th rowspan="2">Total emoluments payable during the month including Basic Wages, D.A., O.T., Bonus</th>
            <th colspan="2">Amounts deducted during the month</th>
            <th rowspan="2">Amount actually paid during the month</th>
            <th rowspan="2">Balance due to the employees</th>
        </tr>
        <tr>
            <th>Fine</th>
            <th>Other deductions</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
        </tr>
        <tr>
            <td><?= $employeeCount ?></td>
            <td><?= number_format($totalGrossWages, 2) ?></td>
            <td>0</td>
            <td><?= number_format($totalDeductions, 2) ?></td>
            <td><?= number_format($totalNetPay, 2) ?></td>
            <td>0</td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Date:</th>
            <th colspan="4" style="text-align: right;">Signature of the Employer</th>
        </tr>
    </table>
</body>
</html>