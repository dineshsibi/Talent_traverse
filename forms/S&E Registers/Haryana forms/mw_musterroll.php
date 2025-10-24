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
$currentState = 'Haryana'; // Hardcoded for this state template

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
                <th class="form-header" colspan="38">
                    Form V<br>
                    The Punjab Minimum Wages Rules, 1950 [Rule 26(5)] <br>
                    Muster Roll<br>
                </th>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="18">Name and Address of the Establishment</th>
                <td colspan="20" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="18"> Place</th>
                <td colspan="20" style="text-align: left;"><?= htmlspecialchars($first_row['location_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="18">For the Month of</th>
                <td colspan="20" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">S. No.</th>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Name</th>
                <th rowspan="2">Father's/ Husband's Name</th>
                <th rowspan="2">Sex</th>
                <th rowspan="2">Nature of<br>Work</th>
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
            <tr>
                <th>1</th>
                <th>2</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th colspan="31">6</th>
                <th>7</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data Rows (Make sure 39 <td>s only) -->
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td class="left-align"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
                        <?php for ($d = 1; $d <= 31; $d++): ?>
                            <td><?= htmlspecialchars($row['day_' . $d] ?? '') ?></td>
                        <?php endfor; ?>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="no-data" colspan="38">No contractor data available for Haryana</td>
                </tr>
            <?php endif; ?>

            <!-- Total Row -->
            <tr>
                <td colspan="6">Total</td>
                <?php for ($d = 1; $d <= 31; $d++): ?>
                    <td>0</td>
                <?php endfor; ?>
                <td></td>
            </tr>
            <tr>
                <td style="text-align: right;" colspan="38"><b>Signature of Employer</td>
            </tr>
        </tbody>
    </table>
</body>

</html>