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
            padding: 20px;
            font-size: 12px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
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
            background-color: #ffffffff;
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
            background-color: #ffffffff;
        }

        .info-row {
            text-align: left;
            font-weight: bold;
        }

        .info-data {
            text-align: left;
        }

        .note {
            font-style: italic;
            font-size: 11px;
            text-align: left;
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

        // Get the month number from month name
        $monthNum = date('m', strtotime($month));

        // Process leave data to find PL, SL, and CL days
        $plDays = [];
        $slDays = [];
        $clDays = [];

        // Check all day fields (day_1 to day_31) for leave types
        for ($day = 1; $day <= 31; $day++) {
            $dayKey = 'day_' . $day;
            if (!empty($row[$dayKey])) {
                $value = strtoupper(trim($row[$dayKey])); // normalize
                if ($value === 'PL') {
                    $plDays[] = $day;
                } elseif ($value === 'SL') {
                    $slDays[] = $day;
                } elseif ($value === 'CL') {
                    $clDays[] = $day;
                }
            }
        }

        // Format days for display (e.g., "5,6")
        $plDaysDisplay = !empty($plDays) ? implode(',', $plDays) : '';
        $slDaysDisplay = !empty($slDays) ? implode(',', $slDays) : '';
        $clDaysDisplay = !empty($clDays) ? implode(',', $clDays) : '';

        // Merge SL + CL cleanly (no stray commas)
        $slClDisplay = '';
        if ($slDaysDisplay !== '' && $clDaysDisplay !== '') {
            $slClDisplay = $slDaysDisplay . ',' . $clDaysDisplay;
        } elseif ($slDaysDisplay !== '') {
            $slClDisplay = $slDaysDisplay;
        } elseif ($clDaysDisplay !== '') {
            $slClDisplay = $clDaysDisplay;
        }

        // Calculate wage period start and end dates based on selected month/year
        $fromDate = '';
        if (!empty($month) && !empty($year)) {
            $dateObj = DateTime::createFromFormat('!m Y', $month . ' ' . $year);
            if ($dateObj) {
                $fromDate = $dateObj->format('d-M-Y'); // First day of month
            }
        }
    ?>
        <table>
            <!-- Main Heading -->
            <tr>
                <th colspan="10" class="main-heading">
                    FORM "G"<br>
                    The Jammu and Kashmir Shops and Establishments Rules, 1968, [See rule 11(1)] <br>
                    Register of Leave With Wages
                </th>
            </tr>

            <!-- Establishment Details -->
            <tr>
                <td colspan="3" class="info-row">Name and address of the Establishment :</td>
                <td colspan="7" class="info-data"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="info-row">Month & Year :</td>
                <td colspan="7" class="info-data"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="info-row">Name of employee :</td>
                <td colspan="7" class="info-data"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="3" class="info-row">Employee Code :</td>
                <td colspan="7" class="info-data"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="3" class="info-row">Father's Name :</td>
                <td colspan="7" class="info-data"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>

            <!-- Column Headers -->
            <tr>
                <th rowspan="2">Date of entry in service</th>
                <th colspan="3">Casual or Sickness Leave</th>
                <th colspan="2">Privelege Leave</th>
                <th colspan="2">Discharged Workers</th>
                <th rowspan="2">Date and amount of payment in lieu of leave due</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Leave due with effect from</th>
                <th>Date from which leave allowed</th>
                <th>Period of leave allowed</th>
                <th>Date from which leave allowed</th>
                <th>Period of leave allowed</th>
                <th>Date of discharge</th>
                <th>No. of days counted against leave with wages</th>
            </tr>

            <!-- Column Numbers -->
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>8</th>
                <th>7</th>
                <th>9</th>
                <th>10</th>
            </tr>

            <!-- Data Row -->
            <tr>
                <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                <td><?= htmlspecialchars($fromDate) ?></td>
                <td class="note"><?= htmlspecialchars($slClDisplay) ?></td>
                <td class="note"><?= htmlspecialchars($slClDisplay) ?></td>
                <td class="note"><?= htmlspecialchars($plDaysDisplay) ?></td>
                <td class="note"><?= htmlspecialchars($plDaysDisplay) ?></td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>-</td>
            </tr>
        </table>

        <?php if ($currentEmployee < $totalEmployees): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>
