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
$currentState = $_SESSION['current_state'] ?? 'Kerala';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
    // Base SQL
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
        AND state LIKE :state
        AND location_code = :location_code";

    // Month/year condition
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }

    $stmt = $pdo->prepare($sql);

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
      background-color: white;
      margin: 0;
      padding: 20px;
      color: #000;
      line-height: 1.4;
    }

    .form-container {
      max-width: 1000px;
      margin: 0 auto;
      border: 1px solid white;
      padding: 20px;
      background-color: white;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }


    .form-title {
      text-align: center;
      font-weight: bold;
      margin-bottom: 5px;
      font-size: 20px;
      padding: 5px;
    }

    .form-subtitle {
      text-align: center;
      margin-bottom: 20px;
      font-style: italic;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      /* keeps columns evenly spread */
      font-size: 12px;
      /* adjust font size so it doesn’t look too tiny */
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
      text-align: left;
      background-color: white;
    }

    .header-row {
      background-color: white;
      font-weight: bold;
      text-align: center;
    }

    @media print {
      body {
        padding: 0;
      }

      .form-container {
        border: none;
        box-shadow: none;
        page-break-inside: avoid;
      }
    }
  </style>
</head>

<body>
  <table>
    <thead>
      <tr>
        <th class="form-title" colspan="25">
          FORM XXVII<br>
          [See rule 78(1) (a)]<br>
          Register of Wages
        </th>
      </tr>
      <tr>
        <td colspan="6" style="text-align: left;">Name and address of the Establishment:</td>
        <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
        <td colspan="6" style="text-align: left;">Wage Period Month:</td>
        <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
      <tr>
        <td colspan="6" style="text-align: left;">Name of the Employer/ Contractor with address</td>
        <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
        <td colspan="6" style="text-align: left;">Week/Fortnight/Month</td>
        <td colspan="6" style="text-align: left;">Monthly</td>
      </tr>
      <tr>
        <td rowspan="2">Sl. No.</td>
        <td rowspan="2">Name of the Workman</td>
        <td rowspan="2">Sl. No. in the register of workmen </td>
        <td rowspan="2">Sex</td>
        <td rowspan="2">Designation/ Nature of Work</td>
        <td rowspan="2">Daily rated/ piece-rated/ Monthly rated</td>
        <td rowspan="2">Wages Period-Week/FN/Month</td>
        <td rowspan="2">Total No.of days worked during the week/FN/Month.</td>
        <td rowspan="2">Units of work done/ Number of days worked</td>
        <td rowspan="2">Daily rate of wages/ Piece rate</td>
        <td rowspan="2">Overtime Rate</td>
        <td colspan="5">Wages Earned</td>
        <td rowspan="2">Gross Wages</td>
        <td colspan="5">Deductions</td>
        <td rowspan="2">Net wages</td>
        <td rowspan="2">Signature with Date or Thumb Impression/ Cheque No. and Date in case of Payment through Bank/ Advice of the Bank to be appended</td>
        <td rowspan="2">Total unpaid amounts accumulated</td>
      </tr>
      <tr>
        <td>Basic Wages</td>
        <td>Dearness Allowance</td>
        <td>Other Allowances/ Cash Payment Nature to be specified</td>
        <td>Overtime earned</td>
        <td>Leave wages including cash in lieu of kinds</td>
        <td>Provident Fund</td>
        <td>E.S.I.</td>
        <td>Other deduction (indicate Nature)</td>
        <td>Fines (if any)</td>
        <td>Total Deductions</td>
      </tr>
      <tr>
        <td>1</td>
        <td>2</td>
        <td>3</td>
        <td>3</td>
        <td>4</td>
        <td>5</td>
        <td>6</td>
        <td>7</td>
        <td>8</td>
        <td>9</td>
        <td>10</td>
        <td>11</td>
        <td>12</td>
        <td>13</td>
        <td>14</td>
        <td>15</td>
        <td>16</td>
        <td>17</td>
        <td>18</td>
        <td>19</td>
        <td>20</td>
        <td>21</td>
        <td>22</td>
        <td>23</td>
        <td>24</td>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): 
        ?>
      <?php
          $pf = (float)($row['pf'] ?? 0);
          $vpf = (float)($row['vpf'] ?? 0);
          $esi = (float)($row['esi'] ?? 0);
          $fine = (float)($row['fine'] ?? 0);
          $deduction = (float)($row['total_deductions'] ?? 0);
          $rate_of_wage = (float)($row['rate_of_wage'] ?? 0);

          $overtime_rate =  $rate_of_wage/30/ 8*2;
          $provident_fund = $pf + $vpf;
          $other_deductions = $deduction - $pf + $vpf + $esi + $fine;
        ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
        <td>Nil</td>
        <td><?= htmlspecialchars($row['month'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
        <td>Nil</td>
        <td><?= htmlspecialchars($overtime_rate ?? '') ?></td>
        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
        <td>Nil</td>
        <td><?= htmlspecialchars($row['over_time_wages'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['leave_travel_allowance'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
        <td><?= htmlspecialchars($provident_fund ?? '') ?></td>
        <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
        <td><?= htmlspecialchars($other_deductions ?? '') ?></td>
        <td><?= htmlspecialchars($row['fine'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['total_deductions'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
        <td></td>
        <td>Nil</td>
      </tr>
      <tr>
        <th colspan="25" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
      <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="25" style="text-align:center;">No data available for Kerala</td>
                </tr>
            <?php endif; ?>
    </tbody>
  </table>
</body>
</html>