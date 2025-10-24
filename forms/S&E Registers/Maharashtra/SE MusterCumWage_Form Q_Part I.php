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
$currentState = 'Maharashtra'; // Hardcoded for this state template

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


    // Employer data with array handling 
    $employer_name = safe($first_row['employer_name'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');

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
            font-size: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }
        .small-text {
            font-size: 8px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="43" class="title">Form Q<br>The Maharashtra Shops and Establishments (Regulation of Employment and Conditions of Service) Rules, 2018. [See Rule 26 (1)]<br>Muster-Roll Cum Wages Register</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name and Address of the Establishment:</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($client_name .' , '. $branch_address)?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name of the Employer:</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($employer_name)?></td>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Month & Year :</th>
            <td colspan="37" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
        </tr>
        <tr>
            <th rowspan="2">Sr. No.</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Full Name of the worker</th>
            <th rowspan="2">Designation of the worker and nature of work</th>
            <th rowspan="2">Age</th>
            <th rowspan="2">Sex</th>
            <th rowspan="2">Date of entry into service</th>
            <th colspan="2">Working hours</th>
            <th colspan="2">Interval for Rest</th>
            <th colspan="31">Date of the Month</th>
            <th rowspan="2">Total Days worked</th>
        </tr>
        <tr>
            <th>From</th>
            <th>To</th>
            <th>From</th>
            <th>To</th>
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
        <tr>
            <th>1</th>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th colspan="2">7</th>
            <th colspan="2">8</th>
            <th colspan="31">9</th>
            <th>10</th>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
            
           <?php
                $dob = $row['date_of_birth'] ?? '';
                $age = '';

                if (!empty($dob)) {
                    // Create DOB from your DB format (d-M-y, e.g., 20-Jan-04)
                    $dobDate = DateTime::createFromFormat('d-M-y', $dob);

                    if ($dobDate) {
                        // ✅ Get last day of the selected month & year
                        $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year); 
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
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($age) ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td colspan="4"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
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
        <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="25" class="no-data">No contractor data available for Maharashtra</td>
            </tr>
        <?php endif; ?>
            </tbody>
    </table>
</body>
</html>