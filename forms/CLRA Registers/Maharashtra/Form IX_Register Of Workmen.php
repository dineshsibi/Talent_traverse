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
  </style>
</head>

<body>
  <table>
    <thead>
      <tr>
        <th colspan="13" style="text-align: center;">FORM IX<br>See Rule 74<br>Register of Workmen Employed by Contractor</th>
      </tr>
      <tr>
        <th colspan="4" style="text-align: left;">Name and address of Contractor : </th>
        <td style="text-align: left;" colspan="9"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th colspan="4">Nature and location of work :</th>
        <td style="text-align: left;" colspan="9"><?= htmlspecialchars($nature_of_business . ' , ' . $location_code) ?></td>
      </tr>
      <tr>
        <th colspan="4">Name and address of establishment in/under which contract is carried on  :  </th>
        <td style="text-align: left;" colspan="9"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th colspan="4">Name and address of Principal Employer :</th>
        <td style="text-align: left;" colspan="9"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
      </tr>
      <tr>
        <th colspan="4">Wage period</th>
        <td style="text-align: left;" colspan="9"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Sl No</th>
        <th>Employee Code</th>
        <th>Name and surname of workman</th>
        <th>Age and Sex</th>
        <th>Father’s / Husband’s name</th>
        <th>Nature of Employment/Designation</th>
        <th>Permanent House Address of workman (Village and<br>Tahsil/Taluk and District)</th>
        <th>Local Address</th>
        <th>Date of commencement of<br>employment</th>
        <th>Signature of thumb- impression<br>of workman</th>
        <th>Date of termination of<br>employment</th>
        <th>Reasons for termination</th>
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
        <th>12</th>
        <th>13</th>
      </tr>
      <?php if (!empty($stateData)): ?>
        <?php $i = 1;
        foreach ($stateData as $row): ?>
          <?php
          // --- AGE CALCULATION ---
          $dob = $row['date_of_birth'] ?? '';
          $age = '';
          if (!empty($dob)) {
            $dobDate = DateTime::createFromFormat('d-M-y', $dob);
            if ($dobDate) {
              $lastDay = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
              $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);
              if ($referenceDate) {
                $age = $dobDate->diff($referenceDate)->y;
              }
            }
          }
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($age) .' , '. htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td>-</td>
            <td>-</td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td></td>
            <td><?= htmlspecialchars($row['date_of_resign'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['reason_for_exit'] ?? '') ?></td>
            <td></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="14" style="text-align:center;">No data available for Maharashtra</td>
        </tr>
      <?php endif; ?>
      <tr>
        <th colspan="13" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
    </tbody>
  </table>

</body>

</html>