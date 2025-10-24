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
        <th colspan="16" style="text-align: center;">FORM - 10 - OVERTIME REGISTER FOR WORKMEN<br>(Vide rule 78 of A.P.factories Rules.1950)<br></th>
      </tr>
      <tr>
        <th colspan="4">Name of the factory or establishment </th>
        <td style="text-align: left;" colspan="12"><?= htmlspecialchars($client_name) ?></td>
      </tr>
      <tr>
        <th colspan="4">Address of the factory or establishment </th>
        <td style="text-align: left;" colspan="12"><?= htmlspecialchars($branch_address) ?></td>
      </tr>
      <tr>
        <th colspan="4">Month Ending </th>
        <td style="text-align: left;" colspan="12"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
    </thead>
    <thead>
      <tr>
        <th>Sl. No</th>
        <th>Employee Code</th>
        <th>Name of Worker<br><br></th>
        <th>Father's or husband's Name</th>
        <th>Sex</th>
        <th>Designation &amp; Department</th>
        <th>Dates on which overtime worked</th>
        <th>Extent of overtime worked on each occation</th>
        <th>Total overtime worked or production in case of piece workers</th>
        <th>Normal Hours</th>
        <th>Normal Rate</th>
        <th>Overtime Rate</th>
        <th>Normal Earnings</th>
        <th>Overtime Earnings</th>
        <th>Total Earnings</th>
        <th>Dates on which over- time payments are made</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($stateData)): ?>
        <?php $i = 1;
        foreach ($stateData as $row): ?>
          <?php
          $earned_Gross = (float) ($row['earned_gross'] ?? 0);
          $overtime_wages = (float) ($row['over_time_wages'] ?? 0);
          $normal_Earnings = $earned_Gross - $overtime_wages;

          // Collect overtime dates
          $ot_dates = [];
          for ($d = 1; $d <= 31; $d++) {
            $col = 'ot_day_' . $d;
            if (!empty($row[$col]) && $row[$col] > 0) {
              $ot_dates[] = $d; // Add day number if value > 0
            }
          }
          $ot_dates_str = !empty($ot_dates) ? implode(", ", $ot_dates) : "Nil";
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($ot_dates_str) ?></td>
            <td><?= htmlspecialchars($row['overtime_earnings'] ?? '') ?></td>
            <td>Nil</td>
            <td>8 Hrs</td>
            <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['overtime_rate_of_wages'] ?? '') ?></td>
            <td><?= number_format($normal_Earnings) ?></td>
            <td><?= htmlspecialchars($row['over_time_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_on_ot_payment'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="14" style="text-align:center;">No data available for Maharashtra</td>
        </tr>
      <?php endif; ?>
      <tr>
        <th colspan="16" style="text-align: left;"> Signature of the Occupier/Principal Employer/Contractor</th>
      </tr>
    </tbody>
  </table>
</body>

</html>