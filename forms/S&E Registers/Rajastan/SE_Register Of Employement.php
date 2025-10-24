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
  </style>
</head>

<body>
  <table>
    <thead>
      <tr>
        <th style="text-align: center;" colspan="42">FORM 12<br>The Rajasthan Shops and Commercial Establishments Rules, 1959 See rule 22<br>Register of Employment (Where opening and closing hours are ordinarily uniform)</th>
      </tr>
      <tr>
        <th colspan="7" style="text-align: left;">Name & Address Of the Establishment</th>
        <td colspan="35" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
      </tr>
      <tr>
        <th colspan="7">Month & Year</th>
        <td colspan="35"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Sl.no</th>
        <th>Employee Code</th>
        <th>Name of person employed</th>
        <th>Whether young person or not</th>
        <th>Time at which employment commences</th>
        <th>Time at which employment ceases</th>
        <th>Rest interval</th>
        <th>Day 1</th>
        <th>Day 2</th>
        <th>Day 3</th>
        <th>Day 4</th>
        <th>Day 5</th>
        <th>Day 6</th>
        <th>Day 7</th>
        <th>Day 8</th>
        <th>Day 9</th>
        <th>Day 10</th>
        <th>Day 11</th>
        <th>Day 12</th>
        <th>Day 13</th>
        <th>Day 14</th>
        <th>Day 15</th>
        <th>Day 16</th>
        <th>Day 17</th>
        <th>Day 18</th>
        <th>Day 19</th>
        <th>Day 20</th>
        <th>Day 21</th>
        <th>Day 22</th>
        <th>Day 23</th>
        <th>Day 24</th>
        <th>Day 25</th>
        <th>Day 26</th>
        <th>Day 27</th>
        <th>Day 28</th>
        <th>Day 29</th>
        <th>Day 30</th>
        <th>Day 31</th>
        <th>Total hours worked During the month</th>
        <th>Days on which overtime work is done and extent of such overtime on each day</th>
        <th>Extent of overtime worked during the month</th>
        <th>Extent of overtime worked during the Year</th>
      </tr>

      <?php if (!empty($stateData)): ?>
        <?php $i = 1;
        foreach ($stateData as $row): ?>
          <?php
          $total_worked_days = (float)($row['total_worked_days'] ?? 0);
          $total_hours = $total_worked_days * 8;

          $overtimeDays = [];

          // Check each day (day_1 to day_31) for overtime
          for ($day = 1; $day <= 31; $day++) {
            $dayColumn = 'day_' . $day;
            if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
              $hours = (float)$row[$dayColumn];
              if ($hours > 8.0) {
                $overtimeDays[] = $day; // Store the day number if hours > 8
              }
            }
          }

          // Convert array of days to comma-separated string
          $overtimeDaysStr = !empty($overtimeDays) ? implode(',', $overtimeDays) : '-';
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td>No</td>
            <td colspan="3"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
            <?php for ($day = 1; $day <= 31; $day++): ?>
              <td><?= htmlspecialchars($row['day_' . $day] ?? '') ?></td>
            <?php endfor; ?>
            <td><?= htmlspecialchars(round($total_hours ?? '')) ?></td>
            <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
            <td><?= htmlspecialchars($row['extent_ot_on_which_occasion'] ?? '') ?></td>
            <td>Nil</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="42" style="text-align:center;">No employee data found for Rajastan</td>
        </tr>
      <?php endif; ?>
      <tr>
        <th colspan="42" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
    </tbody>
  </table>

</body>

</html>