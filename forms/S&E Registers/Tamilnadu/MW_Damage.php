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
$currentState = 'Tamilnadu'; // Hardcoded for this state template

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

    } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            word-wrap: break-word;
            vertical-align: top;
        }
        th {
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>

<table>
    <tbody>
        <tr>
            <th colspan="12" class="form-header">
                FORM II <br>
                The Minimum Wages (Tamil Nadu) Rules, 1953, [Rule(21(4)] <br>
                Register of deductions for damage or loss caused to the Employers by the neglect or default of employed persons											
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name and Address of the Factory/ Establishment</th>
            <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Employer</th>
            <td colspan="7" style="text-align: left;"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Month & Year</th>
            <td colspan="7" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>Sl.No</th>
            <th>Sl.No of the Employee</th>
            <th>Name</th>
            <th>Father's / Husband's name</th>
            <th>sex</th>
            <th>Department</th>
            <th>Damage or Loss caused with Date</th>
            <th>Whether worker showed cause against deduction</th>
            <th>Date and amount of deduction imposed</th>
            <th>Number of installments, if any</th>
            <th>Date on which total amount realized</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>9</th>
            <th>10</th>
            <th>11</th>
            <th>12</th>
        </tr>
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
            <td>Nil</td>
            <td></td>
        </tr>
        <tr>
            <th style="text-align: left;" colspan="6">Date</th>
            <th style="text-align: right;" colspan="6">Authorised Signatory</th>
        </tr>
    </tbody>
</table>

</body>
</html>
