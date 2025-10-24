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
$currentState = 'Karnataka'; // Hardcoded for this state template

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


    $client_name = safe($first_row['client_name'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            font-weight: bold;
        }

        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <th colspan="11" class="title">FORM 1<br>The Karnataka Payment of Subsistence Allowance Rules, 2004, [See rule 3(3)]<br>Register of employed placed under suspension</th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name and address of the establishment.</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Month/Year :</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th rowspan="2">S. No</th>
            <th rowspan="2">Name and Designation of the Suspended employee</th>
            <th rowspan="2">Monthly wages paid to the employee</th>
            <th rowspan="2">Nature of Misconduct and date of Suspension</th>
            <th colspan="2">Date of enquiry</th>
            <th rowspan="2">Result of enquiry</th>
            <th rowspan="2">Percentage of subsistence allowance</th>
            <th rowspan="2">Amount of subsistence allowance paid</th>
            <th rowspan="2">Signature of employee with date</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>

            <th>Commencement</th>
            <th>Completion</th>

        </tr>
        <tr>
            <td>1</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th colspan="11" style="text-align: right;"><br><br>Signature of the Employer/ Authorised Signatory</th>
        </tr>
    </table>
</body>

</html>