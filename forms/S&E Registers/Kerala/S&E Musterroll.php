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
$currentState = 'Kerala'; // Hardcoded for this state template

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


    $location_name = $first_row['location_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';
    $location = $first_row['location_name'] ?? '';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if (!function_exists('getOvertimeValue')) {
    function getOvertimeValue($val)
    {
        if (is_numeric($val) && $val > 8) {
            return $val - 8; // anything above 8 is OT
        }
        return '-'; // blank if not numeric or ≤ 8
    }
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
            height: 40px;
            padding-top: 20px;
        }

        .total-row {
            font-weight: bold;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="71" class="form-header">
                FORM B<br>
                The Kerala Shops & Commercial Establishments Rules, 1961 [See — Rule 10 (1)]<br>
                REGISTER OF EMPLOYMENT
            </td>
        </tr>
        <tr>
            <th colspan="32" style="text-align: left;">Name of the Establishment and Address:</th>
            <td colspan="39" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="32" style="text-align: left;">Month/Year:</th>
            <td colspan="39" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th rowspan="2">Sl . No</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Name of person employed</th>
            <th rowspan="2">Whether young person or not</th>
            <th rowspan="2">Time at which employment commences</th>
            <th rowspan="2">Time at which employment ceases</th>
            <th rowspan="2">Rest interval</th>
            <th colspan="31">Day of Month</th>
            <th rowspan="2">Total hours worked during the month</th>
            <th colspan="31">Days on which over time work is done and extent of such overtime on each occasion</th>
            <th rowspan="2">Extent of over time worked during the month</th>
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
            <th>16</th>
            <th>17</th>
            <th>18</th>
            <th>19</th>
            <th>20</th>
            <th>21</th>
            <th>22</th>
            <th>23</th>
            <th>24</th>
            <th>25</th>
            <th>26</th>
            <th>27</th>
            <th>28</th>
            <th>29</th>
            <th>30</th>
            <th>31</th>
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
            <th>16</th>
            <th>17</th>
            <th>18</th>
            <th>19</th>
            <th>20</th>
            <th>21</th>
            <th>22</th>
            <th>23</th>
            <th>24</th>
            <th>25</th>
            <th>26</th>
            <th>27</th>
            <th>28</th>
            <th>29</th>
            <th>30</th>
            <th>31</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th colspan="31">8</th>
            <th>9</th>
            <th colspan="31">10</th>
            <th>11</th>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $total_worked_days = (float)($row['total_worked_days'] ?? 0);
                    $total = $total_worked_days * 8;


                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td>Adult</td>
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
                        <td><?= htmlspecialchars($total ?? '') ?></td>
                        <?php for ($d = 1; $d <= 31; $d++): ?>
                            <td><?= getOvertimeValue($row['day_' . $d] ?? '') ?></td>
                        <?php endfor; ?>
                        <td><?= htmlspecialchars($row['extent_ot_on_which_occasion'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="16" style="text-align:center;">No data available for Kerala</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>