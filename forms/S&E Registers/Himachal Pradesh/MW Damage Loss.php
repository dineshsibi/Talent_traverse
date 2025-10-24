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
$currentState = 'Himachal Pradesh'; // Hardcoded for this state template

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


// Get one sample row to extract address
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
            font-family: "Times New Roman", Times, serif;
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
        .colspan-10 {
            width: 100%;
        }
        *{
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <td class="form-header" colspan="14">
                From II <br>
                The Minimum Wages (Central) Rules, 1950 [See Rule 21 (4)] <br>
                Register of deductions for damage or loss caused to the Employers by the neglect or default of employed persons												

                </td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: left;"><b>Name and Address of the Factory/ Establishment </td>
                <td colspan="9" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: left;"><b>Month & Year </td>
                <td colspan="9" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Serial No.</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Father's / Husband's Name</th>
                <th>Sex</th>
                <th>Department</th>
                <th>Damage or Loss caused With Date</th>
                <th>Whether show- cause notice was served</th>
               <th> Whether workman showed cause against deduction, if so, enter date</th>

                <th>Date and amount of deduction imposed</th>
                <th>Number of installments, if any</th>
                <th>Date on which total amount realised</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
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
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
            </tr>
            <tr>
                <th colspan="7" style="text-align: left;">Date:</th>
                <th colspan="7" style="text-align: left;">Authorised Signatory</th>
            </tr>
        </tbody>
    </table>
</body>
</html>