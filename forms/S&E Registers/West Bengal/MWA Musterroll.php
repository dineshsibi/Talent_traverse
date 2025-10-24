<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__.'/../../../includes/config.php';
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
$currentState = 'West Bengal'; // Hardcoded for this state template

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


    $branch_address = $first_row['branch_address'] ?? '';
    $location = $first_row['location_name'] ?? '';

} catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; margin: 20px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; min-width: 20px; word-wrap: break-word; }
        th { background-color: #ffffffff; font-weight: bold; }
        .left-align { text-align: left; }
        .form-header-cell { text-align: center; font-weight: bold; font-size: 14px; padding: 8px; }
        .label{
            font-weight: bold;
        }
    </style>
</head>
<body>

<table>
    <!-- Form Header Row (colspan = 39 exactly) -->
    <tr>
        <td colspan="38" class="form-header-cell">
            FORM V <br>
            The West Bengal Minimum Wages Rules, 1951, [Rule 23(5)] <br>
            Muster Roll 
        </td>
    </tr>

    <!-- Establishment Info -->
    <tr>
        <td colspan="12" class="label" style="text-align: left;">Name and Address of the Establishment</td>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
    </tr>
    <tr>
        <th colspan="12" class="label" style="text-align: left;">Place</th>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($location) ?></td>
    </tr>
    <tr>
        <td colspan="12" class="label" style="text-align: left;">Month & Year</td>
        <td colspan="26" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
    </tr>

    <!-- Table Header Row -->
    <tr>
        <th rowspan="2">S.No.</th>
        <th rowspan="2">Employee Code</th>
        <th rowspan="2">Employee Name</th>
        <th rowspan="2">Father's/ Husband's Name</th>
        <th rowspan="2">Sex</th>
        <th rowspan="2">Nature of<br>Work</th>
        <th colspan="31">For the Period Ending</th>
        <th rowspan="2">Remarks</th>
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
    </tr>
    <tr>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th colspan="31" style="text-align:center;">7</th>
        <th>8</th>
    </tr>

    <!-- Data Rows (Make sure 39 <td>s only) -->
    <?php if (!empty($stateData)): ?>
        <?php $i = 1; foreach ($stateData as $row): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td class="left-align"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td class="left-align"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
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
            <td></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="38" style="text-align:center;">No employee data found for West Bengal</td>
        </tr>
    <?php endif; ?>
        <tr>
            <th colspan="38" style="text-align: right;">Signature of Employer</th>
        </tr>
</table>


</body>
</html>
