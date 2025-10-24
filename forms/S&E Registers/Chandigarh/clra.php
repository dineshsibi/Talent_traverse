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
$currentState = 'Chandigarh'; // Hardcoded for this state template

try {
    // Build the SQL query with parameters for CLRA
    $sql = "SELECT * FROM clra 
            WHERE client_name = :client_name
            AND state LIKE :state
            AND location_code = :location_code";

    // Add month/year filter if specified
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }

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

    // Common filter values
    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');

    // Employer & establishment details (default from CLRA if available)
    $employer_name = safe($first_row['employer_name'] ?? '');
    $employer_address = safe($first_row['employer_address'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');

    // If CLRA has no data, get from input table using location_code
    if (empty($stateData)) {
        $sqlInput = "SELECT employer_name, employer_address, branch_address 
                     FROM input 
                     WHERE client_name = :client_name
                     AND location_code = :location_code
                     LIMIT 1";
        $stmtInput = $pdo->prepare($sqlInput);
        $stmtInput->bindValue(':client_name', $filters['client_name']);
        $stmtInput->bindValue(':location_code', $currentLocation);
        $stmtInput->execute();
        $inputRow = $stmtInput->fetch(PDO::FETCH_ASSOC);

        if ($inputRow) {
            $employer_name = safe($inputRow['employer_name'] ?? '');
            $employer_address = safe($inputRow['employer_address'] ?? '');
            $branch_address = safe($inputRow['branch_address'] ?? '');
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }

        th, td {
            border: 1px solid black;
            padding: 6px;
            vertical-align: top;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }

        th {
            background-color: #ffffffff;
            text-align: center;
            font-weight: bold;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
        }

        .label-cell {
            font-weight: bold;
        }
    </style>
</head>
<body>

<table>
    <thead>
        <!-- Form Title -->
        <tr>
            <th colspan="7" class="form-header">
                FORM - XII <br>
                The Contract Labour (R&A) Act, 1970, (Punjab) Rules, 1973 {See Rule 74} <br>
                Register of Contractors
            </th>
        </tr>

        <!-- Employer / Establishment Details -->
        <tr>
            <th colspan="3" style="text-align: left;">Name and Address of the Principal Employer</th>
            <td colspan="4" style="text-align: left;">
                <?= $employer_name ?><?= $employer_address ? ' , ' . $employer_address : '' ?>
            </td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">Name and address of establishment</th>
            <td colspan="4" style="text-align: left;">
                <?= $client_name ?><?= $branch_address ? ' , ' . $branch_address : '' ?>
            </td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">For the Month Of</th>
            <td colspan="4" style="text-align: left;">
                <?= $month ?><?= $year ? ' , ' . $year : '' ?>
            </td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th rowspan="2">Sl. No.</th>
            <th rowspan="2">Name of Contractor<br>and Address</th>
            <th rowspan="2">Nature of Contractor Work</th>
            <th rowspan="2">Location of Contract Work</th>
            <th colspan="2">Period of Contract</th>
            <th rowspan="2">Max No. of Workmen Employed</th>
        </tr>
        <tr>
            <th>From</th>
            <th>To</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                    $contractor = safe($row['name_of_contractor'] ?? '');
                    $address = safe($row['address_of_contractor'] ?? '');
                    $nature = safe($row['nature_of_work_on_contract'] ?? '-');
                    $location = safe($row['location_name'] ?? '-');
                    $from_date = !empty($row['from_date']) ? date('d-m-Y', strtotime($row['from_date'])) : '-';
                    $to_date = !empty($row['to_date']) ? date('d-m-Y', strtotime($row['to_date'])) : '-';
                    $max_workmen = safe($row['maximum_number_of_workmen_employed_by_contractor'] ?? '-');
                ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?= $contractor ?><?= $address ? ', ' . $address : '' ?></td>
                    <td><?= $nature ?></td>
                    <td><?= $location ?></td>
                    <td><?= $from_date ?></td>
                    <td><?= $to_date ?></td>
                    <td><?= $max_workmen ?></td>
                </tr>
                <?php $i++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <th colspan="7" style="text-align:center;">No employee data found for Chandigarh</th>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
