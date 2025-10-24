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
$currentState = 'Goa'; // Hardcoded for this state template

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

    $branch_address = safe($first_row['branch_address'] ?? '');
    $location_name = safe($first_row['location_name'] ?? '');

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
            text-align:left;
        }
        .day-column {
            width: 20px;
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
                <th class="form-header" colspan="39" class="title">
                    Form XXI<br>
                    The Goa, Daman and Diu Shops and Establishment Act, 1973 and Rules, 1975, (See Rule 31 (1) )<br>
                    Register of Employment
                </th>
            </tr>
            <tr>
                <th colspan="15" class="header-info">Name and address of the Establishment</th>
                <td colspan="24" style="text-align: left;"><?= htmlspecialchars($client_name .' , ' . $branch_address)?></td>
            </tr>
            <tr>
                <th colspan="15" class="header-info">Nature and location of work</th>
                <td colspan="24" style="text-align: left;"><?= htmlspecialchars($location_name)?></td>
            </tr>
            <tr>
                <th colspan="15" class="header-info">For the Month of</th>
                <td colspan="24" style="text-align: left;"><?= htmlspecialchars($month .' - ' . $year)?></td>
            </tr>
            <tr>
                <th rowspan="2">Sl No</th>
                <th rowspan="2">Emp Code</th>
                <th rowspan="2">Name of the Employee</th>
                <th rowspan="2">Sex</th>
                <th rowspan="2">Department</th>
                <th colspan="31">Days of Months</th>
                <th colspan="2">Total hours worked</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th class="day-column">Day1</th>
                <th class="day-column">Day2</th>
                <th class="day-column">Day3</th>
                <th class="day-column">Day4</th>
                <th class="day-column">Day5</th>
                <th class="day-column">Day6</th>
                <th class="day-column">Day7</th>
                <th class="day-column">Day8</th>
                <th class="day-column">Day9</th>
                <th class="day-column">Day10</th>
                <th class="day-column">Day11</th>
                <th class="day-column">Day12</th>
                <th class="day-column">Day13</th>
                <th class="day-column">Day14</th>
                <th class="day-column">Day15</th>
                <th class="day-column">Day16</th>
                <th class="day-column">Day17</th>
                <th class="day-column">Day18</th>
                <th class="day-column">Day19</th>
                <th class="day-column">Day20</th>
                <th class="day-column">Day21</th>
                <th class="day-column">Day22</th>
                <th class="day-column">Day23</th>
                <th class="day-column">Day24</th>
                <th class="day-column">Day25</th>
                <th class="day-column">Day26</th>
                <th class="day-column">Day27</th>
                <th class="day-column">Day28</th>
                <th class="day-column">Day29</th>
                <th class="day-column">Day30</th>
                <th class="day-column">Day31</th>
                <th>Normal</th>
                <th>Overtime</th>
            </tr>
        </thead>
        <tbody>
             <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
            <tr>
               <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['department'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_1'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_2'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_3'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_4'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_5'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_6'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_7'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_8'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_9'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_10'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_11'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_12'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_13'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_14'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_15'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_16'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_17'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_18'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_19'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_20'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_21'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_22'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_23'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_24'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_25'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_26'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_27'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_28'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_29'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_30'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_31'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <th colspan="19" style="text-align:center;">No employee data found for Goa</th>
            </tr>
        <?php endif; ?>
        </tbody>
        </table>
    </div>
</body>
</html>