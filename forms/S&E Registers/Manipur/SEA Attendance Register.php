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
$currentState = 'Manipur'; // Hardcoded for this state template

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

    $employer = $first_row['employer_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Function to calculate sum of day values
function calculateDaySum($row) {
    $total = 0;
    for ($d = 1; $d <= 31; $d++) {
        $dayValue = $row['day_' . $d] ?? '';
        
        // Extract numeric values from the cell (handle cases like "8P" or "8.5")
        if (preg_match('/(\d+\.?\d*)/', $dayValue, $matches)) {
            $total += (float)$matches[1];
        }
    }
    return $total;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 10px;
            font-size: 7px;
            line-height: 1.1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffffff;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .main-heading {
            text-align: center;
            font-size: 9px;
            padding: 4px;
            border: 1px solid #000;
            background-color: #ffffffff;
        }

        .info-row {
            text-align: left;
            font-weight: bold;
        }

        .info-data {
            text-align: left;
        }

        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            text-align: center;
            width: 12px;
        }

        .small-font {
            font-size: 6px;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <th colspan="39" class="main-heading">
                FORM - VII<br>
                The Manipur Shops and Establishments Act 1972 & Rules, 1973, [Rule 17 (3)]<br>
                REGISTER OF ATTENDANCE, OVER TIME AND ACCOUNT OF WAGES
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="4" class="info-row">Name of the Shop/Establishment</td>
            <td colspan="35" class="info-data"><?= htmlspecialchars($client_name) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="info-row">Address of the Branch</td>
            <td colspan="35" class="info-data"><?= htmlspecialchars($branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="info-row">Name of the Shopkeeper/Employer</td>
            <td colspan="35" class="info-data"><?= htmlspecialchars($employer) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="info-row">For the month of</td>
            <td colspan="35" class="info-data"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Column Headers - First Row -->
        <tr>
            <th rowspan="2">Sl No</th>
            <th rowspan="2">Name of employees</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Arrival and Departure</th>
            <th colspan="31">Days in the month</th>
            <th rowspan="2">No. of hours for the work during the month</th>
            <th rowspan="2">No of actual hour of work rendered during the month</th>
            <th rowspan="2">Overtime during the month</th>
            <th rowspan="2">Account of wages for the month</th>
        </tr>

        <tr>
            <!-- Day Columns 1-31 -->
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

        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): 
                    // Calculate the sum of day values for this row
                    $daySum = calculateDaySum($row);
                ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>

                        <!-- Day Columns 1-31 -->
                        <?php for ($d = 1; $d <= 31; $d++): ?>
                            <td><?= htmlspecialchars($row['day_' . $d] ?? '') ?></td>
                        <?php endfor; ?>

                        <!-- Summary Columns -->
                        <td><?= $daySum ?></td>
                        <td><?= $daySum ?></td>
                        <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="39" style="text-align:center;">No employee data found for Manipur</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>