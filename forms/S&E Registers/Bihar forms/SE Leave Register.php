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
$currentState = 'Bihar'; // Hardcoded for this state template

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
$employer_name = $first_row['employer_name'] ?? '';
$branch_address = safe($first_row['branch_address'] ?? '');

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
            width: 100%;
            border-collapse: collapse;
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
            background-color: #ffffffff;
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .subtitle {
            text-align: center;
            font-size: 12px;
        }
        .header-info {
            margin-bottom: 15px;
        }
        .header-info div {
            margin-bottom: 5px;
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
    </style>
</head>
<body>
    <table>
            <tr>
                <th colspan="10" class="title">
                    Form IX <br>
                    The Bihar Shops And Establishments Rules, 1955, [ Rule 14. ] <br>
                    Leave With Wages Register.
                </th>
            </tr>
            
            <tr>
                <th colspan="4" style="text-align: left;">Name and Address of Shop/Establishment, if any</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name .' , '. $branch_address)?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Month & Year</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
            </tr>
            
            <!-- Main Header Row -->
            <tr>
                <th rowspan="3">Sl. No</th>
                <th rowspan="3">Employee Code</th>
                <th rowspan="3">Name of Employee</th>
                <th rowspan="3">Whether adult or child</th>
                <th colspan="3">Date on which leave</th>
                <th rowspan="3">Nature of leave</th>
                <th rowspan="3">Total leave taken during the year.</th>
                <th rowspan="3">Balance carried over</th>
            </tr>
            <tr>
                <th rowspan="2">Applied for</th>
                <th colspan="2">Availed</th>
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
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
            </tr>

            <?php if (!empty($stateData)): ?>
                <?php $i = 1; foreach ($stateData as $row): ?>
            <?php
                    // Query to get PL (Paid Leave) data
        $leaveSql = "SELECT day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10,
                        day_11, day_12, day_13, day_14, day_15, day_16, day_17, day_18, day_19, day_20,
                        day_21, day_22, day_23, day_24, day_25, day_26, day_27, day_28, day_29, day_30, day_31
                 FROM input
                 WHERE employee_code = :emp_code 
                 AND month = :month 
                 AND year = :year";

        $leaveStmt = $pdo->prepare($leaveSql);
        $leaveStmt->bindValue(':emp_code', $row['employee_code']);
        $leaveStmt->bindValue(':month', $month);
        $leaveStmt->bindValue(':year', $year);
        $leaveStmt->execute();
        $leaveData = $leaveStmt->fetch(PDO::FETCH_ASSOC);

        // Process leave data to find PL days
        $plDays = [];
        $plCount = 0;
        $slDays = [];
        $slCount = 0;

        if ($leaveData) {
            for ($day = 1; $day <= 31; $day++) {
                $dayKey = 'day_' . $day;
                if (isset($leaveData[$dayKey]) && $leaveData[$dayKey] == 'PL') {
                    $plDays[] = $day; // Store just the day number
                    $plCount++;
                }
            }
        }

        if ($leaveData) {
            for ($day = 1; $day <= 31; $day++) {
                $dayKey = 'day_' . $day;
                if (isset($leaveData[$dayKey]) && $leaveData[$dayKey] == 'SL') {
                    $slDays[] = $day; // Store just the day number
                    $slCount++;
                }
            }
        }

        // Format PL days for display (e.g., "5,6")
        $plDaysDisplay = implode(',', $plDays);
        $slDaysDisplay = implode(',', $slDays);

    ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_code']?? '')?></td>
                <td><?= htmlspecialchars($row['employee_name']?? '')?></td>
                <td>Adult</td>
                <td><?= htmlspecialchars(!empty($plDaysDisplay) ? $plDaysDisplay : '-') ?> <br> <?= htmlspecialchars(!empty($slDaysDisplay) ? $slDaysDisplay : '-') ?></td>
                <td colspan="2"><?= htmlspecialchars(!empty($plDaysDisplay) ? $plDaysDisplay : '-') ?> <br> <?= htmlspecialchars(!empty($slDaysDisplay) ? $slDaysDisplay : '-') ?></td>
                <td>PL <br> SL</td>
                <td><?= htmlspecialchars($row['pl_availed']?? '')?> <br> <?= htmlspecialchars($row['sl_availed']?? '')?></td>
                <td><?= htmlspecialchars($row['pl_closing']?? '')?> <br> <?= htmlspecialchars($row['sl_closing']?? '')?></td>
            </tr>  
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align:center;">No employee data found on Bihar</td>
                </tr>
            <?php endif; ?>
    </table>
</body>
</html>