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
$currentState = 'Assam'; // Hardcoded for this state template

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

        th,
        td {
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
    <div class="form-container">
        <table>
            <tr>
                <td colspan="31" class="title">Form N<br>
                    The Assam Shops and Establishments Act, 1971 with Rules, 1976 [See Rule 43]<br>
                    Register of leave with wages</td>
            </tr>

            <tr>
                <td colspan="11" style="text-align: left;"><b>Name and Address of Shop/Establishment, if any</td>
                <td colspan="20" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align: left;"><b>Name of Shop-keeper/employer</td>
                <td colspan="20" style="text-align: left;"><?= htmlspecialchars($employer_name) ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align: left;"><b>Registration No.</td>
                <td colspan="20" style="text-align: left;">-</td>
            </tr>
            <tr>
                <td colspan="11" style="text-align: left;"><b>For the month of</td>
                <td colspan="20" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>

            <!-- Main Header Row -->
            <tr>
                <td rowspan="2"><b>Sl No</td>
                <td rowspan="2"><b>Emp Code</td>
                <td rowspan="2"><b>Name of the person</td>
                <td rowspan="2"><b>Father's/Husbands <br>name</td>
                <td rowspan="2"><b>Date of entry <br>into service</td>
                <td colspan="2"><b>Period of twelve <br>month's continuous<br> service</td>
                <td colspan="2"><b>Leave Due</td>
                <td rowspan="2"><b>Date of Application<br> for Leave</td>
                <td colspan="3"><b>Leave Applied for</td>
                <td colspan="3"><b>Leave Allowed</td>
                <td colspan="3"><b>Leave Availed</td>
                <td colspan="3"><b>Extended</td>
                <td rowspan="2"><b>Balance Leave to<br> credit, if any</td>
                <td colspan="3"><b>Leave, if <br>refused in <br>Part or Full</td>
                <td rowspan="2"><b>Normal Rate<br> of wages including<br> Dearness Allowance, etc.,<br> if any</td>
                <td rowspan="2"><b>Cash equivalent<br> of supply of<br> meals and sale<br> of food grains<br> or other articles<br> at consessional<br> rates</td>
                <td rowspan="2"><b>Wages paid <br>during the <br>leave period</td>
                <td rowspan="2"><b>Remarks</td>
                <td rowspan="2"><b>Signature of<br> the Employee in <br>acquittance of <br>payment received</td>
            </tr>

            <!-- Sub Header Row -->
            <tr>
                <td><b>From</td>
                <td><b>To</td>
                <td><b>Number of days<br> for which leave <br>is due currently</td>
                <td><b>Total number<br> of days of <br>leave to credit</td>
                <td><b>From</td>
                <td><b>To</td>
                <td><b>No. of Days</td>
                <td><b>From</td>
                <td><b>To</td>
                <td><b>No. of Days</td>
                <td><b>From</td>
                <td><b>To</td>
                <td><b>No. of Days</td>
                <td><b>From</td>
                <td><b>To</td>
                <td><b>No. of Days</td>
                <td><b>From</td>
                <td><b>To</td>
                <td><b>Reason of Refusal</td>
            </tr>
            <?php if (!empty($stateData)): ?>
                <?php $sl_no = 1; // Changed from $i to $sl_no 
                ?>
                <?php foreach ($stateData as $row): ?>

                    <?php
                    // Collect dates for each leave type
                    $pl_dates = [];

                    // Loop through day_1 to day_31
                    for ($i = 1; $i <= 31; $i++) {
                        $dayCol = 'day_' . $i;
                        $dayValue = strtoupper(trim($row[$dayCol] ?? ''));

                        if ($dayValue === 'PL') {
                            $pl_dates[] = $i;
                        }
                    }

                    $pl_str = !empty($pl_dates) ? implode(', ', $pl_dates) : '-';

                    $pl_count = count($pl_dates);


                    $month = (int)$month;
                    $year = (int)$year;

                    // Generate first and last day of selected month
                    $firstDate = date('d-M-Y', strtotime("$year-$month-01"));
                    $lastDate  = date('d-M-Y', strtotime("last day of $year-$month-01"));
                    ?>
                    <!-- Empty Rows for Data Entry -->
                    <tr>
                        <td><?= $sl_no++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td><?= htmlspecialchars($firstDate) ?></td>
                        <td><?= htmlspecialchars($lastDate) ?></td>
                        <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
                        <td><?= htmlspecialchars($pl_str) ?></td>
                        <td colspan="2"><?= htmlspecialchars($pl_str) ?></td>
                        <td><?= $pl_count ?></td>
                        <td colspan="2"><?= htmlspecialchars($pl_str) ?></td>
                        <td><?= $pl_count ?></td>
                        <td colspan="2"><?= htmlspecialchars($pl_str) ?></td>
                        <td><?= $pl_count ?></td>
                        <td>NIL</td>
                        <td>NIL</td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
                        <td>Nil</td>
                        <td>Nil</td>
                        <td>Nil</td>
                        <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                        <td>Nil</td>
                        <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="31" style="text-align:center;">No data available for Assam</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>