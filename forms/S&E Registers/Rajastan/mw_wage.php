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
$currentState = 'Rajastan'; // Hardcoded for this state template

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
<html>
<head>
    <style>
        body{
            font-family: 'Times New Roman', Times, serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            font-weight: bold;
        }
        .header {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="19" class="header">
                Form X<br>
                The Minimum Wages (Central) Rules, 1950 [Rule 26(1)]<br>
                Register of Wages
            </th>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left">Name and address of the Factory/ Establishment :</th>
            <td colspan="16" style='text-align:left'><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left">Place:</th>
            <td colspan="16" style='text-align:left'><?= htmlspecialchars(($first_row['location_name'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left">Month/ Year :</th>
            <td colspan="16" style='text-align:left'><?= htmlspecialchars($month . ' - ' . $year?? '') ?></td>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left">Wages Period</th>
            <td colspan="16" style='text-align:left'> Monthly</td>
        </tr>
        <tr>
            <th rowspan="2">S No.</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Name of<br>the employee</th>
            <th rowspan="2">Father's/<br>Husband's Name</th>
            <th rowspan="2">Designation</th>
            <th colspan="2">Minimum Rate of <br>Wages Payable</th>
            <th colspan="2">Rate of Wages <br>actually paid</th>
            <th rowspan="2">Total attendance units of work done</th>
            <th rowspan="2">Overtime<br> worked</th>
            <th rowspan="2">Gross wages payable</th>
            <th rowspan="2">Employee's Contribution to P.F</th>
            <th rowspan="2">H.R.</th>
            <th rowspan="2">Other deductions, if any, (indicate nature)</th>
            <th rowspan="2">Total Deductions</th>
            <th rowspan="2">Wages Paid</th>
            <th rowspan="2">Date of Payment</th>
            <th rowspan="2">Signature/Thumb Impression of work man</th>
        </tr>
        <tr>
            
            <th>Basic</th>
            <th>D.A.</th>
            <th>Basic</th>
            <th>DA</th>
           
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
            <th>18</th>
        </tr>
        <tbody>
                   <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
        
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?> </td>
            <td><?= htmlspecialchars($row['fixed_basic'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['fixed_da'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['epf'] ?? '') ?> & <?= htmlspecialchars($row['vpf'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['other_deductions'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
            <td></td>
        </tr>
         <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="no-data">No contractor data available for Rajastan</td>
            </tr>
        <?php endif; ?>
         <tr>
            <td colspan="19" style="text-align:left; font-weight: bold;"><br><br><br>Date :<br>Authorised Signatory</td>
        </tr>
        </tbody>
       
    </table>
</body>
</html>