<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__ . '/../../../includes/config.php';
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
$currentState = $_SESSION['current_state'] ?? 'Tamilnadu';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
  // Build the SQL query with parameters
  $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
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

    .form-tittle {
      text-align: center;
      font-weight: bold;
      margin-bottom: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th,
    td {
      border: 1px solid black;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #ffffff;
    }

    * {
      font-family: 'Times New Roman', Times, serif;
    }

    .employee-form {
      margin: 15px;
    }

    .page-break {
      page-break-after: always;
    }
    
    .no-page-break {
      page-break-after: auto;
    }
  </style>
</head>

<body>
  <?php
  $totalEmployees = count($stateData);
  $currentEmployee = 0;

  // Check if we have employee data
  if ($totalEmployees > 0) {
    foreach ($stateData as $row):
      $currentEmployee++;
      $isLast = ($currentEmployee === $totalEmployees);
  ?>
      <div class="employee-form <?= $isLast ? 'no-page-break' : 'page-break' ?>">
        <table>
          <thead>
            <tr>
              <th class="form-tittle" colspan="10">
                FORM No. XXVIII <br>
                [See rule 78(1) (b)] <br>
                Wage Slip
              </th>
            </tr>
            <tr>
              <th>1</th>
              <th colspan="4">Name and address of the Establishment :</th>
              <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
              <th>2</th>
              <th colspan="4">Name of Workman :</th>
              <td colspan="5" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
              <th>3</th>
              <th colspan="4">Father's or Husband's Name :</th>
              <td colspan="5" style="text-align: left;"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
              <th>4</th>
              <th colspan="4">Designation :</th>
              <td colspan="5" style="text-align: left;"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            </tr>
            <tr>
              <th>5</th>
              <th colspan="4">Date of entry into Service :</th>
              <td colspan="5" style="text-align: left;"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            </tr>
          </thead>
          <tbody>
            <?php
            $basic = (float)($row['basic'] ?? 0);
            $da = (float)($row['da'] ?? 0);
            $hra = (float)($row['hra'] ?? 0);
            $Overtime = (float)($row['over_time_wages'] ?? 0);
            $travel = (float)($row['leave_travel_allowance'] ?? 0);
            $Gross = (float)($row['earned_gross'] ?? 0);
            $pf = (float)($row['pf'] ?? 0);
            $vpf = (float)($row['vpf'] ?? 0);
            $esi = (float)($row['esi'] ?? 0);
            $deduction = (float)($row['total_deductions'] ?? 0);

            $other_allowance =  $Gross - ($basic + $da + $hra + $Overtime + $travel);
            $employees_fund = $pf + $vpf;
            $other_deductions = $deduction - ($pf + $vpf + $esi);
            ?>
            <tr>
              <th>6</th>
              <th colspan="4">Wage Period :</th>
              <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
              <th>7</th>
              <th colspan="4">Wage Earned :</th>
              <th colspan="5">Deductions :</th>
            </tr>
            <tr>
              <th>(a)</th>
              <th colspan="2">Basic :</th>
              <td colspan="2"><?= htmlspecialchars($row['basic'] ?? '0') ?></td>
              <th>(i)</th>
              <th colspan="2">Employees Provident Fund</th>
              <td colspan="2"><?= htmlspecialchars($employees_fund ?? '0') ?></td>
            </tr>
            <tr>
              <th>(b)</th>
              <th colspan="2">Dearness Allowance</th>
              <td colspan="2"><?= htmlspecialchars($row['da'] ?? '0') ?></td>
              <th>(ii)</th>
              <th colspan="2">Employees State Insurance</th>
              <td colspan="2"><?= htmlspecialchars($row['esi'] ?? '0') ?></td>
            </tr>
            <tr>
              <th>(c)</th>
              <th colspan="2">House Rent Allowance</th>
              <td colspan="2"><?= htmlspecialchars($row['hra'] ?? '0') ?></td>
              <th>(iii)</th>
              <th colspan="2">Other Deductions</th>
              <td colspan="2"><?= htmlspecialchars($other_deductions ?? '0') ?></td>
            </tr>
            <tr>
              <th>(d)</th>
              <th colspan="2">Overtime Wages</th>
              <td colspan="2"><?= htmlspecialchars($row['over_time_wages'] ?? '0') ?></td>
              <td colspan="5"></td>
            </tr>
            <tr>
              <th>(e)</th>
              <th colspan="2">Leave Wages</th>
              <td colspan="2"><?= htmlspecialchars($row['leave_travel_allowance'] ?? '0') ?></td>
              <td colspan="5"></td>
            </tr>
            <tr>
              <th>(f)</th>
              <th colspan="2">Other Allowances</th>
              <td colspan="2"><?= htmlspecialchars($other_allowance ?? '0') ?></td>
              <td colspan="5"></td>
            </tr>
            <tr>
              <th>(g)</th>
              <th colspan="2">Gross Wages</th>
              <td colspan="2"><?= htmlspecialchars($row['earned_gross'] ?? '0') ?></td>
              <th>(iv)</th>
              <th colspan="2">Net Amount Paid</th>
              <td colspan="2"><?= htmlspecialchars($row['net_salary'] ?? '0') ?></td>
            </tr>
            <tr>
              <th colspan="5">Signature of the Employer/ Manager/or any other Authorised Person.</th>
              <th colspan="5">Signature of Thumb impression of the Person Employed.</th>
            </tr>
          </tbody>
        </table>
      </div>
  <?php 
    endforeach; 
  } else {
    echo "<p>No employee data found for the selected criteria.</p>";
  }
  ?>
</body>
</html>