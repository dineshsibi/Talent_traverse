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
$currentState = 'Kerala'; // Hardcoded for this state template

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
    $location = $first_row['location_name'] ?? '';
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
            padding: 4px;
            text-align: center;
            min-width: 20px;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .empty-row td {
            height: 25px;
        }

        .left-align {
            text-align: left;
        }

        .date-col {
            width: 20px;
        }

        .signature-row td {
            border: none;
            height: 40px;
            padding-top: 20px;
        }

        .total-row {
            font-weight: bold;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td class="form-header" colspan="12">
                Form F<br>
                See rule 10(9)<br>
                Register of Holidays, Leave etc. Granted
            </td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name of the Establishment and Address:</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Month/Year:</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>S. No</th>
            <th>Employee Code</th>
            <th>Name of person employed</th>
            <th>Father's name</th>
            <th>Date of application</th>
            <th>Number of days of leave applied for</th>
            <th>Reason</th>
            <th>Whether granted</th>
            <th>If refused the reason for refusal</th>
            <th>Nature of leave applied for</th>
            <th>Nature of leave granted</th>
            <th>Remarks</th>
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
                    $clDaysDisplay = !empty($clDays) ? implode(',', $scDays) : '';

                    ?>

                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td class="note">
                            <?= !empty($plDaysDisplay) ? htmlspecialchars($plDaysDisplay) : '-' ?><br>
                            <?= !empty($slDaysDisplay) ? htmlspecialchars($slDaysDisplay) : '-' ?> <br>
                            <?= !empty($clDaysDisplay) ? htmlspecialchars($clDaysDisplay) : '-' ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($row['pl_availed'] ?? '') ?> <br>
                            <?= htmlspecialchars($row['sl_availed'] ?? '') ?> <br>
                            <?= htmlspecialchars($row['cl_availed'] ?? '') ?>
                        </td>
                        <td>Personal</td>
                        <td>Yes</td>
                        <td>-</td>
                        <td>
                            PL <br>
                            SL <br>
                            CL
                        </td>
                        <td>
                            PL <br>
                            SL <br>
                            CL
                        </td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="16" style="text-align:center;">No data available for Kerala</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>