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
  <table>
    <thead>
      <tr>
        <th class="form-header" colspan="21" style="text-align: center;">FORM NO. 12<br>(PRESCRIBED UNDER RULES 81 &amp; 87)<br>REGISTER OF WORKERS (ADULT &amp; CHILD)</th>
      </tr>
      <tr>
        <th style="text-align: left;" colspan="11">Name and Address of the Factory :</th>
        <td style="text-align: left;" colspan="10"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th style="text-align: left;" colspan="11">Month &amp; Year</th>
        <td style="text-align: left;" colspan="10"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th rowspan="2">Sl No</th>
        <th rowspan="2">WORKERS DISTINGUSHING NUMBER/PF CODE NUMBER </th>
        <th rowspan="2">NAME</th>
        <th rowspan="2">FATHER'S NAME</th>
        <th colspan="3">HOME ADDRESS</th>
        <th rowspan="2">RESIDENTIAL ADDRESS</th>
        <th rowspan="2">DATE OF FIRST EMPLOYMENT</th>
        <th rowspan="2">AGE AT THE TIME OF EMPLOYMENT</th>
        <th rowspan="2">NO. OF CERTIFICATE &amp; ITS DATE IN CASE IF CHILD</th>
        <th rowspan="2">TOKEN NO. GIVING REFERENCE TO CERTIFICATE (IN CASE OF CHILD)</th>
        <th rowspan="2">NATURE OF WORK</th>
        <th rowspan="2">IF WORKING UNDER AND EXEMPTION STATE CORRESPONDING RULES</th>
        <th rowspan="2">NO. OF GENERAL CERTIFICATE OF FITNESS IF AN ADOLESCENT </th>
        <th rowspan="2">LETTER OF GROUP AS IN FORM NO. 11</th>
        <th colspan="2">RATE OF WAGE PER</th>
        <th rowspan="2">TOTAL WEEKLY HOURS</th>
        <th rowspan="2">REMARK</th>
        <th rowspan="2">DATE OF DISCHARGE</th>
      </tr>
      <tr>
        <th>VILLAGE</th>
        <th>P.S</th>
        <th>DISTRICT</th>
        <th>TIME</th>
        <th>PIECES</th>
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
      </tr>
      <?php if (!empty($stateData)): ?>
        <?php $i = 1;
        foreach ($stateData as $row): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td colspan="3">-</td>
            <td>-</td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td>CD - (G-F)</td>
            <td>NIL</td>
            <td>NIL</td>
            <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
            <td>No</td>
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
            <td>-</td>
            <td>0</td>
            <td>-</td>
            <td><?= htmlspecialchars($row['date_of_resign'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="25" class="no-data">No contractor data available for Odisha</td>
        </tr>
      <?php endif; ?>
      <tr>
        <th colspan="21" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
    </tbody>
  </table>

</body>

</html>