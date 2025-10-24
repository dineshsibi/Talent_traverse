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
$currentState = 'Gujarat'; // Hardcoded for this state template

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
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-title {
           
            font-size: 18px;
            margin-bottom: 5px;
        }
        .act-reference {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .subtitle {
            text-align: center;
        
            margin-bottom: 20px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffffff;
        }
        .note {
            font-style: italic;
            margin: 10px 0;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
        }
        .date-line {
            border-top: 1px solid black;
            width: 150px;
            display: inline-block;
            margin-right: 10px;
        }

        .align{
            text-align:left;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="4" class="header">
                FORM N<br>
                The Maternity Benefit Act, 1961 And (Gujarat) Rules, 1964, (See Rule 15)<br>
                Maternity Register
                </th>
            </tr>
            <tr>
                <th class="align">Establishment Name and Address </th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
            
            </tr>
            <tr>
                <th class="align">Month</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>  
            <tr>
                <th>Place of employment of the woman employee</th>
                <th>Number of cases instituted</th>
                <th>Cases which resulted in conviction</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td></td>
            </tr>
            <tr>
                <th colspan="4" class="form-header">
                    For establishment<br>
                    N.B.- Reasons for prosecution should be given in full in the Appendix below :-
                </th>
            <tr>
                <th colspan="2">Date:</th> <th colspan="2">Signature of employer</th>
            </tr>
    </tbody>
    </table>
</body>
</html>