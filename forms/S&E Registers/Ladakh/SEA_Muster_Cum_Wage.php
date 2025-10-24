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
$currentState = 'Ladakh'; // Hardcoded for this state template

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
            width: 15px;
        }

        .small-font {
            font-size: 6px;
        }

        /* ✅ Proper page break definition */
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
        $dob = $row['date_of_birth'] ?? '';
        $age = '';

        if (!empty($dob)) {
            // Create DOB from your DB format (d-M-y, e.g., 20-Jan-04)
            $dobDate = DateTime::createFromFormat('d-M-y', $dob);

            if ($dobDate) {
                // ✅ Get last day of the selected month & year
                $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);

                if ($referenceDate) {
                    $age = $dobDate->diff($referenceDate)->y;
                }
            }
        }
    ?>
        <table>
            <!-- Main Heading -->
            <tr>
                <th colspan="41" class="main-heading">
                    FORM "L"<br>
                    The Jammu and Kashmir Shops and Establishments Rules, 1968, [See rule 20 (1)]<br>
                    REGISTER OF EMPLOYEES (ATTENDANCE,OVERTIME AND WAGES)
                </th>
            </tr>

            <!-- Employee Information Section -->
            <tr>
                <td colspan="10" class="info-row"> Name of the Establishment:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Employee Code</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Name of the employee:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Father's/Husband's name:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Age:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($age) ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Address of the employee:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Nature of employment:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Rate of wages (whether daily/monthly rated):</td>
                <td colspan="31" class="info-data">Monthly</td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Wage period:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Date of appointment:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="10" class="info-row"> Date of discharge:</td>
                <td colspan="31" class="info-data"><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
            </tr>

            <!-- Column Headers - First Row -->
            <tr>
                <th colspan="31">Date</th>
                <th rowspan="2">Time at which employment commenced</th>
                <th rowspan="2">Time at which employment ceased</th>
                <th colspan="2">Rest intervals</th>
                <th rowspan="2">Overtime Worked, if any</th>
                <th colspan="2">Advance</th>
                <th rowspan="2">Net amount due</th>
                <th rowspan="2">Signature or thumb impression of the employee</th>
                <th rowspan="2">Signature or thumb impression of the employer</th>
            </tr>

            <!-- Column Headers - Second Row -->
            <tr>
                <!-- Date Columns -->
                <?php for ($d = 1; $d <= 31; $d++): ?>
                    <th><?= $d ?></th>
                <?php endfor; ?>

                <!-- Rest intervals -->
                <th>From</th>
                <th>To</th>

                <!-- Advance -->
                <th>Amount</th>
                <th>Date</th>
            </tr>

            <!-- Data Row -->
            <tr>
                <?php for ($d = 1; $d <= 31; $d++): ?>
                    <td><?= htmlspecialchars($row['day_' . $d] ?? '') ?></td>
                <?php endfor; ?>

                <!-- Time and Rest -->
                <td colspan="4"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>

                <!-- Overtime -->
                <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>

                <!-- Advance -->
                <td>Nil</td>
                <td>Nil</td>

                <!-- Final columns -->
                <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <?php if ($currentEmployee < $totalEmployees): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</body>

</html>
