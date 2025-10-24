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
$currentState = 'Andaman and Nicobar'; // Hardcoded for this state template

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
    $location = safe($first_row['location_name'] ?? '');

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
            <th colspan="11" class="form-header">
                Form VI <br>
                The Andaman and Nicobar Islands Shops and Establishments Rules, 2005, [Rule 11(6)] <br>
                Register of Employees 											
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name  of Shop/Establishment</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($first_row['client_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Place</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($location) ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Wage Period</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>Sl. No</th>
            <th>Name of Employee</th>
            <th>Employee Code</th>
            <th>Father’s/Husband’s Name</th>
            <th>Age and Sex</th>
            <th>Nature of Employment</th>
            <th>Home Address of employee(village Thana & District ) </th>
            <th>Date of Commencement of Employment</th>
            <th>Date of termination or leaving of Employment</th>
            <th>Signature or thumb impression of Employee </th>
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
            ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($age) ?> & <?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
            <td></td>
            <td></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="11" style="text-align:center;">No employee data found for Andaman and Nicobar</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>
