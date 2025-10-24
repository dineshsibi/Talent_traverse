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
            page-break-inside: avoid;
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
        
        .employee-form {
            page-break-after: always;
        }
        
        .employee-form:last-child {
            page-break-after: avoid;
        }
        
        @media print {
            .page-break {
                page-break-before: always;
            }
            
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;

        $gross = (float)($row['gross_wages'] ?? 0);
        $basic = (float)($row['basic'] ?? 0);
        $da = (float)($row['da'] ?? 0);
        $hra = (float)($row['hra'] ?? 0);
        $ot = (float)($row['over_time_allowance'] ?? 0);
        $otherallowance = $gross - ($basic + $da + $hra + $ot);

        $epf = (float)($row['epf'] ?? 0);
        $vpf = (float)($row['vpf'] ?? 0);
        $providentfund =  $epf + $vpf;

        $esi = (float)($row['esi'] ?? 0);
        $total_deduction = (float)($row['total_deduction'] ?? 0);
        $other_deductions = $total_deduction - ($epf + $vpf + $esi);

        // Calculate wage period start and end dates based on selected month/year
        $fromDate = '';
        $toDate = '';
        if (!empty($month) && !empty($year)) {
            $dateObj = DateTime::createFromFormat('!m Y', $month . ' ' . $year);
            if ($dateObj) {
                $fromDate = $dateObj->format('d-M-Y'); // First day of month
                $toDate = $dateObj->format('t-M-Y');   // Last day of month
            }
        }
    ?>
        <table>
            <tr>
                <th class="form-header" colspan="12">
                    Form T<br>
                    WAGE SLIP/LEAVE CARD<br>
                    The Tamilnadu Shops and Establishments Rules, 1948 See Rules 20 (1) and 21 (2)
                </th>
            </tr>
            <tr>
                <th>1</th>
                <th colspan="3" style="text-align: left;">Name and address of the Establishment :</th>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th>2</th>
                <th colspan="3" style="text-align: left;">Employee Code :</th>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <th>2</th>
                <th colspan="3" style="text-align: left;">Name of the Person Employed :</th>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th>3</th>
                <th colspan="3" style="text-align: left;">Father's or Husband's Name :</th>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th>4</th>
                <th colspan="3" style="text-align: left;">Designation :</th>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            </tr>
            <tr>
                <th>5</th>
                <th colspan="3" style="text-align: left;">Date of entry into Service :</th>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            </tr>
            <tr>
                <th>6</th>
                <th colspan="3">Wage Period :</th>
                <th colspan="2">From</th>
                <td colspan="2"><?= htmlspecialchars($fromDate) ?></td>
                <th colspan="2">To</th>
                <td colspan="2"><?= htmlspecialchars($toDate) ?></td>
            </tr>
            <tr>
                <th rowspan="8">7</th>
                <th colspan="3">Wage Earned :</th>
                <th colspan="8" style="text-align: center;">Deductions :</th>
            </tr>
            <tr>
                <th>(a)</th>
                <th>Basic :</th>
                <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                <th colspan="2">(i)</th>
                <th colspan="3">Employees Provident Fund</th>
                <td colspan="3"><?= htmlspecialchars($providentfund ?? '') ?></td>
            </tr>
            <tr>
                <th>(b)</th>
                <th>Dearness Allowance</th>
                <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                <th colspan="2">(ii)</th>
                <th colspan="3">Employees State Insurance</th>
                <td colspan="3"><?= htmlspecialchars($row['esi'] ?? '') ?></td>
            </tr>
            <tr>
                <th>(c)</th>
                <th>House Rent Allowance</th>
                <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                <th colspan="2">(iii)</th>
                <th colspan="3">Other Deductions</th>
                <td colspan="3"><?= htmlspecialchars($other_deductions ?? '') ?></td>
            </tr>
            <tr>
                <th>(d)</th>
                <th>Overtime Wages</th>
                <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                <td colspan="8"></td>
            </tr>
            <tr>
                <th>(e)</th>
                <th>Leave Wages</th>
                <td>NIL</td>
                <td colspan="8"></td>
            </tr>
            <tr>
                <th>(f)</th>
                <th>Other Allowances</th>
                <td><?= htmlspecialchars($otherallowance ?? '') ?></td>
                <td colspan="8"></td>
            </tr>
            <tr>
                <th>(g)</th>
                <th>Gross Wages</th>
                <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                <th colspan="2">Net Amount Paid</th>
                <td colspan="6"><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            </tr>
            <tr>
                <th>8</th>
                <th colspan="3">Leave Availed during the month :</th>
                <th>CL</th>
                <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
                <th>SL</th>
                <td><?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
                <th>EL</th>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                <th>M</th>
                <td>NIL</td>
            </tr>
            <tr>
                <th>9</th>
                <th colspan="3">Leave at Credit :</th>
                <th>CL</th>
                <td><?= htmlspecialchars($row['cl_credit'] ?? '') ?></td>
                <th>SL</th>
                <td><?= htmlspecialchars($row['sl_credit'] ?? '') ?></td>
                <th>EL</th>
                <td><?= htmlspecialchars($row['pl_credit'] ?? '') ?></td>
                <th>M</th>
                <td>NIL</td>
            </tr>
            <tr>
                <th colspan="6">Signature of the Employer/ Manager/or any other Authorised Person</th>
                <th colspan="6">Signature of Thumb impression of the Person Employed</th>
            </tr>
        </table>
        <?php
        // Add page break except for last employee
        if ($currentEmployee < $totalEmployees):
        ?>
            <div class="page-break"></div>
        <?php endif; ?>

    <?php endforeach; ?>
</body>

</html>