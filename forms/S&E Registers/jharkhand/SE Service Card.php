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
$currentState = 'Jharkhand'; // Hardcoded for this state template

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
        .address {
            margin-left: 20px;
            line-height: 1.4;
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
        .employee-info {
            margin-bottom: 15px;
        }
        .employee-info div {
            margin-bottom: 5px;
            display: flex;
        }
        .employee-info label {
            width: 250px;
            font-weight: bold;
        }
        
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <td class="form-title" colspan="15">
                    Form XXI<br>
                    The Jharkhand Shops and Establishments Rules, 2001 (See Rule 12-A)<br>
                    Service Card to Employee
                </td>
            </tr>
            <tr>
                <th colspan="8" style="text-align: left;">Name and address of the Establishment </th>
                <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name.' , '. $first_row['branch_address']??'')?></td>
            </tr>
            <tr>
                <th colspan="8" style="text-align: left;">Registration No </th>
                <td colspan="7" style="text-align: left;">-</td>
            <tr>    
                <th colspan="8" style="text-align: left;">Month & Year </th>
                <td colspan="7" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
            </tr>
            <tr>
                <th colspan="8">Personal particulars of employees</th>
                <th colspan="3">Nature of work with wages at particular date</th>
                <th rowspan="2">Signature or thumb impression of the employees</th>
                <th rowspan="2">Signature of Inspection Officer</th>
                <th rowspan="2">Signature of Inspecting Officer</th>
                <th rowspan="2">Remarks</th>
        </tr>
            <tr>
                <th>Sl No</th>
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Father's Name</th>
                <th>Permanent Address</th>
                <th>Local Address</th>
                <th>Age at the time of Appointment</th>
                <th>Date of Appointment</th>
                <th>Designation</th>
                <th>With effect from</th>
                <th>Rate of Wages</th>
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
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_code']??'')?></td>
                <td><?= htmlspecialchars($row['employee_name']??'')?></td>
                <td><?= htmlspecialchars($row['father_name']??'')?></td>
                <td><?= htmlspecialchars($row['present_address']??'')?></td>
                <td><?= htmlspecialchars($row['present_address']??'')?></td>
                <td><?= htmlspecialchars($row['date_of_birth']??'')?></td>
                <td><?= htmlspecialchars($row['date_of_joining']??'')?></td>
                <td><?= htmlspecialchars($row['designation']??'')?></td>
                <td><?= htmlspecialchars($row['date_of_joining']??'')?></td>
                <td><?= htmlspecialchars($row['fixed_gross']??'')?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="15" class="no-data">No contractor data available for Jharkhand</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>