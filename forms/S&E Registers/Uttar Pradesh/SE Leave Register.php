<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__ . '/../../../includes/config.php';
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
$currentState = 'Uttar Pradesh'; // Hardcoded for this state template

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
    $location = safe($first_row['location_name'] ?? '');
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #ffffff;
        }
        .header {
            font-weight: bold;
            text-align: center;
        }
        .title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            font-style: italic;
            margin-bottom: 20px;
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
            <td class="title" colspan="15">
                Form "H" <br>
                (Uttar Pradesh Dookan Aur Vanijya Adhishthan Niyamavali, 1963) {See Rule 18(1)(b) and ( C)}<br>
                Register of leave
            </td>
        </tr>
        <tr>
            <th colspan="3">Name of the establishment</th>
            <td colspan="4"><?= htmlspecialchars($client_name . ' , '. ($first_row['branch_address'] ?? '')) ?></td>
            <th colspan="4">Month</th>
            <td colspan="4"><?= htmlspecialchars($month . ' - ' . $year)?></td>
        </tr>
        <tr>
            <th colspan="2">Employee Code</th>
            <td colspan="2"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <th colspan="2">Name of employee</th>
            <td colspan="3"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <th colspan="2">Nature of Employment</th>
            <td colspan="4"><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
        <tr>
            <th colspan="2">Date of employment</th>
            <td colspan="2"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <th colspan="2">Father's name</th>
            <td colspan="3"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <th colspan="2">Date of Birth</th>
            <td colspan="4"><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
        </tr>
        <tr>
            <th rowspan="3">Sl. No</th>
            <th colspan="4">Earned Leave</th>
            <th colspan="5">Sickness leave</th>
            <th colspan="4">Casual leave</th>
            <th rowspan="3">Signature of Employer</th>
        </tr>
        <tr>
            <th rowspan="2">Balance carried forward</th>
            <th rowspan="2">Date on which leave applied for</th>
            <th colspan="2">Date of availing leave</th>
            <th rowspan="2">Balance Due</th>
            <th colspan="2">Date of availing leave</th>
            <th rowspan="2">Balance Due</th>
            <th rowspan="2">Date of application</th>
            <th rowspan="2">Whether application granted or refused</th>
            <th colspan="2">Date of availing leave</th>
            <th rowspan="2">Balance due</th>
        </tr>
        <tr>
            <th>From</th>
            <th>To</th>
            <th>From</th>
            <th>To</th>
            <th>From</th>
            <th>To</th>
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
        <tr>
            <td><?= $currentEmployee ?></td>
            <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>       
            <td><?= $plDaysDisplay ?></td>
            <td colspan="2"><?= $plDaysDisplay ?></td>
            <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
            <td colspan="2"><?= $slDaysDisplay ?></td>
            <td><?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
            <td><?= $slDaysDisplay ?></td>
            <td>Granted</td>
            <td colspan="2"><?= $clDaysDisplay ?></td>
            <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
            <td></td>
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