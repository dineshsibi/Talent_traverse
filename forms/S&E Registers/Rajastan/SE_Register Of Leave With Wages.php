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
$currentState = 'Rajastan'; // Hardcoded for this state template

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
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php
  $totalEmployees = count($stateData);
  $currentEmployee = 0;

  foreach ($stateData as $row):
    $currentEmployee++;

    // Initialize arrays for each employee (important!)
    $pl_dates = $cl_dates = $sl_dates = [];

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
  ?>

    <table>
      <thead>
        <tr>
          <th class="form-header" colspan="12">
            FORM 8<br>
            The Rajasthan Shops and Commercial Establishments Rules, 1959 See rules 13 &amp; 14<br>
            Register of Leave with Wages
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th colspan="3">Name of employee</th>
          <td colspan="3"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
          <th colspan="3">Occupation</th>
          <td colspan="3"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
        </tr>
        <tr>
          <th colspan="3">Employee Code</th>
          <td colspan="3"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
          <th colspan="3">Wage Period</th>
          <td colspan="3"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
          <th colspan="3">Father’s Name</th>
          <td colspan="3"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
          <th colspan="3">Date of Employment</th>
          <td colspan="3"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
        </tr>
        <tr>
          <th colspan="12" style="text-align: center;">Ordinary Leave</th>
        </tr>
        <tr>
          <th rowspan="2">Sl. No.</th>
          <th colspan="5">Interruptions</th>
          <th rowspan="2">Others</th>
          <th rowspan="2">Leave Due with effect from</th>
          <th rowspan="2">Date from which the workers are allowed leave</th>
          <th colspan="3">Discharged worker</th>
        </tr>
        <tr>
          <th>Adult/Child</th>
          <th>Sickness and Accident</th>
          <th>Authorised leave</th>
          <th>Lock-out or legal strike</th>
          <th>Involuntary unemployment</th>
          <th>Date of discharge</th>
          <th>Date and amount of payment made in lieu of leave due</th>
          <th>Balance Due</th>
        </tr>
        <tr>
          <td>1</td>
          <td>Adult</td>
          <td>Nil</td>
          <td>Nil</td>
          <td>Nil</td>
          <td>Nil</td>
          <td>NIL</td>
          <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>
          <td><?= htmlspecialchars($pl_str) ?></td>
          <td>Nil</td>
          <td>Nil</td>
          <td>Nil</td>
        </tr>
        <tr>
          <th colspan="12" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
        </tr>
      </tbody>
    </table>

    <?php if ($currentEmployee < $totalEmployees): ?>
      <div style="page-break-after: always;"></div>
    <?php endif; ?>

  <?php endforeach; ?>
</body>

</html>