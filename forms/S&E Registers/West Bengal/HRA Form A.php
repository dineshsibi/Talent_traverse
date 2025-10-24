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


    // Employer data with array handling
    $employer_address = safe($first_row['employer_address'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');

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
            <th colspan="8" class="title">
                FORM A <br>
                The West Bengal Workmen's House-rent Allowance Act, 1974 and Rules, 1975, See Rules 20 (1) and 21 (2) <br>
                Register of House-rent Allowance
            </th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">1. Name of the Industry</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name )?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">2. Name of the employer</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($employer_name)?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">3. Address in full</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($branch_address )?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">4. Month and year to which the house-rent allowance relates</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
        </tr>
        <tr>
            <th>Serial No.</th>
            <th>Employee code</th>
            <th>Names of Workmen</th>
            <th>Wages for the month for which house-rent allowance is payable</th>
            <th>House-rent allowance paid</th>
            <th>Mode of payment</th>
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

        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
        
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($month .' - '. $year)?></td>
            <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
            <td>Bank Transfer</td>
            <td></td>
            <td></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="25" class="no-data">No contractor data available for West Bengal</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>