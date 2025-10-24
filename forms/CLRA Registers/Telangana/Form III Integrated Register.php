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
$currentState = $_SESSION['current_state'] ?? 'Telangana';
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
      margin-bottom: 20px;
    }

    .info-section {
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
      text-align: Left;
      word-wrap: break-word;
      /* Wrap long text */
    }

    th {
      background-color: #ffffff;
      font-weight: bold;
    }

    .page-break {
      page-break-after: always;
    }

    .signatory {
      text-align: right;
      margin-top: 30px;
    }

    .info-content {
      display: inline-block;
      width: calc(100% - 410px);
    }

    * {
      font-family: 'Times New Roman', Times, serif;
    }
  </style>
</head>

<body>
  <table>
    <thead>
      <tr>
        <th class="form-title" colspan="26">
          Form-III<br>
          INTEGRATED REGISTER<br>
          MUSTER ROLL-CUM-REGISTER OF WAGES / DEDUCTIONS / OVERTIME / ADVANCES
        </th>
      </tr>
      <tr>
        <th colspan="13">For the month of</th>
        <td colspan="13"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
      <tr>
        <th colspan="13">Name of the Establishment and address</th>
        <td colspan="13"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th colspan="13">Location of work</th>
        <td colspan="13"><?= htmlspecialchars($first_row['location_code'] ?? '') ?></td>
      </tr>
      <tr>
        <th colspan="13">Name and address of Employer / Manager </th>
        <td colspan="13"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
      </tr>
      <tr>
        <th colspan="13">Nature of Establishment / Production / Business etc.</th>
        <td colspan="13"><?= htmlspecialchars($first_row['nature_of_business'] ?? '') ?></td>
      </tr>
      <tr>
        <th>Sl. No</th>
        <th>Name of the worker (ID<br>/Token No. if any)</th>
        <th>Age/ Date of Birth</th>
        <th>Address</th>
        <th>Education / Skill</th>
        <th>Sex (M / F)</th>
        <th>Father’s/ husband’s Name</th>
        <th>Name &amp; address of nominee</th>
        <th>Designation / category / nature of work performed</th>
        <th>Total No. of days worked</th>
        <th>Category of Leave</th>
        <th>Leaves availed (No. of days)</th>
        <th>Total Balance Leaves</th>
        <th>Wage rate / pay or<br>(piece rate / wages per unit)</th>
        <th>Other allowances</th>
        <th>Overtime worked (Number<br>of hours in the month)</th>
        <th>Amount of over time<br>wages</th>
        <th>Amount of Maternity benefit (if any)</th>
        <th>Any other amount<br>(Please mention)</th>
        <th>Total/ gross wages/ earnings</th>
        <th>Amount of advances/loans if any and purpose of advance</th>
        <th>Deductions of fines imposed If any</th>
        <th>Deductions like EPF/ESI/Welfare Fund etc. (if any)</th>
        <th>Net amount payable</th>
        <th>Signature/thumb impression</th>
        <th>Remarks, if any</th>
      </tr>
      <tr>
        <th>(1)</th>
        <th>(2)</th>
        <th>(3)</th>
        <th>(4)</th>
        <th>(5)</th>
        <th>(6)</th>
        <th>(7)</th>
        <th>(8)</th>
        <th>(9)</th>
        <th>(10)</th>
        <th>(11)</th>
        <th>(12)</th>
        <th>(13)</th>
        <th>(14)</th>
        <th>(15)</th>
        <th>(16)</th>
        <th>(17)</th>
        <th>(18)</th>
        <th>(19)</th>
        <th>(20)</th>
        <th>(21)</th>
        <th>(22)</th>
        <th>(23)</th>
        <th>(24)</th>
        <th>(25)</th>
        <th>(26)</th>
      </tr>
    </thead>
    <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
            <?php
                $gross = (float)($row['gross_wages'] ?? 0);
                $over_time = (float)($row['over_time_allowance'] ?? 0);
                $advance = (float)($row['advance_loan'] ?? 0);
                $fine = (float)($row['fine'] ?? 0);
                $total = (float)($row['total_deductions'] ?? 0);
                $other_allowance = $gross - $over_time;
                $deductions = $total - $advance + $fine;
              ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?> , <?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
        <td>-</td>
        <td><?= htmlspecialchars($row['education_level'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
        <td>-</td>
        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
        <td>CL <br> PL <br> SL</td>
        <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?> <br> <?= htmlspecialchars($row['pl_availed'] ?? '') ?> <br> <?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?> <br> <?= htmlspecialchars($row['pl_closing'] ?? '') ?> <br> <?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
        <td>Nil</td>
        <td><?= htmlspecialchars($other_allowance ?? '') ?></td>
        <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['over_time_wages'] ?? '') ?></td>
        <td>-</td>
        <td>-</td>
        <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['advance_loan'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['fine'] ?? '') ?></td>
        <td><?= htmlspecialchars($deductions ?? '') ?></td>
        <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
        <td></td>
        <td></td>
      </tr>
      <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No data available for Telangana</td>
                </tr>
            <?php endif; ?>
      <tr>
        <th colspan="13">Signature of the employer / contractor</th>
        <td colspan="13"></td>
      </tr>
      <tr>
        <th colspan="13">Name of signatory</th>
        <td colspan="13"></td>
      </tr>
      <tr>
        <th colspan="13">Certificate by the Principal Employer if the employer is contractor</th>
        <td colspan="13"></td>
      </tr>
      <tr>
        <th colspan="13">This is to certify that the contractor has paid wages to workmen employed by him as shown in this register in his / in the presence of his authorized representatives.</th>
        <td colspan="13"></td>
      </tr>
      <tr>
        <th colspan="13">Signature of Representative of Principal employer</th>
        <td colspan="13"></td>
      </tr>
      <tr>
        <th colspan="13">Designation in the Establishment</th>
        <td colspan="13"></td>
      </tr>
      <tr>
        <th colspan="26" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
    </tbody>
  </table>
</body>
</html>