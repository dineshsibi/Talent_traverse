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
$currentState = $_SESSION['current_state'] ?? 'Maharashtra';
$currentLocation = $_SESSION['current_location'] ?? '';

try {

  // fetch the matching holiday descriptions from n_f_holiday (comma-separated if multiple).
  $sql = "SELECT c.*,
                   CASE WHEN COALESCE(c.nh_fh_week_off_wages,0) > 0 THEN (
                       SELECT TRIM(BOTH ', ' FROM GROUP_CONCAT(DISTINCT TRIM(h.description) SEPARATOR ', '))
                       FROM n_f_holiday h
                       WHERE h.client_name = c.client_name
                         AND h.principal_employer_name = c.principal_employer_name
                         AND h.state = c.state
                         AND h.location_code = c.location_code
                         AND h.month = c.month
                         AND h.year = c.year
                         AND h.description IS NOT NULL
                         AND TRIM(h.description) <> ''
                   ) ELSE NULL END AS description
            FROM combined_data c
            WHERE c.client_name = :client_name
              AND c.principal_employer_name = :principal_employer
              AND c.state LIKE :state
              AND c.location_code = :location_code";



  // Add month/year filter if specified by user
  if (!empty($filters['month']) && !empty($filters['year'])) {
    $sql .= " AND c.month = :month AND c.year = :year";
  }

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
  $month = safe($filters['month'] ?? '');
  $year = safe($filters['year'] ?? '');
  $principal_employer = safe($currentPrincipal);
  $location_code = $first_row['location_code'] ?? '';

  $address = safe($first_row['address'] ?? '');
  $nature = safe($first_row['nature_of_business'] ?? '');
  $principal_employer_address = safe($first_row['principal_employer_address'] ?? '');
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

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 8px;
      text-align: left;
      vertical-align: top;
    }

    th {
      font-weight: bold;
    }

    .form-header {
      text-align: left;
    }
  </style>
</head>

<body>
  <table>
    <thead>
      <tr>
        <th style="text-align: center;" colspan="67">FORM II<br>sub-rule (i) of rule 27<br>Muster-roll-cum-wages Register</th>
      </tr>
      <tr>
        <th class="form-header" colspan="41" style="text-align: left;">Name &amp; Address of the Factory/ Establishment</th>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th class="form-header" colspan="41" style="text-align: left;">Name &amp; Address of the Contractor ( if any) </th>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th class="form-header" colspan="41" style="text-align: left;">Name &amp; Address of the Principal employer </th>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
      </tr>
      <tr>
        <th class="form-header" colspan="41" style="text-align: left;">Place of Work</th>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($nature . ' , ' . $location_code) ?></td>
      </tr>
      <tr>
        <th class="form-header" colspan="41" style="text-align: left;">Month &amp; Year</th>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th rowspan="2">Sl No</th>
        <th rowspan="2">Employee Code</th>
        <th rowspan="2">Name of Employee</th>
        <th rowspan="2">Father/Husband Name</th>
        <th rowspan="2">Sex M/F</th>
        <th rowspan="2">Date of Birth</th>
        <th rowspan="2">Degn/Dept</th>
        <th rowspan="2">Date of<br>joining</th>
        <th rowspan="2">ESIC No</th>
        <th rowspan="2">PF No</th>
        <th colspan="31" style="text-align: center;">ATTENDANCE<br>Units of work done(if piece rated)</th>
        <th rowspan="2">No. of payable dayss</th>
        <th rowspan="2">Name of N&amp;F Holiday for which wages have been paid</th>
        <th colspan="10" style="text-align: center;">Earned wages and other allowances</th>
        <th rowspan="2">Total</th>
        <th colspan="11" style="text-align: center;">Deductions</th>
        <th rowspan="2">Net Payable</th>
        <th rowspan="2">Date of payment</th>
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
        <th>23</th>
        <th>24</th>
        <th>25</th>
        <th>26</th>
        <th>27</th>
        <th>28</th>
        <th>29</th>
        <th>30</th>
        <th>31</th>
        <th>Basic</th>
        <th>DA/VDA</th>
        <th>HRA</th>
        <th>Conv.<br>Allow.</th>
        <th>Medical Allowance</th>
        <th>Attendance Bonus</th>
        <th>Special allowance</th>
        <th>OT</th>
        <th>Msic. Earnings</th>
        <th>Others</th>
        <th>ESI</th>
        <th>PF</th>
        <th>PT</th>
        <th>thS</th>
        <th>Society</th>
        <th>Insurance</th>
        <th>Salary Advance</th>
        <th>Fine</th>
        <th>Damages</th>
        <th>Others</th>
        <th>Total </th>
      </tr>
      <?php if (!empty($stateData)): ?>
        <?php $i = 1;
        foreach ($stateData as $row): ?>
          <?php
          $earned_Gross = (float) ($row['earned_gross'] ?? 0);
          $basic = (float) ($row['basic'] ?? 0);
          $da = (float) ($row['da'] ?? 0);
          $hra = (float) ($row['hra'] ?? 0);
          $special_allowance = (float) ($row['special_allowance'] ?? 0);
          $conveyance = (float) ($row['conveyance_allowance'] ?? 0);
          $overtime_wages = (float) ($row['over_time_wages'] ?? 0);
          $medical = (float) ($row['medical_allowance'] ?? 0);
          $others = $earned_Gross - ($basic + $da + $hra + $special_allowance + $conveyance + $overtime_wages + $medical);

          $pf = (float) ($row['pf'] ?? 0);
          $vpf = (float) ($row['vpf'] ?? 0);
          $pff = $pf + $vpf;

          $total_deductions = (float) ($row['earned_gross'] ?? 0);
          $esi = (float) ($row['esi'] ?? 0);
          $ptax = (float) ($row['p_tax'] ?? 0);
          $tds = (float) ($row['tds'] ?? 0);
          $advance_loan = (float) ($row['advance_loan'] ?? 0);
          $deduction = (float) ($row['deduction_for_damages_loss'] ?? 0);
          $fine = (float) ($row['fine'] ?? 0);
          $medical = (float) ($row['medical_insurance'] ?? 0);
          $others1 = $total_deductions - ($pf + $vpf + $esi + $ptax + $tds + $advance_loan + $deduction + $fine + $medical);
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['ip'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['uan_no'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_1'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_2'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_3'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_4'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_5'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_6'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_7'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_8'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_9'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_10'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_11'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_12'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_13'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_14'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_15'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_16'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_17'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_18'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_19'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_20'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_21'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_22'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_23'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_24'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_25'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_26'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_27'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_28'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_29'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_30'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_31'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
            <td>
              <?php
              $nh_amount = (float) ($row['nh_fh_week_off_wages'] ?? 0);
              // alias from SQL: nf_description
              $holiday_desc = (string) ($row['description'] ?? '');
              // remove any accidental leading commas, hyphens or whitespace (extra safety)
              $holiday_desc = preg_replace('/^[,;\-\s]+/u', '', $holiday_desc);
              $holiday_desc = trim($holiday_desc);
              if ($nh_amount > 0 && $holiday_desc !== '') {
                echo htmlspecialchars($holiday_desc);
              } else {
                echo '-';
              }
              ?>
            </td>
            <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['conveyance_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['medical_allowance'] ?? '') ?></td>
            <td>Nil</td>
            <td><?= htmlspecialchars($row['special_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['over_time_wages'] ?? '') ?></td>
            <td>NiL</td>
            <td><?= number_format($others) ?></td>
            <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
            <td><?= number_format($pff) ?></td>
            <td><?= htmlspecialchars($row['p_tax'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['tds'] ?? '') ?></td>
            <td>Nil</td>
            <td><?= htmlspecialchars($row['medical_insurance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['advance_loan'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['fine'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['deduction_for_damages_loss'] ?? '') ?></td>
            <td><?= number_format($others1) ?></td>
            <td><?= htmlspecialchars($row['total_deductions'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="14" style="text-align:center;">No data available for Maharashtra</td>
        </tr>
      <?php endif; ?>
      <tr>
        <th colspan="67" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
    </tbody>
  </table>

</body>

</html>