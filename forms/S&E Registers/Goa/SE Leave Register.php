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

    $branch_address = $first_row['branch_address'] ?? '';
    $employer_name = $first_row['employer_name'] ?? '';
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

        th,
        td {
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

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;

        // Initialize arrays for each employee (important!)
        $pl_dates = $cl_dates = $sl_dates = $ml_dates = [];

        // Loop through day_1 to day_31
        for ($i = 1; $i <= 31; $i++) {
            $dayCol = 'day_' . $i;
            $dayValue = strtoupper(trim($row[$dayCol] ?? ''));

            if ($dayValue === 'PL') {
                $pl_dates[] = $i;
            } elseif ($dayValue === 'CL') {
                $cl_dates[] = $i;
            } elseif ($dayValue === 'SL') {
                $sl_dates[] = $i;
            } elseif ($dayValue === 'ML') {
                $ml_dates[] = $i;
            }
        }

        $pl_str = !empty($pl_dates) ? implode(', ', $pl_dates) : '-';
        $cl_str = !empty($cl_dates) ? implode(', ', $cl_dates) : '-';
        $sl_str = !empty($sl_dates) ? implode(', ', $sl_dates) : '-';
        $ml_str = !empty($ml_dates) ? implode(', ', $ml_dates) : '-';
    ?>
        <div class="form-container">
            <table>
                <tr>
                    <td colspan="34" class="title">
                        Form XII<br>
                        The Goa, Daman and Diu Shops and Establishment Act, 1973 and Rules, 1975, [See Rule 21 (3)]<br>
                        REGISTER OF LEAVE</td>
                </tr>
                <tr>
                    <th colspan="15" class="header-info">Name and Address of the Establishment</th>
                    <td colspan="19" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                </tr>
                <tr>
                    <th colspan="15" class="header-info">Name of Employer</th>
                    <td colspan="19" style="text-align: left;"><?= htmlspecialchars($employer_name) ?></td>
                </tr>
                <tr>
                    <th colspan="15" class="header-info">Registration.No</th>
                    <td colspan="19" style="text-align: left;">-</td>
                </tr>
                <tr>
                    <th colspan="15" class="header-info">Name of Employee</th>
                    <td colspan="19" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="15" class="header-info">Father's Name</th>
                    <td colspan="19" style="text-align: left;"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="15" class="header-info">Date of appointment</th>
                    <td colspan="19" style="text-align: left;"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="15" class="header-info">Month</th>
                    <td colspan="19" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                </tr>
                <tr>
                    <th colspan="9">Earned Leave with Wages</th>
                    <th colspan="9">SICK LEAVE</th>
                    <th colspan="9">CASUAL LEAVE</th>
                    <th colspan="7">MATERNITY LEAVE</th>
                </tr>
                <tr>
                    <th rowspan="2">Date Of Application</th>
                    <th colspan="3">Applied</th>
                    <th colspan="2">Leave Granted</th>
                    <th rowspan="2">Balance</th>
                    <th colspan="2">If refused in part or full</th>
                    <th rowspan="2">Date of Application</th>
                    <th colspan="2">Applied</th>
                    <th colspan="2">Leave granted</th>
                    <th rowspan="2">Balance Due</th>
                    <th colspan="3">If refused, in part or full</th>
                    <th rowspan="2">Date of Application</th>
                    <th colspan="2">Applied</th>
                    <th colspan="2">Leave granted</th>
                    <th rowspan="2">Balance Due</th>
                    <th colspan="3">If refused, in part or full</th>
                    <th rowspan="2">Date of Application</th>
                    <th colspan="2">Applied</th>
                    <th colspan="2">Leave granted</th>
                    <th colspan="2">Signature</th>
                </tr>
                <tr>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>No.of days to which the employee is entitled</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>From Date Reasons</th>
                    <th>To Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Reasons</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Reasons</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Employee</th>
                    <th>Employer</th>
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
                    <th>32</th>
                    <th>33</th>
                    <th>34</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($pl_str) ?></td>
                    <td colspan="2"><?= htmlspecialchars($pl_str) ?></td>

                    <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                    <td colspan="2"><?= htmlspecialchars($pl_str) ?></td>

                    <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
                    <td colspan="2">NIL</td>

                    <td><?= htmlspecialchars($sl_str) ?></td>
                    <td colspan="2"><?= htmlspecialchars($sl_str) ?></td>

                    <td colspan="2"><?= htmlspecialchars($sl_str) ?></td>

                    <td><?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
                    <td colspan="3">NIL</td>


                    <td><?= htmlspecialchars($cl_str) ?></td>
                    <td colspan="2"><?= htmlspecialchars($cl_str) ?></td>

                    <td colspan="2"><?= htmlspecialchars($cl_str) ?></td>

                    <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
                    <td colspan="3">NIL</td>

                    <td><?= htmlspecialchars($ml_str) ?></td>
                    <td colspan="2"><?= htmlspecialchars($ml_str) ?></td>

                    <td colspan="2"><?= htmlspecialchars($ml_str) ?></td>

                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>
        <?php if ($currentEmployee < $totalEmployees): ?>
            <div style="page-break-after: always;"></div>
        <?php endif; ?>

    <?php endforeach; ?>
</body>

</html>