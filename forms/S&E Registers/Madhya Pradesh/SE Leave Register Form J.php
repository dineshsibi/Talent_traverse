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
$currentState = 'Madhya Pradesh'; // Hardcoded for this state template

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

// Get one sample row to extract address
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
            margin: 0;
            padding: 20px;
        }
        .form-container {
            width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 12px;
            word-wrap: break-word;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .header-info {
            text-align: left;
            padding-left: 10px;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left top 0;
            white-space: nowrap;
            display: block;
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 100px;
            height: 20px;
        }
        .vertical-header {
            position: relative;
            height: 120px;
        }
        .vertical-header span {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%) rotate(-90deg);
            transform-origin: center;
            white-space: nowrap;
        }
         @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;
        
        // Get the month number from month name
        $monthNum = date('m', strtotime($month));
        
        // Process leave data to find PL, SL, and CL days
        $plDays = [];
        $slDays = [];
        $clDays = [];
        
        // Check all day fields (day_1 to day_31) for leave types
        for ($day = 1; $day <= 31; $day++) {
            $dayKey = 'day_' . $day;
            if (isset($row[$dayKey])) {
                if ($row[$dayKey] == 'PL') {
                    $plDays[] = $day;
                } elseif ($row[$dayKey] == 'SL') {
                    $slDays[] = $day;
                } elseif ($row[$dayKey] == 'CL') {
                    $clDays[] = $day;
                }
            }
        }
        
        // Format days for display (e.g., "5,6")
        $plDaysDisplay = !empty($plDays) ? implode(',', $plDays) : '';
        $slDaysDisplay = !empty($slDays) ? implode(',', $slDays) : '';
        $clDaysDisplay = !empty($clDays) ? implode(',', $clDays) : '';
        
        // Calculate leave counts
        $plCount = count($plDays);
        $slCount = count($slDays);
        $clCount = count($clDays);
    ?>
    <table>
        <tr>
            <th colspan="13" class="title">
            FORM J <br>
            The Madhya Pradesh Shops and Establishment Act, 1958 and Rules, 1959 [See Rule 13 ( 2 )] <br>
            Register of Leave											
											

            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name and Address of employer / establishment Account for the year</th>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name . ' , '. ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Employee Name</th>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($first_row['employee_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Employee ID</th>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($first_row['employee_code'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Occupation</th>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($first_row['designation'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Date of Entry in to service</th>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($first_row['date_of_joining'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Month/Year</th>
            <td colspan="8" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year)?></td>
        </tr>
        <tr>
            <th colspan="5">PRIVILEGE LEAVE</th>
            <th colspan="8">CASUAL LEAVE</th>
        </tr>
        <tr>
            <th>Acculated balance brought forward </th>
            <th colspan="2">Number of days Leave applied for</th>
            <th colspan="2">Leave granted</th>
            <th rowspan="2">Balance of carried</th>
            <th rowspan="2">If Leave refused amount of leave and date and reason of refusal</th>
            <th>Leave Salary Paid</th>
            <th colspan="2">Leave Salary paid to discharged Employee or on his quitting employment after having applied for and having been refused Leave</th>
            <th rowspan="2">Due Number of Days</th>
            <th rowspan="2">Availed (Number of Days with dates)</th>
            <th rowspan="2">Balance (Number of days)</th>
        </tr>
        <tr>
            <th>From previous year</th>
            <th>From (Date)</th>
            <th>To(Date)</th>
            <th>From (Date)</th>
            <th>To(Date)</th>
            <th>Advance of return</th>
            <th>Date of discharge etc</th>
            <th>Date and amount of paymet made in respect of leave</th>
        </tr>
        <tr>
            <td>1</td>
            <td colspan="2">2</td>
            <td colspan="2">3</td>
            <td>4</td>
            <td>5</td>
            <td>6</td>
            <td colspan="2">7</td>
            <td>8</td>
            <td>9</td>
            <td>10</td>
        </tr>
        <tr>
            <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>
            <td colspan="2"><?= $plDaysDisplay ?></td>
            <td colspan="2"><?= $plDaysDisplay ?></td>
            <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
            <td>-</td>
            <td>Nil</td>
            <td colspan="2">Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td><?= $clDaysDisplay ?></td>
        </tr>
    </table>
    <?php 
    // Add page break after each employee except the last one
    if ($currentEmployee < $totalEmployees) {
        echo '<div class="page-break"></div>';
    }
    ?>
    
    <?php endforeach; ?>
</body>
</html>