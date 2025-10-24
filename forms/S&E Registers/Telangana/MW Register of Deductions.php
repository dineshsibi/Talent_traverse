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
$currentState = 'Telangana'; // Hardcoded for this state template

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
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
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
            <th colspan="12" class="title">
                Form-II<br>
                The Andhra Pradesh Minimum Wages Rules, 1960 [Rule 22 (4) (iii) ]<br>
                Register of deductions for damage or loss caused to the Employer by the neglect or default of the employed persons
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name and address of the Factory /Establishment :</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Employer :</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' .$employer_address)?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Month/Year :</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        
        <tr>
            <th>Serial No</th>
            <th>Employee Code</th>
            <th>Name</th>
            <th>Father's/Husband's Name</th>
            <th>Sex</th>
            <th>Department</th>
            <th>Damage or Loss caused With Date</th>
            <th>Whether workmen showed cause against deduction, if so, enter date</th>
            <th>Date and amount of deductions imposed</th>
            <th>Numbers of instalments, if any</th>
            <th>Date on which total amount realised</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <th>1</th>
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
    </table>
</body>
</html>