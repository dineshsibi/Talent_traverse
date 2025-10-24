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
$currentState = 'Haryana'; // Hardcoded for this state template

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

    $client_name = safe($first_row['client_name'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }

        .left-align {
            text-align: left;
        }

        .total-row {
            font-weight: bold;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-row-group;
        }

        tr {
            page-break-inside: avoid;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;
        $isLast = ($currentEmployee === $totalEmployees);

        // Age calculation
        $dob = $row['date_of_birth'] ?? '';
        $age = '';
        if (!empty($dob)) {
            $dobDate = DateTime::createFromFormat('d-M-y', $dob);
            if ($dobDate) {
                $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);
                if ($referenceDate) {
                    $age = $dobDate->diff($referenceDate)->y;
                }
            }
        }

        // Count PL leave days
        $plCount = 0;
        for ($d = 1; $d <= 31; $d++) {
            $dayKey = "day_" . $d;
            if (isset($row[$dayKey]) && strtoupper(trim($row[$dayKey])) === "PL") {
                $plCount++;
            }
        }
    ?>
        <table>
            <tr>
                <th colspan="18" class="form-header">
                    FORM C <br>
                    The Punjab Shops and Commercial Establishments Rules, 1958, Rule 5 <br>
                    Register of Employees
                </th>
            </tr>
            <tr>
                <th colspan="9" class="left-align">Name and Address of Establishment</th>
                <td colspan="9" class="left-align"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" class="left-align">Employee Code</th>
                <td colspan="5" class="left-align"><?= htmlspecialchars($row['employee_code'] ?? '-') ?></td>
                <th colspan="4" class="left-align">Name of Employee</th>
                <td colspan="5" class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '-') ?></td>
            </tr>
            <tr>
                <th colspan="4" class="left-align">Father's/Husband's Name</th>
                <td colspan="5" class="left-align"><?= htmlspecialchars($row['father_name'] ?? '-') ?></td>
                <th colspan="4" class="left-align">Date of Appointment</th>
                <td colspan="5" class="left-align"><?= htmlspecialchars($row['date_of_joining'] ?? '-') ?></td>
            </tr>
            <tr>
                <th colspan="4" class="left-align">Age</th>
                <td colspan="5" class="left-align"><?= htmlspecialchars($age) ?></td>
                <th colspan="4" class="left-align">Nature of Work</th>
                <td colspan="5" class="left-align"><?= htmlspecialchars($row['nature_of_business'] ?? '-') ?></td>
            </tr>
            <tr>
                <th colspan="9" class="left-align">Whether employed on daily, monthly, contract or piece-rate wages, with rate</th>
                <td colspan="9" class="left-align">Monthly</td>
            </tr>
            <tr>
                <th colspan="9" class="left-align">Month</th>
                <td colspan="9" class="left-align"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>

            <tr>
                <th rowspan="2">Date</th>
                <th colspan="3">Spread Over</th>
                <th colspan="3">Rest Interval</th>
                <th rowspan="2">Total Working Hours</th>
                <th colspan="3">Overtime</th>
                <th colspan="4">Leave</th>
                <th colspan="3">Signature of</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Total</th>
                <th>From</th>
                <th>To</th>
                <th>Total</th>
                <th>From</th>
                <th>To</th>
                <th>Total</th>
                <th>Remuneration</th>
                <th>Duration</th>
                <th>Date Applied</th>
                <th>Date Granted</th>
                <th>Remarks</th>
                <th>Employer</th>
                <th>Employee</th>
            </tr>

            <?php for ($day = 1; $day <= 31; $day++):
                $workedRaw = isset($row['day_' . $day]) ? trim($row['day_' . $day]) : '';
                $otDisplay = '-';
                if ($workedRaw !== '' && is_numeric($workedRaw)) {
                    $workedNum = (float)$workedRaw;
                    if ($workedNum > 8) {
                        $ot = $workedNum - 8;
                        $otDisplay = ($ot == (int)$ot) ? (string)(int)$ot : (string)$ot;
                    }
                }
            ?>
                <tr>
                    <td>Day <?= $day ?></td>

                    <?php if ($day === 1): ?>
                        <td colspan="6" rowspan="31"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
                    <?php endif; ?>

                    <td><?= htmlspecialchars($workedRaw) ?></td>
                    <td colspan="3"><?= htmlspecialchars($otDisplay) ?></td>
                    <td><?= htmlspecialchars($row['daily_wage'] ?? 'Nil') ?></td>
                    <td>0</td>
                    <td>-</td>
                    <td>-</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endfor; ?>

            <tr class="total-row">
                <td colspan="9" class="left-align">1 | Total overtime hours during the month</td>
                <td colspan="9"><?= htmlspecialchars($row['ot_hours'] ?? '0') ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="9" class="left-align">2 | Leave availed during the month</td>
                <td colspan="9"><?= $plCount ?> </td>
            </tr>
        </table>

        <?php if (!$isLast): ?>
            <div class="page-break"></div>
        <?php endif; ?>

    <?php endforeach; ?>

</body>

</html>