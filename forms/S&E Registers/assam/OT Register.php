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
$currentState = 'Assam'; // Hardcoded for this state template

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

    // Get one sample row to extract address
    $first_row = !empty($stateData) ? reset($stateData) : [];
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


?>

<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #0c0000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffff;
        }

        .title {
            text-align: center;
            font-weight: bold;
        }

        .input-field {
            color: blue;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <td class="title" colspan="10">
                    Form L<br>
                    Register of overtime work and payment of overtime wages<br>
                    The Assam Shops and Establishments Act, 1971 with Rules, 1976 [See Rule 40]
                </td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: left;"><b>Name of the Establishment </td>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name) ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: left;"><b>Name of the Employer</td>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: left;"><b>Address</td>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($first_row['branch_address'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: left;"><b>Registration. No</td>
                <td colspan="5" style="text-align: left;">-</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: left;"><b>Month & Year </td>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Employee Code</th>
                <th>Name of the Employee</th>
                <th>Rate of Wages</th>
                <th>Money value of meals & concessional supply of food grains etc., if any</th>
                <th>Overtime rate of wages per hour</th>
                <th>Dates on which overtime work was done</th>
                <th>Extent of overtime work done on each day (in hours)</th>
                <th>Total amount of overtime wages the employee entitled to</th>
                <th>Total amount of overtime wages paid</th>
                <th>Signature of the Employee</th>
            </tr>
        </thead>

        <body>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $fixed_gross = (float)($row['fixed_gross'] ?? 0);
                    $calculation1 = (($fixed_gross / 31) / 8) * 2;

                    $overtimeDays = [];

                    // Check each day (day_1 to day_31) for overtime
                    for ($day = 1; $day <= 31; $day++) {
                        $dayColumn = 'day_' . $day;
                        if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                            $hours = (float)$row[$dayColumn];
                            if ($hours > 8.0) {
                                $overtimeDays[] = $day; // Store the day number if hours > 8
                            }
                        }
                    }

                    // Convert array of days to comma-separated string
                    $overtimeDaysStr = !empty($overtimeDays) ? implode(',', $overtimeDays) : '-';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($first_row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($first_row['fixed_gross'] ?? '') ?></td>
                        <td>Nill</td>
                        <td><?= htmlspecialchars(round($calculation1 ?? '')) ?></td>
                        <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                        <td><?= htmlspecialchars($first_row['extent_ot_on_which_occasion'] ?? '') ?></td>
                        <td><?= htmlspecialchars($first_row['ot_hours'] ?? '') ?></td>
                        <td><?= htmlspecialchars($first_row['over_time_allowance'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align:center;">No data available for Assam</td>
                </tr>
            <?php endif; ?>
        </body>

    </table>
</body>

</html>