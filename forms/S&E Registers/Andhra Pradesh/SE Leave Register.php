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
$pdo = require($configPath); // This gets the PDO connection from config.php

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
  die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Andhra Pradesh'; // Hardcoded for this state template

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

  $location = $first_row['location_name'] ?? '';
  $branch_address = $first_row['branch_address'] ?? '';
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

    .header {
      text-align: center;
    }
  </style>
</head>

<body>
 <?php
$totalEmployees = count($stateData);
$currentEmployee = 0;

foreach ($stateData as $row):
  $currentEmployee++;

  // Collect dates for each leave type
  $pl_dates = [];
  $cl_dates = [];
  $sl_dates = [];

  // Loop through day_1 to day_31
  for ($i = 1; $i <= 31; $i++) {
    $dayCol = 'day_' . $i;
    $dayValue = strtoupper(trim($row[$dayCol] ?? ''));

    if ($dayValue === 'PL') {
      $pl_dates[] = $i;
    } elseif ($dayValue === 'CL') {
      $cl_dates[] = $i;
    } elseif ($dayValue === 'SL') {
      $sl_dates[] = $i;
    }
  }

  $pl_str = !empty($pl_dates) ? implode(', ', $pl_dates) : '-';
  $cl_str = !empty($cl_dates) ? implode(', ', $cl_dates) : '-';
  $sl_str = !empty($sl_dates) ? implode(', ', $sl_dates) : '-';
?>
  <!-- Each employee block -->
  <div class="employee-page">
    <table>
      <thead>
        <tr>
          <th class="header" colspan="14">FORM - XXV<br>The Andhra Pradesh Shops and Establishments Act, 1988 and Rules, 1990, [See Rule 29(6)]<br>Register of Leave</th>
        </tr>
        <tr>
          <th style="text-align: left;" colspan="7">Name Address of the Establishment/Shop </th>
          <td style="text-align: left;" colspan="7"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
          <th style="text-align: left;" colspan="7">Employee Code</th>
          <td style="text-align: left;" colspan="7"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
        </tr>
        <tr>
          <th style="text-align: left;" colspan="7">Name of the employee :</th>
          <td style="text-align: left;" colspan="7"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
        </tr>
        <tr>
          <th style="text-align: left;" colspan="7">Father’s/Husband’s Name :</th>
          <td style="text-align: left;" colspan="7"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
        </tr>
        <tr>
          <th style="text-align: left;" colspan="7">Registration No.</th>
          <td style="text-align: left;" colspan="7">-</td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th colspan="7" style="text-align: left;">Date of appointment :</th>
          <td colspan="7" style="text-align: left;"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
        </tr>
        <tr>
          <th colspan="14" class="header">LEAVE WITH WAGES</th>
        </tr>
        <tr>
          <th rowspan="2">Leave Type</th>
          <th rowspan="2">Date of application</th>
          <th colspan="2">Applied</th>
          <th rowspan="2">No. of days to which the employee is entitled</th>
          <th colspan="2">Leave granted</th>
          <th rowspan="2">No. of days</th>
          <th rowspan="2">Balance</th>
          <th colspan="2">If refused, in part or full To</th>
          <th rowspan="2">No. of days</th>
          <th rowspan="2">Reasons</th>
          <th rowspan="2">Signature of Employee Employer</th>
        </tr>
        <tr>
          <th>From</th>
          <th>To</th>
          <th>From</th>
          <th>To</th>
          <th>From </th>
          <th>To</th>
        </tr>
        <tr>
          <th>1</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th><th>13</th>
        </tr>
        <tr>
          <td>PL</td>
          <td><?= htmlspecialchars($pl_str) ?></td>
          <td colspan="2"><?= htmlspecialchars($pl_str) ?></td>
          <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
          <td colspan="2"><?= htmlspecialchars($pl_str) ?></td>
          <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
          <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
          <td colspan="2">Nil</td><td>Nil</td><td>Nil</td><td></td>
        </tr>
        <tr>
          <td>CL</td>
          <td><?= htmlspecialchars($cl_str) ?></td>
          <td colspan="2"><?= htmlspecialchars($cl_str) ?></td>
          <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
          <td colspan="2"><?= htmlspecialchars($cl_str) ?></td>
          <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
          <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
          <td colspan="2">Nil</td><td>Nil</td><td>Nil</td><td></td>
        </tr>
        <tr>
          <td>SL</td>
          <td><?= htmlspecialchars($sl_str) ?></td>
          <td colspan="2"><?= htmlspecialchars($sl_str) ?></td>
          <td><?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
          <td colspan="2"><?= htmlspecialchars($sl_str) ?></td>
          <td><?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
          <td><?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
          <td colspan="2">Nil</td><td>Nil</td><td>Nil</td><td></td>
        </tr>
        <tr>
          <th colspan="14" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
        </tr>
      </tbody>
    </table>
  </div>

  <?php if ($currentEmployee < $totalEmployees): ?>
    <div style="page-break-before: always;"></div>
  <?php endif; ?>

<?php endforeach; ?>

</body>

</html>