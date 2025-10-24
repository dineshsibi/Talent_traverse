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
$currentState = 'Goa'; // Hardcoded for this state template

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
            background-color: #ffffff;
        }

        .empty-row {
            height: 30px;
        }

        .colspan-18 {
            width: 100%;
        }

        .sub-header {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <td class="form-header" colspan="18">
                    Form D <br>
                    The Delhi Labour Welfare Fund Rules, 1997 See Rule 27 (1) (a) <br>
                    Register Of Wages
                </td>
            </tr>
            <tr>
                <th colspan="9" style="text-align:left">Name and Address of the Factory/ Establishment </th>
                <td colspan="9" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="9" style="text-align:left">Month & Year </th>
                <td colspan="9" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">Serial No.</th>
                <th rowspan="2">Name of the employees</th>
                <th rowspan="2">Ticket and badge No.</th>
                <th rowspan="2">Occupation</th>
                <th colspan="4">Amount payable during the month</th>
                <th colspan="2">Amount Deducted during the month</th>
                <th colspan="4">Amount Actually paid during the month</th>
                <th colspan="4">Balance due to the Employee</th>
            </tr>
            <tr>
                <!-- Amount payable sub-headers -->
                <th class="sub-header">Basic Wages</th>
                <th class="sub-header">Overtime</th>
                <th class="sub-header">Dearness allowance and other allowance</th>
                <th class="sub-header">Bonus</th>

                <!-- Amount Deducted sub-headers -->
                <th class="sub-header">Fine</th>
                <th class="sub-header">Other Deductions</th>

                <!-- Amount Actually paid sub-headers -->
                <th class="sub-header">Basic wages</th>
                <th class="sub-header">Overtime</th>
                <th class="sub-header">Dearness allowance and other</th>
                <th class="sub-header">Bonus</th>

                <!-- Balance due sub-headers -->
                <th class="sub-header">Basic wages</th>
                <th class="sub-header">Overtime</th>
                <th class="sub-header">Dearness allowance and other</th>
                <th class="sub-header">Bonus</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $gross = (float) ($row['gross_wages'] ?? 0);
                    $basic = (float) ($row['basic'] ?? 0);
                    $ot = (float) ($row['over_time_allowance'] ?? 0);
                    $bonus = (float) ($row['statutory_bonus'] ?? 0);
                    $Dearness=$gross - ($basic+$ot+$bonus);

                    $total = (float) ($row['total_deduction'] ?? 0);
                    $fines = (float) ($row['fines_damage_or_loss'] ?? 0);
                    $other=$total-$fines;
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= number_format($Dearness) ?></td>
                        <td><?= htmlspecialchars($row['statutory_bonus'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
                        <td><?= number_format($other) ?></td>
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= number_format($Dearness) ?></td>
                        <td><?= htmlspecialchars($row['statutory_bonus'] ?? '') ?></td>
                        <td>Nil</td>
                        <td>Nil</td>
                        <td>Nil</td>
                        <td>Nil</td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No employee data found for Goa</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>