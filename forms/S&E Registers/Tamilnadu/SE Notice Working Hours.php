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
$currentState = 'Tamilnadu'; // Hardcoded for this state template

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
            background-color: #ffffffff;
        }

        .empty-row {
            height: 30px;
        }

        .label-cell {
            font-weight: bold;
        }

        * {
            font-family: "Times New Roman", Times, serif;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="16" class="form-header">
                Form S<br>
                The Tamil Nadu Shops And Establishments Rules, 1948 [See Sub Rule (4) Of Rule 18]<br>
                Notice Of Daily Hours Of Work, Rest Interval, Weekly Holiday Etc
            </td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name & Full Address of the Establishment</th>
            <td colspan="10" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name of the Employer/Contractor/Managing Director/Managing Partner/Authorised Person with full Residencial address</th>
            <td colspan="10" style="text-align: left;"><?= htmlspecialchars(($first_row['employer_name'] ?? '') . ' , ' . ($first_row['employer_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">For the month of</th>
            <td colspan="10" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Date of Payment of Wages:</th>
            <td colspan="10" style="text-align: left;"><?= htmlspecialchars($first_row['payment_date'] ?? '') ?></td>
        </tr>
        <tr>
            <th rowspan="2">S. No</th>
            <th rowspan="2">Name of the Person Employed</th>
            <th rowspan="2">Sex</th>
            <th rowspan="2">Father's Husband's name</th>
            <th rowspan="2">Designation</th>
            <th rowspan="2">Employee's number</th>
            <th rowspan="2">Date of entry into service</th>
            <th rowspan="2">Adult/Adolescent/child</th>
            <th rowspan="2">Shift Number</th>
            <th rowspan="2">Time of Commencement of Work</th>
            <th rowspan="2">Rest Interval</th>
            <th rowspan="2">Time which work ends</th>
            <th rowspan="2">Weekly Holiday</th>
            <th rowspan="2">Class of Workers</th>
            <th colspan="2">Rates of Wages</th>
        </tr>
        <tr>
            <th>Max</th>
            <th>Min</th>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td>Adult</td>
                        <td colspan="4"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
                        <td>-</td>
                        <td>Skilled</td>
                        <td>As Per Act</td>
                        <td>As Per Act</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="42" class="no-data">No data available for Tamilnadu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>