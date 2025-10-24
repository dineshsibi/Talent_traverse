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
$currentState = 'West Bengal'; // Hardcoded for this state template

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
<style>
    body {
        font-family: "Times New Roman", Times, serif;
        margin: 0;
        padding: 20px;
    }
    .form-container:last-child {
        page-break-after: auto; /* Avoid blank page after last employee */
    }
    .form-header {
        text-align: center;
        font-weight: bold;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        page-break-inside: avoid;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #ffffff;
    }
    tr {
        page-break-inside: avoid;
    }
    thead {
        display: table-header-group;
    }
</style>
</head>
<body>

<?php 
$totalEmployees = count($stateData);
$currentEmployee = 0;

foreach ($stateData as $row): 
    $currentEmployee++;

    // Employee-specific calculations
    $epf = (float)($row['epf'] ?? 0);
    $vpf = (float)($row['vpf'] ?? 0);
    $pf = $epf + $vpf;
    $total = (float)($row['total_deduction'] ?? 0);
    $other = $total - $pf;
?>
<div class="form-container">
    <table>
        <thead>
            <tr>
                <td class="form-header" colspan="3">
                    Form X <br>
                    The West Bengal Minimum Wages Rules, [See Rule 23(1)] <br>
                    Register of Wages					
                </td>
            </tr>
            <tr>
                <th colspan="2">Name and address of the Establishment</th>
                <td><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="2">Place</th>
                <td><?= htmlspecialchars($row['location_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="2">Month & Year</th>
                <td><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>1</th>
                <th>Employee Code</th>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <th>2</th>
                <th>Name of the employee with Father’s/Husband’s Name:</th>
                <td><?= htmlspecialchars(($row['employee_name'] ?? '') . ' , ' . ($row['father_name'] ?? '')) ?></td>
            </tr>
            <tr>
                <th>3</th>
                <th>Designation</th>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            </tr>
            <tr>
                <th rowspan="3">4</th>
                <th colspan="2">Minimum Rate of Wages payable:-</th>
            </tr>
            <tr>
                <th >(a) Basic:</th>
                <td><?= htmlspecialchars($row['fixed_basic'] ?? '') ?></td>
            </tr>
            <tr>
                <th >(b) D.A:</th>
                <td><?= htmlspecialchars($row['fixed_da'] ?? '') ?></td>
            </tr>
            <tr>
                <th rowspan="3">5</th>
                <th colspan="2">Rates of wages actually paid:-</th>
            </tr>
            <tr>
                <th >(a) Basic wages :</th>
                <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
            </tr>
            <tr>
                <th >(b) D.A:</th>
                <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            </tr>
            <tr>
                <th>6</th>
                <th>Total Attendance/Units of work done:</th>
                <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
            </tr>
            <tr>
                <th>7</th>
                <th>Over-time wages :</th>
                <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
            </tr>
            <tr>
                <th>8</th>
                <th>Gross Wages payable:</th>
                <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            </tr>
            <tr>
                <th>9</th>
                <th colspan="2">Deductions:-</th>
            </tr>
            <tr>
                <th>10</th>
                <th>PF:</th>
                <td><?= htmlspecialchars($pf) ?></td>
            </tr>
            <tr>
                <th>11</th>
                <th>HR:</th>
                <td>Nil</td>
            </tr>
            <tr>
                <th>12</th>
                <th>Other Deduction:</th>
                <td><?= htmlspecialchars($other) ?></td>
            </tr>
            <tr>
                <th>13</th>
                <th>Total Deduction:</th>
                <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            </tr>
            <tr>
                <th>14</th>
                <th>Net wages paid:</th>
                <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            </tr>
            <tr>
                <th>15</th>
                <th>Date Of Payment:</th>
                <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: right;">Employee’s Signature <br>Thumb Impression</th>
            </tr>
        </tbody>
    </table>
</div>
<?php if ($currentEmployee < $totalEmployees): ?>
    <div style="page-break-before: always;"></div>
<?php endif; ?>

<?php endforeach; ?>

</body>
</html>
