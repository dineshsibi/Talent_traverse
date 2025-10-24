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
$currentState = 'Uttarkhand'; // Hardcoded for this state template

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
                <td class="form-header" colspan="18">
                    Form E<br>
                    Uttarkhand Dookan Aur Vanijya Adhishthan Niyamavali, 1963, [ See Rule 13]<br>
                    Register of Fines and Realization Thereof
                </td>
            </tr>
            <tr>
                <th colspan="7">Name and Address of the Factory/ Establishment </th>
                <td colspan="11" style="text-align: left;"><?= htmlspecialchars($client_name .' , ' . $branch_address)?></td>
            </tr>
            
            <tr>
                <th colspan="7">Month & Year </th>
                <td colspan="11" style="text-align: left;"><?= htmlspecialchars($month .' - ' . $year)?></td>
            </tr>
            <tr>
                <th rowspan="2">Serial No.</th>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Name of Employee with number in register of employees</th>
                <th rowspan="2">Rate of wages</th>
                <th rowspan="2">Act or omission for which fined</th>
                <th colspan="2">Fines imposed</th>
                <th colspan="3">Fines realized or remitted</th>
                <th rowspan="2">Rate of realization or disbursement</th>
                <th rowspan="2">Amount realized</th>
                <th rowspan="2">Reference to serial number(Col. 1)</th>
                <th rowspan="2">Amount disbursed</th>
                <th rowspan="2">Object for which disbursed</th>
                <th rowspan="2">Amount in hand in the fund</th>
                <th rowspan="2">Remarks</th>
                <th rowspan="2">Signature of employer or of his agent</th>
            </tr>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Amount realized</th>
                <th>Amount remitted</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
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
                <th>13</th>
                <th>14</th>
                <th>15</th>
                <th>16</th>
                <th>17</th>
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
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>