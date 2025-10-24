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
$currentState = $_SESSION['current_state'] ?? 'Goa';
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }
        th {
            background-color: #ffffff;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left top 0;
            white-space: nowrap;
            width: 20px;
            height: 100px;
            position: relative;
            top: 80px;
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
    <th class="form-title" colspan="11">
        FORM X<br>
        [See Rule 17(4)]<br>
        Register of deductions for damage or loss caused to the employer by the neglect or default of employees
    </th>
  </tr>
  <tr>
    <th colspan="5">Name of the establishment and address:</th>
    <td colspan="6"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
  </tr>
  <tr>
    <th colspan="5">Registration number:</th>
    <td colspan="6">-</td>
  </tr>
  <tr>
    <th colspan="5">Month/Year :</th>
    <td colspan="6"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
  </tr>
  <tr>
    <th>Sl.No</th>
    <th>Employee Code</th>
    <th>Name of Employee</th>
    <th>Father's or husband's name </th>
    <th>Damage or loss caused</th>
    <th>Whether worker showed cause against deduction or not and if so, date on which cause was shown</th>
    <th>Amount of deduction imposed</th>
    <th>Date on which deduction imposed </th>
    <th>Number of instalments, if any</th>
    <th>Date on which total amount realised</th>
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
  </tr>
</thead>
<tbody>
        <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
  <tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['particulars_of_damage_loss'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['whether_worker_showed_cause_against_deduction'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['amount_of_deduction_imposed'] ?? '') ?></td>
    <td>Nil</td>
    <td><?= htmlspecialchars($row['no_of_installment'] ?? '') ?></td>
    <td><?= htmlspecialchars($row['last_installment_date'] ?? '') ?></td>
    <td></td>
  </tr>
 
  <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="19" style="text-align:center;">No data available for Goa</td>
            </tr>
        <?php endif; ?>
         <tr>
    <th colspan="11" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
  </tr>
</tbody>
</table>
</body>
</html>