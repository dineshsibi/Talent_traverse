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
$currentState = 'Jharkhand'; // Hardcoded for this state template

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

        .form-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
        }

        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-header {
            margin-bottom: 20px;
        }

        .form-header div {
            margin-bottom: 10px;
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
            background-color: #ffffff;
            text-align: center;
        }

        .address {
            margin-left: 20px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-title" colspan="10">
                    Form IX <br>
                    The Jharkhand Shops and Establishments Rules, 2001 [ Rule 14 ] <br>
                    LEAVE WITH WAGES REGESTER.
                </th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name and address of the Establishment </th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $first_row['branch_address'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Month & Year </th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>

            <tr>
                <th rowspan="3">Serial no.</th>
                <th rowspan="3">Employee Code</th>
                <th rowspan="3">Name of employee</th>
                <th rowspan="3">Whether adult or child</th>
                <th colspan="3">Date on which leave</th>
                <th rowspan="3">Nature of leave</th>
                <th rowspan="3">Total leave taken during the year</th>
                <th rowspan="3">Balance carried over</th>
            </tr>
            <tr>
                <th rowspan="2">Applied for</th>
                <th colspan="2">Availed</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
            </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
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

                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code']) ?></td>
                        <td><?= htmlspecialchars($row['employee_name']) ?></td>
                        <td>Adult</td>
                        <td class="note">
                            <?= !empty($plDaysDisplay) ? htmlspecialchars($plDaysDisplay) : '-' ?><br>
                            <?= !empty($slDaysDisplay) ? htmlspecialchars($slDaysDisplay) : '-' ?>
                        </td>
                        <td class="note" colspan="2">
                            <?= !empty($plDaysDisplay) ? htmlspecialchars($plDaysDisplay) : '-' ?><br>
                            <?= !empty($slDaysDisplay) ? htmlspecialchars($slDaysDisplay) : '-' ?>
                        </td>
                        <td>PL<br>SL </td>
                        <td><?= htmlspecialchars($row['pl_availed']) ?><br><?= htmlspecialchars($row['sl_availed']) ?></td>
                        <td><?= htmlspecialchars($row['pl_closing']) ?><br>
                            <?= htmlspecialchars($row['sl_closing']) ?></td>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <th colspan="10" style="text-align:center;">No employee data found for Jharkhand</th>
                    </tr>
                <?php endif; ?>
        </tbody>
    </table>
</body>

</html>