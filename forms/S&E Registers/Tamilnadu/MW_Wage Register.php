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


    $location_name = $first_row['location_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';
    $location=$first_row['location_name'] ?? '';

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
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            font-weight: bold;
        }
        .header {
            font-weight: bold;
            text-align: center;
        }
        .subheader {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="22" class="header">
                Form X<br>
                The Minimum Wages (Tamilnadu) Rules, 1953 [Rule 30 (1)]<br>
                Register of Wages
            </td>
        </tr>
        <tr>
            <td colspan="7" class="subheader">Name of establishment & Address :</td>
            <td colspan="15" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address)?></td>
        </tr>
        <tr>
            <td colspan="7" class="subheader">Wages period :</td>
            <td colspan="15" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year)?></td>
        </tr>
        <tr>
            <td colspan="7" class="subheader">Place</td>
            <td colspan="15" style="text-align: left;"><?= htmlspecialchars($location)?></td>
            
        </tr>
        <tr>
            <th rowspan="2">Sl.No</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Name of the Employee</th>
            <th rowspan="2">Father's/husband's name</th>
            <th rowspan="2">Designation/ Nature of work done</th>
            <th colspan="2">Minimum rate of wages payable :</th>
            <th rowspan="2">Total attendance/units of work done</th>
            <th rowspan="2">Days of  Absence</th>
            <th colspan="2">Rates of wages actually paid :</th>
            <th rowspan="2">Overtime wages</th>
            <th rowspan="2">Other cash payments (Nature of payment to be indicated)</th>
            <th rowspan="2">Total</th>
            <th rowspan="2">Employee's contribution to P.F.</th>
            <th rowspan="2">LWF</th>
            <th rowspan="2">Other deductions, if any, (indicate nature)</th>
            <th rowspan="2">Total Deductions</th>
            <th rowspan="2">Net Amount paid</th>
            <th rowspan="2">Date of payment</th>
            <th rowspan="2">Signature/Thumb Impression of work man</th>
            <th rowspan="2">Date on which wages slip issued</th>
        </tr>
        <tr>
            <th>Basic</th>
            <th>D.A.</th>
            <th>Basic</th>
            <th>D.A.</th>
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
            <th>13</th>
            <th>14</th>
            <th>15</th>
            <th>16</th>
            <th>17</th>
            <th>18</th>
            <th>19</th>
            <th>20</th>
            <th>21</th>
            <th>22</th>
        </tr>
        <tbody> <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
        <?php
            $gross = (float)($row['gross_wages'] ?? 0);
            $basic= (float)($row['basic'] ?? 0);
            $da= (float)($row['da'] ?? 0);
            $overtime= (float)($row['over_time_allowance'] ?? 0);
            $othercashpayment = $gross-($basic+$da+$overtime);                       
         
            $epf= (float)($row['epf'] ?? 0);
            $vpf= (float)($row['vpf'] ?? 0);
            $total=$epf+$vpf;

            $total_deduction = (float)($row['total_deduction'] ?? 0);
            $lwf= (float)($row['lwf'] ?? 0);
            $epf= (float)($row['epf'] ?? 0);
            $vpf= (float)($row['vpf'] ?? 0);
            $other_deduction = $total_deduction-($lwf+$epf+$vpf);   


            ?>
        <tr>
            <td><?= $i++ ?></td>
            <td> <?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td>As per Act</td>
            <td>As per Act</td>
            <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['lop'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($othercashpayment ?? '') ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($total ?? '') ?></td>
            <td><?= htmlspecialchars($row['lwf'] ?? '') ?></td>
            <td><?= htmlspecialchars($other_deduction ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
            <td></td>
            <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="23" class="no-data" style="text-align: center;">No data available for Tamilnadu</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>