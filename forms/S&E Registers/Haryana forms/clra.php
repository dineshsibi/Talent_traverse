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
$currentState = 'Haryana'; // Hardcoded for this state template

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
        th, td {
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
        .colspan-6 {
            width: 100%;
        }
        
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
            <th class="form-header" colspan="7">
                FORM - 8<br>
                The Contract Labour (R&A) Act, 1970 (Haryana) Rules, 1975, {See Rule 73}<br>
                Register of Contractors
            </th>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Name and Address of the Principal Employer </th>
                <td colspan="4" style="text-align: left;"> <?= $employer_name ?><?= $employer_address ? ' , ' . $employer_address : '' ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Name and address of establishment</th>
                <td colspan="4" style="text-align: left;"><?= $client_name ?><?= $branch_address ? ' , ' . $branch_address : '' ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">For the Month Of</th>
                <td colspan="4" style="text-align: left;"><?= $month ?><?= $year ? ' - ' . $year : '' ?></td>
            </tr>
            <tr>
                <th rowspan="2">Sl. No.</th>
                <th rowspan="2">Name and address of contractor</th>
                <th rowspan="2">Nature of Work on Contract</th>
                <th rowspan="2">Location of Contract work</th>
                <th colspan="2">Period of Contract</th>
                <th rowspan="2">Maximum No. of workmen employed by contractor</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
            </tr>
        </thead>
        <tbody>
            <tr>
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
                    <td class="no-data" colspan="7">No contractor data available for Haryana</td>
                </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>