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
$currentState = 'Delhi'; // Hardcoded for this state template

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

    $location = $first_row['location_name'] ?? '';
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
            font-size: 12px;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            min-width: 20px;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .empty-row td {
            height: 25px;
        }

        .left-align {
            text-align: left;
        }

        .date-col {
            width: 20px;
        }

        .signature-row td {
            border: none;
            height: 30px;
            padding-top: 20px;
        }

        .total-row {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="41">
                    FORM V <br>
                    The Delhi Minimum Wages Rules, 1950, [Rule 26(5)] <br>
                    Register of Muster Roll
                </th>
            </tr>
            <tr>
                <th colspan="19" style="text-align: left;">Name and Address of the Establishment</th>
                <td colspan="22" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="19" style="text-align: left;">Place</th>
                <td colspan="22" style="text-align: left;"><?= $location ?></td>
            </tr>
            <tr>
                <th colspan="19" style="text-align: left;">For the Period ending</th>
                <td colspan="22" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">S. No.</th>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Name</th>
                <th rowspan="2">Father's/ Husband's Name</th>
                <th rowspan="2">Sex</th>
                <th rowspan="2">Nature of<br>Work</th>
                <th rowspan="2">Time at which the work commenced</th>
                <th rowspan="2">Time at which the work ceased</th>
                <th rowspan="2">Rest Interval</th>
                <th colspan="31">Hours Worked on</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th class="date-col">Day 1</th>
                <th class="date-col">Day 2</th>
                <th class="date-col">Day 3</th>
                <th class="date-col">Day 4</th>
                <th class="date-col">Day 5</th>
                <th class="date-col">Day 6</th>
                <th class="date-col">Day 7</th>
                <th class="date-col">Day 8</th>
                <th class="date-col">Day 9</th>
                <th class="date-col">Day 10</th>
                <th class="date-col">Day 11</th>
                <th class="date-col">Day 12</th>
                <th class="date-col">Day 13</th>
                <th class="date-col">Day 14</th>
                <th class="date-col">Day 15</th>
                <th class="date-col">Day 16</th>
                <th class="date-col">Day 17</th>
                <th class="date-col">Day 18</th>
                <th class="date-col">Day 19</th>
                <th class="date-col">Day 20</th>
                <th class="date-col">Day 21</th>
                <th class="date-col">Day 22</th>
                <th class="date-col">Day 23</th>
                <th class="date-col">Day 24</th>
                <th class="date-col">Day 25</th>
                <th class="date-col">Day 26</th>
                <th class="date-col">Day 27</th>
                <th class="date-col">Day 28</th>
                <th class="date-col">Day 29</th>
                <th class="date-col">Day 30</th>
                <th class="date-col">Day 31</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td colspan="3"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
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
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="no-data" colspan="7">No employee data available for Delhi</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>