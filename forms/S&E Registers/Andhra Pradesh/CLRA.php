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
$currentState = 'Andhra Pradesh'; // Hardcoded for this state template

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
        * {
            font-family: "Times New Roman", Times, serif;
        }
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
                Form - XII <br>
                The Contract Labour (R&A) Act, 1970 (Andhra Pradesh) Rules, 1971 (See Rule 74) <br>
                Register Of Contractors                        
            </th>
        </tr>

        <!-- Employer/Establishment Details -->
        <tr>
            <th colspan="3" style="text-align: left;">Name and Address of the Principal Employer</th>
            <td colspan="4" style="text-align: left;">
               <?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?>
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
                <?= $month ?><?= $year ? ' - ' . $year : '' ?>
            </td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th rowspan="2">Sl. No.</th>
            <th rowspan="2">Name and Address of Contractor</th>
            <th rowspan="2">Nature of Work on Contract</th>
            <th rowspan="2">Location of Contract Work</th>
            <th colspan="2">Period of Contract</th>
            <th rowspan="2">Maximum No. of Workmen Employed</th>
        </tr>
        <tr>
            <th>From</th>
            <th>To</th>
        </tr>
    </thead>
    <tbody>
       <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name_of_contractor'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nature_of_work_on_contract'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['location_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['from_date'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['to_date'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['maximum_number_of_workmen_employed_by_contractor'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="no-data">No contractor data available for Andhra Pradesh <?= $currentState ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th colspan="3" style="text-align: left;">Date:</th>
            <th colspan="4" style="text-align: left;">Authorised Signatory</th>
        </tr>
    </tbody>
</table>

</body>
</html>