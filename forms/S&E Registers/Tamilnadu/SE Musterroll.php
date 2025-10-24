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
$currentState = 'Tamilnadu'; // Hardcoded for this state template

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
    $employer_name = $first_row['employer_name'] ?? '';
    $employer_address = $first_row['employer_address'] ?? '';
    $nature_of_business = safe($first_row['nature_of_business'] ?? 'Not specified');
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

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffffff;
        }

        .empty-row {
            height: 30px;
        }

        .label-cell {
            font-weight: bold;
        }

        * {
            font-family: "Times New Roman", Times, serif;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="43" class="form-header">
                FORM–V<br>
                REGISTER OF EMPLOYMENT<br>
                The Tamil Nadu Shops and Establishments Rules, 1948 Rule 16(1)(a)(ii)
            </td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name and Address of the Catering Establishment</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name and Address of the Employer:</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars(($first_row['employer_name'] ?? '') . ' , ' . ($first_row['employer_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name of the Manager / Incharge:</th>
            <td colspan="37" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Registration Certificate No:</th>
            <td colspan="37" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">For the period</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th rowspan="2">Serial Number</th>
            <th rowspan="2">Name of the employee</th>
            <th rowspan="2">Employee Identification No.</th>
            <th rowspan="2">Time at which work commences</th>
            <th rowspan="2">Rest Interval</th>
            <th rowspan="2">Time at which work ends</th>
            <th colspan="31" style="text-align: center;">Daily Hours of Work including Overtime (If any)</th>
            <th rowspan="2">Total Days Worked</th>
            <th rowspan="2">Total hours worked</th>
            <th rowspan="2">Number of days on Loss of pay</th>
            <th rowspan="2">Benefit availed for working on National Holiday</th>
            <th rowspan="2">Benefit availed for working on Festival Holiday</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>Day1</th>
            <th>Day2</th>
            <th>Day3</th>
            <th>Day4</th>
            <th>Day5</th>
            <th>Day6</th>
            <th>Day7</th>
            <th>Day8</th>
            <th>Day9</th>
            <th>Day10</th>
            <th>Day11</th>
            <th>Day12</th>
            <th>Day13</th>
            <th>Day14</th>
            <th>Day15</th>
            <th>Day16</th>
            <th>Day17</th>
            <th>Day18</th>
            <th>Day19</th>
            <th>Day20</th>
            <th>Day21</th>
            <th>Day22</th>
            <th>Day23</th>
            <th>Day24</th>
            <th>Day25</th>
            <th>Day26</th>
            <th>Day27</th>
            <th>Day28</th>
            <th>Day29</th>
            <th>Day30</th>
            <th>Day31</th>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $totalNumbers = 0;

                    for ($d = 1; $d <= 31; $d++) {
                        $dayField = 'day_' . $d;

                        if (!empty($row[$dayField])) {
                            // Extract numbers from string (if any)
                            preg_match_all('/\d+/', $row[$dayField], $matches);

                            if (!empty($matches[0])) {
                                foreach ($matches[0] as $num) {
                                    $totalNumbers += (int)$num;
                                }
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
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
                        <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                        <td><?= $totalNumbers ?></td>
                        <td><?= htmlspecialchars($row['lop'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nfh_wages'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nfh_wages'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="42" class="no-data">No data available for Tamilnadu</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="43">
                    * Abbreviations to be used: H – Weekly Holiday; FH – Festival Holiday; NH – National Holiday; EL – Earned Leave; ML – Medical Leave;
                    HW – Holidays with Wages; MBL – Maternity Leave; SH – Substituted Holiday; SP – Suspension; LOP – Loss of Pay.
                    <br>** Abbreviations to be used:- H – for holidays allowed; W/D – for work on double wages; W/H – for work with substituted holiday; 'N/E' if not eligible for wages.
                </th>
            </tr>
        </tbody>
    </table>
</body>

</html>