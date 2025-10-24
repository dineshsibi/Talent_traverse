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
$currentState = 'Assam'; // Hardcoded for this state template

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
        .colspan-18 {
            width: 100%;
        }
        .sub-header {
            font-weight: bold;
            text-align: center;
        }
       
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <td class="form-header" colspan="16">
                    Form M <br>
                    The Assam Shops and Establishment Act 1971, with Rules, 1976 [See  Rule  41] <br>
                    Register of Employment 														

                </td>
            </tr>
    
            <tr>
                <td colspan="8" style="text-align: left;"><b>Name  of the Establishment </td>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name)?></td>
            </tr>
            
            <tr>
                <td style="text-align: left;" colspan="8"><b>Registration No</td>
                <td colspan="8" style="text-align: left;">-</td>
            </tr>
            <tr>
                <td  style="text-align: left;" colspan="8"><b>Address</td>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($branch_address)?></td>
            </tr>
            <tr>
                <td style="text-align: left;" colspan="8"><b>Name of the employer</td>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($employer_name)?></td>
            </tr>
            <tr>
                <td style="text-align: left;" colspan="8"><b>For the month of</td>
                <td colspan="8" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
            </tr>
        
            <tr>
                <th>Serial No.</th>
                <th>Employee Code</th>
                <th>Name of the <br>Employee</th>
                <th>Father's name or<br> Husband's name<br> in case of married<br> woman employee</th>
                <th>Date of Birth</th>
                <th>Post held or nature <br>of job performed</th>
                <th>Date of Appointment</th>
                <th>Status Probationer/<br>Temporary/Casual/<br>Permanent</th>
                <th>Scale of Pay,<br> If any</th>
                <th>Rate of <br>increment</th>
                <th>Basic Pay</th>
                <th>Dearness Allowance</th>
                <th>Other Allowances</th>
                <th>Free Board <br>and/or Lodging</th>
                <th>Concessional supply <br>of food grains<br> and/or other <br>articles, if any</th>
                <th>Signature of the<br> Employer</th>
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
        </thead>
        <tbody>
             <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                $Gross = (float)($row['gross_wages'] ?? 0);
                $Basic = (float)($row['basic'] ?? 0);
                $Da = (float)($row['da'] ?? 0);

                $calculation=$Gross -($Basic +$Da);
                ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                <td>Permanent</td>
                <td>NIL</td>
                <td>NIL</td>
                <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                <td><?= htmlspecialchars($calculation ?? '') ?></td>
                <td>NIL</td>
                <td>NIL</td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="no-data">No data available for Assam</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>