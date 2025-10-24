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
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .empty-row {
            height: 25px;
        }

        .left-align {
            text-align: left;
        }

        .colspan-full {
            width: 100%;
        }

        .signature-cell {
            height: 60px;
            vertical-align: bottom;
        }

        .skill-categories {
            font-weight: bold;
            text-align: center;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }

        .label {
            font-weight: bold;

        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <td class="form-header" colspan="25">
                    Form B <br>
                    The Ease of Compliance to Maintain Registers under various Labour Laws Rules, 2017 See Rule 2 (1) <br>
                    Format For Wage Register
                </td>
            </tr>
            <tr>
                <td colspan="10" class="label" style="text-align: left;">Name and Address of The Factory Establishment :</td>
                <td colspan="15" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <td colspan="10" class="label" style="text-align: left;">Name of Owner :</td>
                <td colspan="15" style="text-align: left;"><?= htmlspecialchars($employer_name) ?></td>
            </tr>
            <tr>
                <td colspan="10" class="label" style="text-align: left;">LIN :</td>
                <td colspan="15" style="text-align: left;">-</td>
            </tr>
            <tr>
                <td colspan="10" class="label" style="text-align: left;">Wage period :</td>
                <td colspan="15" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <td colspan="10" class="label" style="text-align: left;">(Monthly/Fortnightly/Weekly/Daily/Piece Rated):</td>
                <td colspan="15" style="text-align: left;">Monthly</td>
            </tr>
            <tr>
                <td colspan="25" class="label" style="text-align: left;">Rate of Minimum Wages and since the date:</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="5" class="skill-categories">Highly Skilled</td>
                <td colspan="5" class="skill-categories">Skilled</td>
                <td colspan="7" class="skill-categories">Semi-Skilled</td>
                <td colspan="6" class="skill-categories">Un Skilled</td>
            </tr>
            <tr>
                <td colspan="2"><b>Basic</td>
                <td colspan="5" style="text-align:center;">0</td>
                <td colspan="5" style="text-align:center;">0</td>
                <td colspan="7" style="text-align:center;">0</td>
                <td colspan="6" style="text-align:center;">0</td>
            </tr>
            <tr>
                <td colspan="2"><b>DA</td>
                <td colspan="5" style="text-align:center;">0</td>
                <td colspan="5" style="text-align:center;">0</td>
                <td colspan="7" style="text-align:center;">0</td>
                <td colspan="6" style="text-align:center;">0</td>
            </tr>
            <tr>
                <td colspan="2"><b>Overtime</td>
                <td colspan="5" style="text-align:center;">0</td>
                <td colspan="5" style="text-align:center;">0</td>
                <td colspan="7" style="text-align:center;">0</td>
                <td colspan="6" style="text-align:center;">0</td>
            </tr>

            <tr>
                <th rowspan="2">Sl. No. in Employee Register</th>
                <th rowspan="2">Name</th>
                <th rowspan="2">Rate of Wage</th>
                <th rowspan="2">No. of Days Worked</th>
                <th rowspan="2">Overtime hours worked</th>
                <th rowspan="2">Basic Pay</th>
                <th rowspan="2">Special Basic Pay</th>
                <th rowspan="2">DA</th>
                <th rowspan="2">Payments of Overtime</th>
                <th rowspan="2">HRA</th>
                <th rowspan="2">Others</th>
                <th rowspan="2">Total</th>
                <th colspan="7">Deducation</th>
                <th rowspan="2">Total</th>
                <th rowspan="2">Net Payment</th>
                <th rowspan="2">Employer PF Welfare Found</th>
                <th rowspan="2">Receipt by Employee/Bank Transaction ID</th>
                <th rowspan="2">Date of Payment</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>PF</th>
                <th>ESIC</th>
                <th>Society</th>
                <th>Income Tax</th>
                <th>Insurance</th>
                <th>Others</th>
                <th>Recoveries</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $Gross = (float)($row['gross_wages'] ?? 0);
                    $Basic =  (float)($row['basic'] ?? 0);
                    $Da = (float)($row['da'] ?? 0);
                    $Overtime = (float)($row['over_time_allowance'] ?? 0);
                    $Hra = (float)($row['hra'] ?? 0);
                    $calculation1 = $Gross - $Basic - $Da - $Overtime - $Hra;

                    $EPF = (float)($row['epf'] ?? 0);
                    $VPF = (float)($row['vpf'] ?? 0);
                    $calculation2 = $EPF - $VPF;

                    $Total = (float)($row['total_deduction'] ?? 0);
                    $ESI = (float)($row['esi'] ?? 0);
                    $It = (float)($row['it_tds'] ?? 0);
                    $Insurance = (float)($row['insurance'] ?? 0);
                    $calculation3 = $Total - $EPF - $VPF - $ESI - $It - $Insurance;
                    ?>
                    <tr>

                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                        <td><?= htmlspecialchars($calculation1 ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                        <td><?= htmlspecialchars($calculation2 ?? '') ?></td>
                        <td><?= htmlspecialchars($row['esi'] ?? '') ?><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['it_tds'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['insurance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($calculation3 ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employer_pf'] ?? '') ?></td>
                        <td>Bank Transaction</td>
                        <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                        <td></td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="25" style="text-align:center;">No data available for Manipur</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>