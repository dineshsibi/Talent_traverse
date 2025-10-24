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
$currentState = 'Pondicherry'; // Hardcoded for this state template

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
    $employer_name = $first_row['employer_name'] ?? '';
    $employer_address = $first_row['employer_address'] ?? '';
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
            margin: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            font-weight: bold;
            background-color: #ffffffff;
        }

        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }

        .left-align {
            text-align: left;
        }

        .right-align {
            text-align: right;
        }

        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            width: 20px;
        }

        .small-text {
            font-size: 10px;
        }

        .section-header {
            font-weight: bold;
            background-color: #ffffffff;
        }

        .sub-header {
            font-weight: bold;
            background-color: #ffffffff;
        }

        @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <?php if (!empty($stateData)): ?>
        <table>
            <!-- Title Row -->
            <tr>
                <th colspan="23" class="title">
                    FORM X<br>
                    The Puducherry Shops and Establishments (Amendment) Rules, 2010, [See sub-rule (5) of rule 17]<br>
                    REGISTER OF WAGES
                </th>
            </tr>

            <!-- Establishment Information -->
            <tr>
                <th colspan="5" class="left-align">Name of the establishment:</th>
                <td colspan="10" class="left-align"><?= htmlspecialchars($client_name) ?></td>
                <th colspan="8" class="left-align">Wages period</th>
            </tr>
            <tr>
                <td colspan="5" class="left-align section-header">Name of the Employer/Contractor with address:</td>
                <td colspan="10" class="left-align"><?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?></td>
                <th colspan="4" class="left-align">Month & Year</td>
                <td colspan="4" class="left-align"><?= htmlspecialchars($month . ' - ' . $year) ?></th>
            </tr>

            <!-- Main Header Row -->
            <tr>
                <th rowspan="2">Sl. No.</th>
                <th rowspan="2">Name of the person employed</th>
                <th rowspan="2">Sex</th>
                <th rowspan="2">Designation/nature of work</th>
                <th rowspan="2">Daily rated/piece-rated/monthly rated</th>
                <th rowspan="2">Wages period-weekly/fortnight/month.</th>
                <th rowspan="2">Total number of days worked during the week/fortnight/month.</th>
                <th rowspan="2">Unit of work done/number of days worked</th>
                <th rowspan="2">Daily rate wages/piece-rate</th>
                <th rowspan="2">Overtime rate</th>
                <th colspan="6">Wages earned</th>
                <th colspan="4">Deductions</th>
                <th rowspan="2">Net wages</th>
                <th rowspan="2">Signature with date or thumb-impression/cheque number and date in case of payment through bank/advice of the bank to be appended</th>
                <th rowspan="2">Total unpaired amounts accumulated</th>
            </tr>
            <tr>
                <th>Basic wages</th>
                <th>Dearness allowance</th>
                <th>Other allowances /cash Payment (nature to be specified)</th>
                <th>Overtime earned</th>
                <th>Leave wages including cash in Lieu of kind</th>
                <th>Gross wages</th>
                <th>Provident fund</th>
                <th>Employees' State Insurance</th>
                <th>Other deductions (indicate nature)*</th>
                <th>Fines (if any)*</th>
            </tr>

            <!-- Column Numbers -->
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
            </tr>

            <?php
            $i = 1;
            $totalBasicWages = 0;
            $totalDearnessAllowance = 0;
            $totalOtherAllowances = 0;
            $totalOvertimeEarned = 0;
            $totalLeaveWages = 0;
            $totalGrossWages = 0;
            $totalProvidentFund = 0;
            $totalESI = 0;
            $totalOtherDeductions = 0;
            $totalFines = 0;
            $totalNetWages = 0;

            foreach ($stateData as $row):
                // Calculate totals for summary
                $basic = (float)($row['basic'] ?? 0);
                $da = (float)($row['da'] ?? 0);
                $overtime_allowance = (float)($row['over_time_allowance'] ?? 0);
                $grossWages = (float)($row['gross_wages'] ?? 0);
                $other_allowance =  $grossWages - ($basic + $da + $overtime_allowance);

                $epf = (float)($row['epf'] ?? 0);
                $vpf = (float)($row['vpf'] ?? 0);
                $contribution_pf = $epf + $vpf;

                $net_pay = (float)($row['net_pay'] ?? 0);
                $insurance = (float)($row['insurance'] ?? 0);
                $fines = (float)($row['fines_damage_or_loss'] ?? 0);
                $other_deduction = $net_pay - ($epf + $vpf + $insurance + $fines);

                $fixed = (float)($row['fixed_gross'] ?? 0);
                $overtime_rate = (($fixed / 31) / 8) * 2;

            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                    <td class="left-align"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                    <td>Monthly Rated</td>
                    <td>Monthly</td>
                    <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                    <td>NIL</td>
                    <td><?= round($overtime_rate) ?></td>
                    <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                    <td><?= htmlspecialchars($other_allowance ?? '') ?></td>
                    <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                    <td>0</td>
                    <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                    <td><?= htmlspecialchars($contribution_pf ?? '') ?></td>
                    <td><?= htmlspecialchars($row['insurance'] ?? '') ?></td>
                    <td><?= htmlspecialchars($other_deduction ?? '') ?></td>
                    <td><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <table>
            <tr>
                <td style="text-align: center;">No data available for Puducherry</td>
            </tr>
        </table>
    <?php endif; ?>
</body>

</html>