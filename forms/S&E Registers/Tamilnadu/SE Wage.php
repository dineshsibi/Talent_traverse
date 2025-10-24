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
<?php
// Calculate total male and female employees
$totalMale = 0;
$totalFemale = 0;

foreach ($stateData as $row) {
    if (isset($row['gender'])) {
        if (strtolower($row['gender']) === 'male') {
            $totalMale++;
        } elseif (strtolower($row['gender']) === 'female') {
            $totalFemale++;
        }
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
            <th colspan="30" class="form-header">
                FORM–W<br>
                Maintenance of Register of wages<br>
                The Tamil Nadu Shops and Establishments Rules, 1948 Rule 16(1)(a)(iii)
            </th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name and Address of the Establishment:</th>
            <td colspan="12" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            <th colspan="10">Total Number of Persons employed
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name and Address of the Employer:</th>
            <td colspan="12" style="text-align: left;"><?= htmlspecialchars(($first_row['employer_name'] ?? '') . ' , ' . ($first_row['employer_address'] ?? '')) ?></td>
            <th colspan="2">Men</th>
            <th colspan="2">Women</th>
            <th colspan="3">Male Young persons</th>
            <th colspan="3">Female Young persons</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name of the manager/In-charge:</th>
            <td colspan="12" style="text-align: left;">-</td>
            <td colspan="2"><?= $totalMale ?></td>
            <td colspan="2"><?= $totalFemale ?></td>
            <td colspan="3">-</td>
            <td colspan="3">-</td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Registration No:</th>
            <td colspan="22" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Wage Period from to(Monthly / Fortnightly / Weekly / Daily / Piece Rated):</th>
            <td colspan="22" style="text-align: left;">Monthly</td>
        </tr>
        <tr>
            <th rowspan="3">Serial Number</th>
            <th rowspan="3">Name of the Employee</th>
            <th rowspan="3">Employee Identification No.</th>
            <th rowspan="3">Number of days Worked</th>
            <th rowspan="3">Basic Wage</th>
            <th rowspan="3">Dearness allowances</th>
            <th rowspan="3">House rent allowances</th>
            <th rowspan="3">Other allowances (nature may be specified)</th>
            <th rowspan="3">overtime wages</th>
            <th rowspan="3">Overtime Wages (wages for EL availed / double wages for National Festival Holidays / wages for accumulated leave)</th>
            <th rowspan="3">Gross wages</th>
            <th rowspan="3">Provident Fund No.</th>
            <th rowspan="3">Employee's State Insurance Corporation No.</th>
            <th rowspan="3">Labour welfare fund</th>
            <th colspan="10" style="text-align: center;">Deductions</th>
            <th rowspan="3">Net wages</th>
            <th rowspan="3">Date of payment</th>
            <th rowspan="3">Unpaid accumulations</th>
            <th rowspan="3">Rate of which subsistence allowance calculated and amount paid</th>
            <th rowspan="3">Receipt by Employee / Bank Transaction Identify and Date</th>
            <th rowspan="3">Remarks</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">Advances</th>
            <th colspan="6" style="text-align: center;">Damages / Fine</th>
        </tr>
        <tr>
            <th>Advance paid</th>
            <th>Advance recovery pending at the beginning of the month</th>
            <th>Advance Recovered</th>
            <th>Pending recovery</th>
            <th>Deduction imposed on Damages, loss or Fines</th>
            <th>Deduction recovery pending at the beginning of the month</th>
            <th>Deduction made on Damages,loss or Fines</th>
            <th>Pending recovery</th>
            <th>Any other deductions</th>
            <th>Total deductions</th>
        </tr>
        <tr>

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
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $gross = (float)($row['gross_wages'] ?? 0);
                    $basic = (float)($row['basic'] ?? 0);
                    $da = (float)($row['da'] ?? 0);
                    $hra = (float)($row['hra'] ?? 0);
                    $otherallowance = $gross - ($basic + $da + $hra);

                    $epf = (float)($row['epf'] ?? 0);
                    $vpf = (float)($row['vpf'] ?? 0);
                    $providentfund =  $epf + $vpf;

                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                        <td><?= htmlspecialchars($otherallowance ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nfh_wages'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                        <td><?= htmlspecialchars($providentfund ?? '') ?></td>
                        <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['lwf'] ?? '') ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="42" class="no-data">No data available for Tamilnadu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>