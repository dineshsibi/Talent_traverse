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
    $employer_address = safe($first_row['employer_address'] ?? '');
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
            <th colspan="38" class="title">Form II<br>The Maharashtra Minimum Wages Rules, 1963 Rule 27(1)<br>Muster-roll-cum-wages Register</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name and Address of the Establishment / Shop :</th>
            <td colspan="30" style="text-align: left;"><?= htmlspecialchars($client_name .' , '. $branch_address)?></td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name and address of the Employer :</th>
            <td colspan="30" style="text-align: left;"><?= htmlspecialchars($employer_name .' , '. $employer_address)?></td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Month & Year :</th>
            <td colspan="30" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
        </tr>
        <tr>
            <th rowspan="2">Sl No</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Name of the Employee</th>
            <th rowspan="2">Date Of Entry</th>
            <th rowspan="2">Date Of Leaving</th>
            <th rowspan="2">Minimum Rates of wages payable</th>
            <th rowspan="2">Actual Rates of wages payable(Basic)</th>
            <th rowspan="2">Total production in case of piece rate</th>
            <th rowspan="2">Total overtime hours worked</th>
            <th rowspan="2">H.R.A payable</th>
            <th rowspan="2">Conveyance</th>
            <th rowspan="2">Statutory Bonus</th>
            <th rowspan="2">Overtime Earnings</th>
            <th rowspan="2">Special Allowance</th>
            <th rowspan="2">Other Allowance</th>
            <th rowspan="2">Gross Wages payable</th>
            <th colspan="9">Deductions</th>
            <th rowspan="2">Total Deductions</th>
            <th rowspan="2">Net Wages paid</th>
            <th rowspan="2">Leave Wages Previous balance</th>
            <th colspan="4">Earned Leave</th>
            <th colspan="3">Casual Leave</th>
            <th rowspan="2">Date of payment of wages</th>
            <th rowspan="2">Mode of Payment</th>
            <th rowspan="2">Signature of thumb Impression of the employees</th>
        </tr>
        <tr>
            <th>PF</th>
            <th>ESI</th>
            <th>PT</th>
            <th>IT</th>
            <th>LWF</th>
            <th>VPF</th>
            <th>Advance</th>
            <th>Fine& Damage</th>
            <th>Other Deduction</th>
            <th>During the month</th>
            <th>LeaveAvailed during the month</th>
            <th>Leave refused During the month</th>
            <th>Leave Balance at the end of the month</th>
            <th>Earned during the month</th>
            <th>Availed during the month</th>
            <th>Balance at the end of the month</th>
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
            <th>32</th>
            <th>33</th>
            <th>34</th>
            <th>35</th>
            <th>36</th>
            <th>37</th>
            <th>38</th>
        </tr>
         <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>

            <?php
                $gross_wages = (float) ($row['gross_wages'] ?? 0);
                $special_allowance = (float) ($row['special_allowance'] ?? 0);
                $over_time = (float) ($row['over_time_allowance'] ?? 0);
                $statutory = (float) ($row['statutory_bonus'] ?? 0);
                $conveyance = (float) ($row['conveyance_allowance'] ?? 0);
                $hra = (float) ($row['hra'] ?? 0);
                $calculation1=$gross_wages-$special_allowance-$over_time-$statutory-$conveyance-$hra;

                $other_deduction = (float) ($row['other_deductions'] ?? 0);
                $insurance = (float) ($row['insurance'] ?? 0);
                $calculation2 =  $other_deduction + $insurance;
            ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
            <td>As Per Act</td>
            <td><?= htmlspecialchars($row['fixed_basic'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['conveyance_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['statutory_bonus'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['special_allowance'] ?? '') ?></td>
            <td><?= number_format($calculation1, 2) ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['epf'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['ptax'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['it_tds'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['lwf'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['vpf'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['advance_recovery'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
            <td><?= number_format($calculation2, 2) ?></td>
            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
            <td>Nil</td>
            <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['cl_opening'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
            <td>Bank Transfer</td>
            <td></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="25" class="no-data">No contractor data available for karnataka</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>