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
$currentState = 'Manipur'; // Hardcoded for this state template

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

    $employer = $first_row['employer_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';

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

        .signature {
            text-align: right;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <th colspan="7" class="main-heading">
                FORM -I<br>
                The Manipur Shops and Establishments Act 1972 & Rules, 1973, (Rule 3)<br>
                REGISTER OF WAGES
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="3" class="info-row">Name of shop/Establishment</td>
            <td colspan="4" class="info-data"><?= htmlspecialchars($client_name) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="info-row">Address</td>
            <td colspan="4" class="info-data"><?= htmlspecialchars($branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="info-row">Name of shopkeeper/Employer</td>
            <td colspan="4" class="info-data"><?= htmlspecialchars($employer) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="info-row">Week/Month ending</td>
            <td colspan="4" class="info-data"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th>Sl No</th>
            <th>Name of persons employed</th>
            <th>Employee Code</th>
            <th>Rate of wages per day,week or month</th>
            <th>Total amount paid as wages</th>
            <th>Acquittance</th>
            <th>Remarks</th>
        </tr>

        <!-- Column Numbers -->
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
        </tr>

        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                        <td>-</td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No employee data found for Manipur</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th class="signature" colspan="7"> Signature of Shopkeeper/Employer</th>
            </tr>
        </tbody>

    </table>
</body>

</html>