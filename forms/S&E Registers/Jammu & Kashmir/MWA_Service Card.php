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
$currentState = 'Jammu and Kashmir'; // Hardcoded for this state template

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
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
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
            font-style: italic;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <th colspan="8" class="main-heading">
                FORM "P"<br>
                The Jammu and Kashmir Shops and Establishments Rules, 1968, [See rule 13]<br>
                SERVICE CARD
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="4" class="info-row">Name and Address of Shop/Establishment, if any</td>
            <td colspan="4" class="info-data"> <?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="info-row">Name of Shop-keeper/employer</td>
            <td colspan="4" class="info-data"> <?= htmlspecialchars($employer_name) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="info-row">Month & year</td>
            <td colspan="4" class="info-data"> <?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="info-row">Registration No.</td>
            <td colspan="4" class="info-data">-</td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th>Sl No</th>
            <th>Employee Code</th>
            <th>Employee Name</th>
            <th>Employee's Father name</th>
            <th>Address of the employee</th>
            <th>Date of appointment</th>
            <th>Nature of employment</th>
            <th>Salary Fixed</th>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>

                    <!-- Data Row -->
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                    </tr>

                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="25" class="no-data">No contractor data available for Jammu & Kashmir</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="4" style="text-align: left;">Date</th>
                <th colspan="4" style="text-align: right;">Authorised signatory</th>
            </tr>
        </tbody>
    </table>

    <!-- Signature Section -->

</body>

</html>