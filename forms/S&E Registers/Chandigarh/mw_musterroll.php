<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__.'/../../../includes/config.php';
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
$currentState = 'Chandigarh'; // Hardcoded for this state template

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
    <style>
        body { font-family: 'Times New Roman', Times, serif; margin: 20px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; min-width: 20px; word-wrap: break-word; }
        th { background-color: #ffffffff; font-weight: bold; }
        .left-align { text-align: left; }
        .form-header-cell { text-align: center; font-weight: bold; font-size: 14px; padding: 8px; }
        .label{
            font-weight: bold;
        }
    </style>
</head>
<body>

<table>
    <!-- Form Header Row (colspan = 39 exactly) -->
    <tr>
        <td colspan="39" class="form-header-cell">
            Form V <br>
            The Punjab Minimum Wages Rules, 1950 [Rule 26(5)] <br>
            Muster Roll
        </td>
    </tr>

    <!-- Establishment Info -->
    <tr>
        <td colspan="12" class="label" style="text-align: left;">Name and Address of the Establishment</td>
        <td colspan="27" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
    </tr>
    <tr>
        <td colspan="12" class="label" style="text-align: left;">For the Period ending</td>
        <td colspan="27" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
    </tr>

    <!-- Table Header Row -->
    <tr>
        <th rowspan="2">Sl.No.</th>
        <th rowspan="2">Employee Code</th>
        <th rowspan="2">Employee Name</th>
        <th rowspan="2">Father's/ Husband's Name</th>
        <th rowspan="2">Sex</th>
        <th rowspan="2">Nature of<br>Work</th>
        <th colspan="31">Date</th>
        <th rowspan="2">Total Attendance</th>
        <th rowspan="2">Remarks</th>
    </tr>
    <tr>
        <?php for ($d = 1; $d <= 31; $d++): ?>
            <th><?= $d ?></th>
        <?php endfor; ?>
    </tr>

    <!-- Data Rows (Make sure 39 <td>s only) -->
    <?php if (!empty($stateData)): ?>
        <?php $i = 1; foreach ($stateData as $row): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td class="left-align"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
            <?php for ($d = 1; $d <= 31; $d++): ?>
                <td><?= htmlspecialchars($row['day_'.$d] ?? '') ?></td>
            <?php endfor; ?>
            <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
            <td></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="39" style="text-align:center;">No employee data found for Chandigarh</td>
        </tr>
    <?php endif; ?>

    <!-- Total Row -->
    <tr>
        <th colspan="6">Total</th>
        <?php for ($d = 1; $d <= 31; $d++): ?>
            <td>0</td>
        <?php endfor; ?>
        <td>0</td>
        <td></td>
    </tr>

    <!-- Signature Row -->
    <tr>
        <th colspan="39" style="text-align: right;">Signature of Employer</th>
    </tr>
</table>


</body>
</html>
