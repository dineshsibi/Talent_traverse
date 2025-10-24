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
$currentState = 'Madhya Pradesh'; // Hardcoded for this state template

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
        .signature-row th {
            text-align: right;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="11">
                    Form A<br>
                    The Madhya Pradesh Child Labour (Prohibition and Regulation) Rules, 1993 [See Rule 3 (1)]<br>
                    Register of Children Employed
                </th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Name and address of Establishment</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Name and address of Employer</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Nature of work done by the Establishment</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($nature_of_business) ?></td>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Month & Year</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl. No.</th>
                <th>Name of child</th>
                <th>Father's name</th>
                <th>Date of birth</th>
                <th>Permanent address</th>
                <th>Date of joining in the establishment</th>
                <th>Nature of work employed</th>
                <th>Daily hours of work</th>
                <th>Intervals of rest</th>
                <th>Wages paid</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td></td>
            </tr>
            <tr>
                <th class="signature-row" colspan="11" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
            </tr>    
        </tbody>
    </table>
</body>
</html>