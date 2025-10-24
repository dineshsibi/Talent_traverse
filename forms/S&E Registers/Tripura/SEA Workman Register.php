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
            <th colspan="15" class="form-header">
                FORM U <br>
                The Tripura Shops and Establishments Rules, 1970, (See rule 44) <br>
                REGISTER OF EMPLOYEES 											
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name of Shop/Establishment</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($first_row['client_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Address</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($first_row['branch_address'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name of Shop-keeper/employer</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Registration. No</th>
            <td colspan="9" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Day/Week/Month (in accordance with mode of payment & year) :</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>Sl. No</th>
            <th>Name of the person employed</th>
            <th>Employee Code</th>
            <th>Father’s name of the person employed (in the case of married woman husband’s name)</th>
            <th>Date of Birth of the person employed</th>
            <th>Date of appointment</th>
            <th>Post held or nature of job performed</th>
            <th>Scale of pay, if any</th>
            <th>Rate of Increment</th>
            <th>Basic Pay</th>
            <th>Dearness Allowances</th>
            <th>Other Allowances</th>
            <th>Total wages day/week/month (according to mode of pay).</th>
            <th>Remarks</th>
            <th>Signature of the person employed with date thereof</th>
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
            <th>13</th>
            <th>14</th>
            <th>15</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($stateData)): ?>
        <?php $i = 1; foreach ($stateData as $row): ?>
            <?php
            $gross = (float)($row['gross_wages'] ?? 0);
            $basic = (float)($row['basic'] ?? 0);
            $da = (float)($row['da'] ?? 0);
            $other_allowance = $gross - ( $basic + $da )
            ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
            <td>-</td>
            <td>-</td>
            <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            <td><?= htmlspecialchars($other_allowance) ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td></td>
            <td></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="15" style="text-align:center;">No employee data found for Tripura</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>
