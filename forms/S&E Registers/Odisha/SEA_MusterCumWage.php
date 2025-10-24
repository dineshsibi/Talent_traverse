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
$currentState = 'Odisha'; // Hardcoded for this state template

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

    // Fetch NFH data for employees with positive nfh_wages
    $nfhData = [];
    if (!empty($stateData)) {
        $employeeNfhWages = [];
        foreach ($stateData as $row) {
            if (!empty($row['nfh_wages']) && $row['nfh_wages'] > 0) {
                $employeeNfhWages[] = $row['location_code'];
            }
        }
        
        if (!empty($employeeNfhWages)) {
            // Get unique location codes
            $uniqueLocationCodes = array_unique($employeeNfhWages);
            $placeholders = implode(',', array_fill(0, count($uniqueLocationCodes), '?'));
            
            // Fetch NFH descriptions for these location codes
            $nfhSql = "SELECT location_code, description FROM nfh 
                      WHERE location_code IN ($placeholders) 
                      AND month = ? AND year = ?";
            
            $nfhStmt = $pdo->prepare($nfhSql);
            
            // Bind parameters
            $params = array_merge($uniqueLocationCodes, [$filters['month'], $filters['year']]);
            $nfhStmt->execute($params);
            
            $nfhResults = $nfhStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Organize NFH data by location code
            foreach ($nfhResults as $nfhRow) {
                $nfhData[$nfhRow['location_code']] = $nfhRow['description'];
            }
        }
    }

    $first_row = !empty($stateData) ? reset($stateData) : [];

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');

    $branch_address = $first_row['branch_address'] ?? '';
    $principal_employer = $first_row['employer_name'] ?? '';
    $employer_address = $first_row['employer_address'] ?? '';
    $location = $first_row['location_name'] ?? '';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 10px;
            font-size: 8px;
            line-height: 1.1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .main-heading {
            text-align: center;
            font-size: 10px;
            padding: 4px;
            border: 1px solid #000;
            background-color: #ffffff;
            font-weight: bold;
        }

        .sub-heading {
            text-align: left;
            font-weight: bold;
            background-color: #ffffff;
        }

        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            text-align: center;
            width: 20px;
        }

        .section-break {
            page-break-before: always;
        }

        .small-font {
            font-size: 7px;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <td colspan="67" class="main-heading">
                Form 10<br>
                The Orissa Shops and Commercial Establishments (Amendment) Rules, 2009, [See Rule 15 (3)]<br>
                Combined Muster Roll Cum Register of Wages
            </td>
        </tr>

        <!-- In lieu of section -->
        <tr>
            <th colspan="67" class="text-left small-font">
                In lieu of<br>
                1. Form No. 29 (Muster Roll) Rule 104 of Orissa Factories Rules, 1950.<br>
                2. Form No. V (Muster Roll) Rule 26(5) of Orissa Minimum Wages Rules, 1954.<br>
                3. Form No. X (Wages) Rule 26(1) of Orissa Minimum Wages Rules, 1954.<br>
                4. Form No. XIII (Muster Roll) Rule 33(1) of Orissa Beedi & Cigar Workers (Condition of employment) Rules, 1969.<br>
                5. Form No. XVI (Muster Roll) Rule 239(1) a of Orissa Building & Other Construction Workers etc. Rules, 2002.<br>
                6. From No. XVII (Register of Wages) Rule 239 (1) a of Orissa Building & Other Construction Workers etc. Rules, 2002.<br>
                7. From No. XVIII(Register of Wage-cum-Muster Roll) Rule 239(1) a of Orissa Building & Other Construction Workers etc. Rules, 2002.<br>
                8. Form No. XVII (Muster Roll) Rule 52(2)(a) of Orissa Inter-State Migrant Workmen (RE&CS) Rules, 1980.<br>
                9. Form No. XVIII(Register of Wages) Rule 52(2)(a) of Orissa Inter-State Migrant Workmen (RE&CS) Rules, 1980<br>
                10. Form No. 10 (Register of payment) of Orissa Shops and Commercial Establishment Rules, 1958.<br>
                11. Form No. 8 (Daily record of works & orders relating to compensating Leave and Deduction from wages of Orissa Shops and Commercial Establishment Rules, 1958.<br>
                12. Form X (Muster Roll) Rule 36 of Orissa Motor Transport Workers Rules, 1966.<br>
                13. From XIII (Wages) Rule 77(2)(a) of Orissa Contract Labour (R&A), Rules, 1975.<br>
                14. Form XII (Muster Roll) Rule 77(2)(a) of Orissa Contract Labour (R&A), Rules, 1975.<br>
                15. Form VI (Muster Roll) Rule 9 of Orissa Industrial Employment (N&F) H. Rules, 1972.
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="4" class="sub-heading">Name & Address of the Factory/ Establishment</td>
            <td colspan="63" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="sub-heading">Name & Address of the Contractor ( if any)</td>
            <td colspan="63" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="sub-heading">Name & Address of the Principal employer</td>
            <td colspan="63" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $employer_address) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="sub-heading">Place of Work</td>
            <td colspan="63" style="text-align: left;"><?= htmlspecialchars($location) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="sub-heading">Month & Year</td>
            <td colspan="63" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>

        <!-- Column Headers - First Row -->
        <tr>
            <th rowspan="2">Sl No</th>
            <th rowspan="2">Name of Employee</th>
            <th rowspan="2">Father/Husband Name</th>
            <th rowspan="2">Sex M/F</th>
            <th rowspan="2">Date of Birth</th>
            <th rowspan="2">Emp. No./<br>Sl.No. in<br>register of<br>employees</th>
            <th rowspan="2">Degn/Dept</th>
            <th rowspan="2">Date of<br>joining</th>
            <th rowspan="2">ESIC No</th>
            <th rowspan="2">PF No</th>
            <th colspan="31">ATTENDANCE<br>Units of work done(if piece rated)</th>
            <th rowspan="2">No. of payable days</th>
            <th rowspan="2">Name of N&F Holiday for which wages have been paid</th>
            <th colspan="10">Earned wages and other allowances</th>
            <th rowspan="2">Total</th>
            <th colspan="11">Deductions</th>
            <th rowspan="2">Net Payable</th>
            <th rowspan="2">Date of payment</th>
        </tr>

        <!-- Column Headers - Second Row -->
        <tr>
            <!-- Attendance Dates -->
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

            <!-- Earned wages -->
            <th>Basic</th>
            <th>DA/VDA</th>
            <th>HRA</th>
            <th>Conv.<br>Allow.</th>
            <th>Medical Allowance</th>
            <th>Attendance Bonus</th>
            <th>Special allowance</th>
            <th>OT</th>
            <th>Msic. Earnings</th>
            <th>Others</th>

            <!-- Deductions -->
            <th>ESI</th>
            <th>PF</th>
            <th>PT</th>
            <th>TDS</th>
            <th>Society</th>
            <th>Insurance</th>
            <th>Salary Advance</th>
            <th>Fine</th>
            <th>Damages</th>
            <th>Others</th>
            <th>Total</th>
        </tr>

        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                        $epf = (float)($row['epf'] ?? 0);
                        $vpf = (float)($row['vpf'] ?? 0);
                        $pf = $epf + $vpf;

                        $gross = (float)($row['gross_wages'] ?? 0);
                        $basic = (float)($row['basic'] ?? 0);
                        $da = (float)($row['da'] ?? 0);
                        $hra = (float)($row['hra'] ?? 0);
                        $conveyance = (float)($row['conveyance_allowance'] ?? 0);
                        $medical = (float)($row['medical_allowance'] ?? 0);
                        $attendance = (float)($row['attendance_bonus'] ?? 0);
                        $special = (float)($row['special_allowance'] ?? 0);
                        $overtime = (float)($row['over_time_allowance'] ?? 0);
                        
                        $others = $gross - ($basic + $da + $hra + $conveyance + $medical + $attendance + $special + $overtime);

                        // Check if employee has NFH wages and get description
                        $nfhDescription = '';
                        if (!empty($row['nfh_wages']) && $row['nfh_wages'] > 0 && 
                            !empty($row['location_code']) && 
                            isset($nfhData[$row['location_code']])) {
                            $nfhDescription = $nfhData[$row['location_code']];
                        }
                    ?>
                    <!-- Data Row -->
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td>-</td>
                        <td>-</td>

                        <!-- Attendance data -->
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
                        <td><?= htmlspecialchars($nfhDescription) ?></td>

                        <!-- Earned wages data -->
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['conveyance_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['medical_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['attendance_bonus'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['special_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td>Nil</td>
                        <td><?= htmlspecialchars($others ?? '') ?></td>

                        <!-- Total -->
                        <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>

                        <!-- Deductions data -->
                        <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                        <td><?= htmlspecialchars($pf ?? '') ?></td>
                        <td><?= htmlspecialchars($row['ptax'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['it_tds'] ?? '') ?></td>
                        <td>-</td>
                        <td><?= htmlspecialchars($row['insurance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['advance_recovery'] ?? '') ?></td>
                        <td colspan="2"><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['other_deductions'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>

                        <!-- Net Payable and Date of Payment -->
                        <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="25" class="no-data">No contractor data available for Odisha</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>