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
$currentState = 'Telangana'; // Hardcoded for this state template

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
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            font-weight: bold;
        }

        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }

        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <th colspan="30" class="title">
                Form X<br>
                The Andhra Pradesh Minimum Wages Rules, 1960 [Rule 30(1)]<br>
                Register of Wages
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name and address of factory/Establishment :</th>
            <td colspan="25" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name of the manager or person responsible for payment of wages :</th>
            <td colspan="25" style="text-align: left;"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">For the month :</th>
            <td colspan="25" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <!-- Column Headers -->
        <tr>
            <th>Serial No.</th>
            <th>Employee Code</th>
            <th>Name of the worker</th>
            <th>Father's/<br>Husband's Name</th>
            <th>Designation/<br>nature of work done</th>
            <th>Rate of pay</th>
            <th>No. of Days worked</th>
            <th>Days of absentee</th>
            <th>Gross wages</th>
            <th>Overtime wages</th>
            <th>Total</th>
            <th>Fines</th>
            <th>Absence</th>
            <th>Damage or loss</th>
            <th>House Accommodation</th>
            <th>Miscellaneous amenities</th>
            <th>Advance over payments</th>
            <th>Income Tax</th>
            <th>Provident Fund</th>
            <th>E.S.I Contribution</th>
            <th>Co-operative Society</th>
            <th>Life Insurance</th>
            <th>Premium</th>
            <th>Other authorised deductions</th>
            <th>Total</th>
            <th>Net wages paid</th>
            <th>Signature/<br>Thumb impression</th>
            <th>Date of payment</th>
            <th>Date on which wages slip issued</th>
            <th>Remarks</th>
        </tr>
        <!-- Numbered Columns (1-29) -->
        <tr>
            <th>1</th>
            <th>2</th>
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
        </tr>
        <?php if (!empty($stateData)): ?>
            <?php $i = 1;
            foreach ($stateData as $row): ?>

                <?php
                $pl_availed = (float)($row['pl_availed'] ?? 0);
                $sl_availed = (float)($row['sl_availed'] ?? 0);
                $cl_availed = (float)($row['cl_availed'] ?? 0);
                $absentee = $pl_availed + $sl_availed + $cl_availed;
                ?>
                <?php
                $epf = (float)($row['epf'] ?? 0);
                $vpf = (float)($row['vpf'] ?? 0);
                $providient_fund = $epf + $vpf;
                ?>
                <?php
                $ptax = (float)($row['ptax'] ?? 0);
                $lwf = (float)($row['lwf'] ?? 0);
                $insurance = (float)($row['insurance'] ?? 0);
                $advance_recovery = (float)($row['advance_recovery'] ?? 0);
                $other_deductions = (float)($row['other_deductions'] ?? 0);
                $fines_damage_or_loss = (float)($row['fines_damage_or_loss'] ?? 0);
                $authorised_deductions = $ptax + $lwf + $insurance + $advance_recovery + $other_deductions;
                ?>
                <!-- Sample Data Rows -->
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                    <td><?= htmlspecialchars($absentee) ?></td>
                    <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td><?= htmlspecialchars($row['it_tds'] ?? '') ?></td>
                    <td><?= htmlspecialchars($providient_fund) ?></td>
                    <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td>Nil</td>
                    <td><?= htmlspecialchars($authorised_deductions) ?></td>
                    <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                    <td></td>
                    <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="42" class="no-data" style="text-align: center;">No data available for Telangana</td>
            </tr>
        <?php endif; ?>
    </table>

</body>

</html>