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
$currentState = $_SESSION['current_state'] ?? 'Odisha';
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
      text-align: center;
    }
  </style>
</head>

<body>
  <table>
    <thead>
      <tr>
        <th class="form-header" colspan="20" style="text-align: center;">Form 8<br>[See Rule 15 (2)]<br>Service and leave account</th>
      </tr>
      <tr>
        <th colspan="8" style="text-align: left;">Name and Address of the  Establishment / Shop :</th>
        <td style="text-align: left;" colspan="12"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th colspan="8" style="text-align: left;">Name and address of the Employer</th>
        <td style="text-align: left;" colspan="12"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
      </tr>
      <tr>
        <th colspan="8" style="text-align: left;">Month &amp; Year</th>
        <td style="text-align: left;" colspan="12"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
      <tr>
        <th colspan="8" style="text-align: left;">Registration No.</th>
        <td style="text-align: left;" colspan="12">-</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th rowspan="2">Sl No</th>
        <th rowspan="2">Employee Code</th>
        <th rowspan="2">Emp Name</th>
        <th rowspan="2">Adult, male or female, or child</th>
        <th rowspan="2">Name of employment, if any </th>
        <th rowspan="2">Mothly or weeky rate of pay or wage</th>
        <th colspan="2">Leave earned</th>
        <th rowspan="2">Period of leave refused to be cairried over</th>
        <th colspan="3">Leave availed</th>
        <th rowspan="2">Balance</th>
        <th colspan="3">Sickness leave</th>
        <th rowspan="2">Balance</th>
        <th colspan="2">Signature </th>
        <th rowspan="2">Remarks</th>
      </tr>
      <tr>
        <th>At credit</th>
        <th>Earned</th>
        <th>From</th>
        <th>To</th>
        <th>No. of days</th>
        <th>From</th>
        <th>To </th>
        <th>No. of days</th>
        <th>Signature of thumb impression of the employee</th>
        <th>Employer</th>
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
      </tr>
      <?php if (!empty($stateData)): ?>
        <?php $i = 1;
        foreach ($stateData as $row): ?>
          <?php
          // Process leave data to find PL, SL days
          $plDays = [];
          $slDays = [];

          // Check all day fields (day_1 to day_31) for leave types
          for ($day = 1; $day <= 31; $day++) {
            $dayKey = 'day_' . $day;
            if (isset($row[$dayKey])) {
              if ($row[$dayKey] == 'PL') {
                $plDays[] = $day;   // store day number
              } elseif ($row[$dayKey] == 'SL') {
                $slDays[] = $day;   // store day number
              }
            }
          }

          // Convert arrays to comma-separated string
          $plList = !empty($plDays) ? implode(',', $plDays) : 'None';
          $slList = !empty($slDays) ? implode(',', $slDays) : 'None';

          ?>

          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td>Adult</td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td>Monthly</td>
            <td colspan="2"><?= htmlspecialchars($row['pl_credit'] ?? '') ?></td>
            <td>NIL</td>
            <td colspan="2"><?= $plList ?></td>
            <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
            <td colspan="2"><?= $slList ?></td>
            <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="25" class="no-data">No contractor data available for Odisha</td>
        </tr>
      <?php endif; ?>
      <tr>
        <th colspan="20" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
    </tbody>
  </table>

</body>

</html>