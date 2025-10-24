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
$currentState = 'Goa'; // Hardcoded for this state template

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
    $employer_name = $first_row['employer_name'] ?? '';
    $employer_address = $first_row['employer_address'] ?? '';

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
            margin: 0;
            padding: 20px;
        }
        .form-container {
            width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .header-info {
            text-align: left;
            padding-left: 10px;
        }
        .sub-header {
            font-weight: normal;
            background-color: #ffffff;
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
                <th colspan="19" class="title">
                    Form XXIII<br>
                    The Goa, Daman and Diu Shops and Establishment Act, 1973 and Rules, 1975, [See Rule 31(2)]<br>
                    Register of Wages
                </th>
            </tr>
            <tr>
                <th colspan="9" class="header-info" style="text-align: left;">Name and Address of the Establishment </th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($client_name .' , ' . $branch_address)?></td>
            </tr>
            <tr>
                <th colspan="9" class="header-info" style="text-align: left;">Name of Employer and address</th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($employer_name .' , ' . $employer_address)?></td>
            </tr>
            <tr>
                <th colspan="9" class="header-info" style="text-align: left;">Registration No</th>
                <td colspan="10" style="text-align: left;">-</td>
            </tr>
            <tr>
                <th colspan="9" class="header-info" style="text-align: left;">Wage Period</th>
                <td colspan="10" style="text-align: left;">Monthly</td>
            </tr>
            <tr>
                <th colspan="9" class="header-info" style="text-align: left;">Month/Year</th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($month .' - ' . $year)?></td>
            </tr>
            <tr>
                <th rowspan="2">S No</th>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Name of the Employee</th>
                <th rowspan="2">Father's / Husband's name</th>
                <th rowspan="2">Designation</th>
                <th colspan="2">Minimum rate of wages payable</th>
                <th colspan="5">Wages Payable</th>
                <th colspan="4">Deductions</th>
                <th rowspan="2">Net amount of wages paid</th>
                <th rowspan="2">Signature or thumb impression of the employee</th>
                <th rowspan="2">Date of Payment</th>
            </tr>
            <tr>
                <th>Basic</th>
                <th>Dearness Allowance</th>
                <th>Basic Salary or Wage</th>
                <th>Dearness Allowance</th>
                <th>Other Allowances</th>
                <th>Overtime Wages</th>
                <th>Gross Wages payable</th>
                <th>Advances</th>
                <th>Provident Fund Contributions</th>
                <th>Other authorised deductions</th>
                <th>Total deductions</th>
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
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php    
                 $Gross = (float)($row['gross_wages'] ?? 0);
                 $Basic=  (float)($row['basic'] ?? 0);
                 $Da= (float)($row['da'] ?? 0);
                 $Overtime=(float)($row['over_time_allowance'] ?? 0);
                 $calculation1=$Gross-($Basic+$Da+$Overtime);

                 $EPF = (float)($row['epf'] ?? 0);
                 $VPF = (float)($row['vpf'] ?? 0);
                 $calculation2= $EPF + $VPF;

                 $Total=(float)($row['total_deduction'] ?? 0);
                 
                 $calculation3=$Total-($EPF+$VPF);
                  ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                <td>As Per Act</td>
                <td>As Per Act</td>
                <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                <td><?= htmlspecialchars($calculation1 ?? '') ?></td>
                <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                <td>NIL</td>
                <td><?= htmlspecialchars($calculation2 ?? '') ?></td>
                <td><?= htmlspecialchars($calculation3 ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                <td></td>
                <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                
            </tr>
            
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No employee data found for Goa</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</body>
</html>