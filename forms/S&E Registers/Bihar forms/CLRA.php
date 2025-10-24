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
$currentState = 'Bihar'; // Hardcoded for this state template

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
            font-size: 14px;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            font-family: 'Times New Roman', Times, serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: 'Times New Roman', Times, serif;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-family: 'Times New Roman', Times, serif;
        }
        th {
            background-color: #ffffff;
            font-family: 'Times New Roman', Times, serif;
        }
        .empty-row {
            height: 30px;
        }
        .signature-date {
            text-align: left;
            font-weight: normal;
        }
        .signature-auth {
            text-align: right;
            font-weight: normal;
        }
        .no-data {
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="7">
                    Form - XII<br>
                    The Contract Labour (R&A) Act, 1970, (Bihar) Rules, 1972, (See Rule 74)<br>
                    Register Of Contractors
                </th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name and Address of the Principal Employer</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name and address of Establishment</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">For the Month Of</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">Sl. No.</th>
                <th rowspan="2">Name and Address of Contractor</th>
                <th rowspan="2">Nature of Work on Contract</th>
                <th rowspan="2">Location of Contract work</th>
                <th colspan="2">Period of Contract</th>
                <th rowspan="2">Maximum No. of workmen employed by Contractor</th>
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
                    <tr>
                        <th class="signature-date" colspan="3"><b>Date</th>
                        <th class="signature-auth" colspan="4" style="text-align: right;"><b>Authorised Signatory</th>
                    </tr>
            <?php else: ?>
                <tr>
                    <td class="no-data" colspan="7">No contractor data found on Bihar</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>