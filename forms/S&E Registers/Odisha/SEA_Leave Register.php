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
$currentState = 'Odisha'; // Hardcoded for this state template

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
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .main-heading {
            text-align: center;
            font-size: 14px;
            padding: 6px;
            border: 1px solid #000;
            background-color: #ffffff;
        }

        .info-row {
            text-align: left;
            font-weight: bold;
        }

        .info-data {
            text-align: left;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            text-align: center;
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
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;

        $pl_opening = (float)($row['pl_opening'] ?? 0);
        $pl_credit = (float)($row['pl_credit'] ?? 0);
        $total = $pl_opening + $pl_credit;

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
        //calculate total days of the particular month
        if ($dateObj) {
            $fromDate = $dateObj->format('01-M-Y'); // Always 1st day of month
            $toDate = $dateObj->format('t-M-Y');    // Last day of month
            $daysInMonth = $dateObj->format('t');   // Number of days in month
        }

    ?>
        <table>
            <!-- Main Heading -->
            <tr>
                <th colspan="20" class="main-heading">
                    Form 9<br>
                    The Odisha Shops and Commercial Establishments Rules, 1958 [See Rule 15 (2)]<br>
                    Service and leave account
                </th>
            </tr>

            <!-- Establishment and Employee Details -->
            <tr>
                <td colspan="4" class="info-row">Name of employer/ establishment</td>
                <td colspan="16" class="info-data"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="info-row">Employee Code</td>
                <td colspan="16" class="info-data"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="4" class="info-row">Name of employee</td>
                <td colspan="16" class="info-data"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="4" class="info-row">Registration Certificate No.</td>
                <td colspan="16" class="info-data">-</td>
            </tr>
            <tr>
                <td colspan="4" class="info-row">Adult, Male or Female, or child</td>
                <td colspan="16" class="info-data"><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="4" class="info-row">Month/Year</td>
                <td colspan="16" class="info-data"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>

            <!-- Column Headers -->
            <tr>
                <th rowspan="2">Name of employment, if any</th>
                <th rowspan="2">Monthly or weekly rate of pay or wages</th>
                <th colspan="3">Date of employment</th>
                <th colspan="3">Leave earned</th>
                <th rowspan="2">Period of leave refused to be carried over</th>
                <th colspan="3">Leave availed</th>
                <th rowspan="2">Balance of leave at credit</th>
                <th colspan="3">Sickness leave</th>
                <th rowspan="2">Balance after each occasion</th>
                <th rowspan="2">Signature of thumb impression of the employee</th>
                <th rowspan="2">Signature of Employer</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Number of days</th>
                <th>At credit</th>
                <th>Earned</th>
                <th>Total</th>
                <th>From</th>
                <th>To</th>
                <th>Number of days</th>
                <th>From</th>
                <th>To</th>
                <th>Number of days availed</th>
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
            </tr>

            <tr>
                <td>-</td>
                <td>Monthly</td>
                <td><?= htmlspecialchars($fromDate) ?></td>
                <td><?= htmlspecialchars($toDate) ?></td>
                <td><?= htmlspecialchars($daysInMonth ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_credit'] ?? '') ?></td>
                <td><?= htmlspecialchars($total ?? '') ?></td>
                <td>Nil</td>
                <td colspan="2">
                    <?php
                    $plDays = [];
                    for ($d = 1; $d <= 31; $d++) {
                        $col = 'day_' . $d;
                        if (isset($row[$col]) && strtoupper(trim($row[$col])) === 'PL') {
                            $plDays[] = $d;
                        }
                    }
                    echo !empty($plDays) ? implode(', ', $plDays) : '-';
                    ?>
                </td>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
                <td colspan="2">
                    <?php
                    $slDays = [];
                    for ($d = 1; $d <= 31; $d++) {
                        $col = 'day_' . $d;
                        if (isset($row[$col]) && strtoupper(trim($row[$col])) === 'SL') {
                            $slDays[] = $d;
                        }
                    }
                    echo !empty($slDays) ? implode(', ', $slDays) : '-';
                    ?>
                </td>
                <td><?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
                <td></td>
                <td></td>
                <td></td>
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