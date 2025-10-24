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
$pdo = require($configPath);

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentPrincipal = $_SESSION['current_principal'] ?? '';
$currentState = $_SESSION['current_state'] ?? 'Madhya Pradesh';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
        AND state LIKE :state
        AND location_code = :location_code
        AND month = :month 
        AND year = :year";
    
    // Add month/year filter if specified
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }
    
    // Prepare and execute query
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindValue(':client_name', $filters['client_name']);
    $stmt->bindValue(':principal_employer', $currentPrincipal);
    $stmt->bindValue(':state', "%$currentState%");
    $stmt->bindValue(':location_code', $currentLocation);
    
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $stmt->bindValue(':month', $filters['month']);
        $stmt->bindValue(':year', $filters['year']);
    }
    
    $stmt->execute();
    $stateData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $first_row = !empty($stateData) ? reset($stateData) : [];

    // Safe output variables
    $client_name = safe($filters['client_name'] ?? '');
    $branch_address = $first_row['address'] ?? '';
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
    $principal_employer = safe($currentPrincipal);
    $principal_employer_address = $first_row['principal_employer_address'] ?? '';
    $location_code = $first_row['location_code'] ?? '';
    $nature_of_business = $first_row['nature_of_business'] ?? '';
    $employee_name = $first_row['employee_name'] ?? '';
    $father_name = $first_row['father_name'] ?? '';

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            font-size: smaller;
            margin-bottom: 20px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 400px;
            vertical-align: top;
        }
        .info-content {
            display: inline-block;
            width: calc(100% - 410px);
        }
        .month-year {
            text-align: right;
            font-weight: bold;
            margin-bottom: 15px;
        }
       table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Force equal column distribution */
            font-size: 10px;
            /* Reduce font size for fitting */
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            word-wrap: break-word;
            /* Wrap long text */
        }
        th {
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
    <th class="form-title" colspan="16">
        FORM XVII<br>
        [See Rule 78(2)(a)]<br>
        Register of Wages
    </th>
  </tr>
  <tr>
    <th colspan="8" style="text-align: left;">Name and address of the Contractor</th>
    <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
  </tr>
  <tr>
    <th colspan="8" style="text-align: left;">Name and Address of Establishment in/ under which Contract is carried on : </th>
    <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
  </tr>
  <tr>
    <th colspan="8" style="text-align: left;">Nature and Location of Work : </th>
    <td colspan="8" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
  </tr>
  <tr>
    <th colspan="8" style="text-align: left;">Name and Address of Principal Employer</th>
    <td colspan="8" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
  </tr>
  <tr>
    <th colspan="8" style="text-align: left;">Month Year:</th>
    <td colspan="8" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
  </tr>
  <tr>
    <th rowspan="2">SI.No.</th>
    <th rowspan="2">Name of Workman</th>
    <th rowspan="2">Sl. No. in the register of workmen </th>
    <th rowspan="2">Designation/ Nature of employment</th>
    <th rowspan="2">No. of days worked</th>
    <th rowspan="2">Units of work done</th>
    <th rowspan="2">Daily Rate of wages/piece rate </th>
    <th colspan="5" style="text-align: center;">Amount of wages earned</th>
    <th rowspan="2">Deductions, if any (indicate nature)</th>
    <th rowspan="2">Net Amount Paid</th>
    <th rowspan="2">Signature/ thumb impression of workmen</th>
    <th rowspan="2">Initial of contractor or his representative</th>
  </tr>
  <tr>
    <th>Basic wages</th>
    <th>Dearness Allowances</th>
    <th>Overtime</th>
    <th>Other cash payments (Nature of payment to<br>be indicated)</th>
    <th><br>Total</th>
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
  </tr>
</thead>
<tbody>
    <?php if (!empty($stateData)): ?>
        <?php $i = 1; foreach ($stateData as $row): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
    <td>Nil</td>
    <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['over_time_wages'] ?? '') ?></td>
    <td>Nil</td>
    <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['total_deductions'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
    <td></td>
    <td></td>
  </tr>
  <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="16" style="text-align:center;">No data available for Madhya Pradesh</td>
            </tr>
        <?php endif; ?>
  <tr>
    <th colspan="16" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
  </tr>
</tbody>
</table>
</body>
</html>