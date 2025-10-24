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
$currentState = 'Haryana'; // Hardcoded for this state template

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            font-size: 12px;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .empty-row {
            height: 25px;
        }
        .left-align {
            text-align: left;
        }
        .colspan-full {
            width: 100%;
        }
        .signature-row td {
            border: none;
            height: 30px;
            padding-top: 10px;
        }
        .sub-header {
            font-weight: normal;
        }
        
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="16">
                    Form E<br>
                    The Punjab Shops and Commercial Establishments Rules, 1958, Rule 5 <br>
                    Register of Deductions
                </th>
            </tr>
            <tr>
                <th colspan="9" style="text-align: left;">Name of the establishment</th>
                <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="9" style="text-align: left;">Month & Year</th>
                <td colspan="7" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>S. No</th>
                <th>Emp. ID</th>
                <th>Name of Employee</th>
                <th>Parentage</th>
                <th>Wage<br>period</th>
                <th>Wages<br>payable</th>
                <th>Amount</th>
                <th>Fault for<br>which<br>deductions<br>made</th>
                <th>Date of<br>deduction</th>
                <th>Whether employee showed cause against deductions</th>
                <th>Amount deductions and purpose for which utilised</th>
                <th>Date of utilization</th>
                <th>Balance with the employer</th>
                <th>Signature of the employee</th>
                <th>Signature of the employer</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <th>1</th>
                <th>1A</th>
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
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td class="signature-cell"></td>
                <td class="signature-cell"></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>