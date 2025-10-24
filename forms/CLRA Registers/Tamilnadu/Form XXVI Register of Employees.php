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
$currentState = $_SESSION['current_state'] ?? 'Tamilnadu';
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
        <th class="form-title" colspan="47">
          FORM XXVI<br>
          [See Rule 75]<br>
          Register Of Employment Of Contract Labour
        </th>
      </tr>
      <tr>
        <th colspan="10" style="text-align: left;">Name and Address of the Principal Employer:</th>
        <td colspan="37" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
      </tr>
      <tr>
        <th colspan="10" style="text-align: left;">Name and Address of the Contractor:</th>
        <td colspan="37" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
      </tr>
      <tr>
        <th colspan="10" style="text-align: left;">Nature and location of the work site:</th>
        <td colspan="37" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
      </tr>
      <tr>
        <th colspan="10" style="text-align: left;">Month & Year :</th>
        <td colspan="37" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
      </tr>
      <tr>
        <th>Sl. No</th>
        <th>Name of the Workman</th>
        <th>Sl. No. in the register of workmen </th>
        <th>Age and Date of Sex</th>
        <th>Permanent Home Address</th>
        <th>Local Address</th>
        <th>Designation (Nature of Work)</th>
        <th>Father's/ Husband’s Name</th>
        <th>Date of Entry into service</th>
        <th>Rate of Wages</th>
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
        <th>Number of Days Worked</th>
        <th colspan="2">Signature or Thumb impression of the Workman</th>
        <th>Date of Termination of Employment</th>
        <th colspan="2">Signature of Contractor/ Representative)</th>
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
        <th colspan="31" style="text-align: center;">11</th>
        <th>12</th>
        <th colspan="2">13</th>
        <th>14</th>
        <th colspan="2">15</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($stateData)): ?>
        <?php $i = 1;
        foreach ($stateData as $row): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td>-</td>
            <td>-</td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
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
            <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
            <td colspan="2"></td>
            <td>-</td>
            <td colspan="2"></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="19" style="text-align:center;">No data available for Tamilnadu</td>
        </tr>
      <?php endif; ?>
      <tr>
        <th colspan="47" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
      </tr>
    </tbody>
  </table>
</body>

</html>