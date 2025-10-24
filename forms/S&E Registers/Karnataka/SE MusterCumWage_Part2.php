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
        .subheader {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="34" class="title">FORM T<br>COMBINED MUSTER ROLL- CUM - REGISTER OF WAGES<br>[See Rule 24(9-B) of Kamataka Shops & Commercial Establishment Rules, 1963]<br>in lieu of <br>1. Form I, II of Rule 22(4); Form IV of Rule 28 (2); Forms V and VIl of Rule 29(1) and (5) of karnataka Minimum Wages Rules, 1958<br>2. Form I of Rules 3(1) of Kamataka Payment of Wages Rules, 1963<br>3. Form XIII of Rules 75; Form XV, XVII, XX, XXI, XXII, and XXIII of 78(1)(a)(i), (ii) & (iii) of the Contract Labour (Regulation and Abolition) (Kamataka) Rules, 1974 <br>4. Form XIII of Rule 43; Forms XVII, XVIII, XIX, XX, XXI, XXII of Rule 46(2)(a),(c) and (d) of Inter-state Migrant Workmen (Regulation of Employment and conditions of service) Karnataka Rules, 1981nd conditions of service) Karnataka Rules, 1981</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month / Year:</th>
            <td colspan="30" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name and address of the Establishment</th>
            <td colspan="30" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name and Address of employer</th>
            <td colspan="30" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?></td>
            
        </tr>
        <tr>
            <th colspan="7"><b>Employee Details</th>
            <th colspan="13" class="subheader">Earned wages and other allowances</th>
            <th colspan="11" class="subheader">Deductions</th>
            <th></th><th></th><th></th>
        </tr>
        <tr>
            <th>Sl No </th>
            <th>Employee Code </th>
            <th>Name of the Employee Father/Husband Name </th>
            <th>Male/Female </th>
            <th>Department/Designation </th>
            <th>Date of Joining </th>
            <th>Wages fixed including VDA</th>
            <th>Basic </th>
            <th>DA/VDA </th>
            <th>HRA </th>
            <th>Conveyance </th>
            <th>Medical Allowance </th>
            <th>Attendance Bonus </th>
            <th>Special allowance </th>
            <th>OT </th>
            <th>NFH </th>
            <th>Maternity Benefit </th>
            <th>Others </th>
            <th>Subsistence allowance if any </th>
            <th>Total </th>
            <th>ESI </th>
            <th>PF </th>
            <th>PT </th>
            <th>TDS </th>
            <th>Society </th>
            <th>Insurance </th>
            <th>Salary Advance </th>
            <th>Fines </th>
            <th>Damages/Loss </th>
            <th>Others </th>
            <th>Total </th>
            <th>Net Payable </th>
            <th>Mode of Payment Cash/Cheque No. </th>
            <th>Employee signature or thumb impression </th>
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
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>

            <?php
                $other_allowance = (float)($row['other_allowance'] ?? 0);
                $statutory= (float)($row['statutory_bonus'] ?? 0);
                $exgratia= (float)($row['exgratia_bonus'] ?? 0);
                $advance= (float)($row['advance'] ?? 0);
                $calculation1= $other_allowance + $statutory +  $exgratia + $advance;
   
                $epf = (float)($row['epf'] ?? 0);
                $vpf = (float)($row['vpf'] ?? 0);
                $calculation2 = $epf + $vpf ;
 
                $other_deduction = (float)($row['other_deductions'] ?? 0);
                $lwf = (float)($row['lwf'] ?? 0);
                $calculation3 = $other_deduction + $lwf ;

             ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['conveyance_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['medical_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['attendance_bonus'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['special_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['nfh_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['maternity_bonus'] ?? '') ?></td>
            <td><?= htmlspecialchars($calculation1 ?? '') ?></td>
            <td><?= htmlspecialchars($row['subsistence_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
            <td><?= htmlspecialchars($calculation2 ?? '') ?></td>
            <td><?= htmlspecialchars($row['ptax'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['it_tds'] ?? '') ?></td>
            <td>NIL</td>
            <td><?= htmlspecialchars($row['insurance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['advance_recovery'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
            <td><?= htmlspecialchars($calculation3 ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td>Bank Transfer</td>
            <td></td>
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