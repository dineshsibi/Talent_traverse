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
$currentState = 'Karnataka'; // Hardcoded for this state template

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


    $client_name = safe($first_row['client_name'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');
    $employer_name=safe($first_row['employer_name'] ?? '');
    $employer_address=safe($first_row['employer_address'] ?? '');

    } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="41" class="title">FORM T<br>COMBINED MUSTER ROLL- CUM - REGISTER OF WAGES<br>[See Rule 24(9-B) of Kamataka Shops & Commercial Establishment Rules, 1963]<br>in lieu of <br>1. Form I, II of Rule 22(4); Form IV of Rule 28 (2); Forms V and VIl of Rule 29(1) and (5) of karnataka Minimum Wages Rules, 1958<br>2. Form I of Rules 3(1) of Kamataka Payment of Wages Rules, 1963<br>3. Form XIII of Rules 75; Form XV, XVII, XX, XXI, XXII, and XXIII of 78(1)(a)(i), (ii) & (iii) of the Contract Labour (Regulation and Abolition) (Kamataka) Rules, 1974 <br>4. Form XIII of Rule 43; Forms XVII, XVIII, XIX, XX, XXI, XXII of Rule 46(2)(a),(c) and (d) of Inter-state Migrant Workmen (Regulation of Employment and conditions of service) Karnataka Rules, 1981nd conditions of service) Karnataka Rules, 1981</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month / Year:</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name and address of the Establishment</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name and Address of employer</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?></td>
        </tr>
        <tr>
            <th colspan="8"><b>Employee Details</b></th>
            <td colspan="31" style="text-align: center;"><b>ATTENDANCE (Please mention the date of suspension of employees, if any)</b></td>
            <th rowspan="2"><b>No. of payable days </b></th>
            <th rowspan="2"><b>Total OT hours worked </b></th>       
        </tr>
        <tr>
            <th>Sl No </th>
            <th>Employee Code </th>
            <th>Name of the Employee Father/Husband Name </th>
            <th>Male/Female </th>
            <th>Department/Designation </th>
            <th>Date of Joining</th>
            <th>ESIC No </th>
            <th>PF No </th>
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
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td>-</td>
            <td>-</td>
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
            <td><?= htmlspecialchars($row['day_23'] ?? '') ?>/td>
            <td><?= htmlspecialchars($row['day_24'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_25'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_26'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_27'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_28'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_29'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_30'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_31'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <th colspan="19" style="text-align:center;">No data available for Karnataka</th>
            </tr>
        <?php endif; ?>
    </tbody>
    </table>
</body>
</html>