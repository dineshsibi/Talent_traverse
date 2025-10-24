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
        .section-header {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="30" class="title">Form Q<br>The Maharashtra Shops and Establishments (Regulation of Employment and Conditions of Service) Rules, 2018. [See Rule 26 (1)]<br>Muster-Roll Cum Wages Register</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name and Address of the Establishment:</th>
            <td colspan="26" style="text-align: left;"><?= htmlspecialchars($client_name .' , '. $branch_address)?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of the Employer:</th>
            <td colspan="26" style="text-align: left;"><?= htmlspecialchars($employer_name)?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month & Year :</th>
            <td colspan="26" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
        </tr>
        <tr>
            <th colspan="9"></th>
            <th colspan="6">Earning</th>
            <th colspan="8">Deduction</th>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th>Sr. No.</th>
            <th>Employee Code</th>
            <th>Full Name of the worker</th>
            <th>Designation of the worker and nature of work</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Date of entry into service</th>
            <th>Minimum rate of wages payable Rs.</th>
            <th>Total production in case of piece rate Rs.</th>
            <th>Actual Wages Paid Rs</th>
            <th>House Rent Allowance Paid Rs.</th>
            <th>Dearness Allowance Paid Rs.</th>
            <th>Gross Amount Payable Rs.</th>
            <th>Total hours of overtime worked during the month</th>
            <th>Overtime Earnings Rs.</th>
            <th>Provident Fund Contribution Rs.</th>
            <th>Family Pension Rs.</th>
            <th>ESI Contribution Rs.</th>
            <th>Professional Tax Rs.</th>
            <th>Income Tax Rs. (if any)</th>
            <th>Loan and Interest Rs.</th>
            <th>Advances Rs.</th>
            <th>Other Deductions Rs. (if any)</th>
            <th>Total Deduction Rs.</th>
            <th>Net Payable Rs.</th>
            <th>Date of Payment</th>
            <th>Bank Account Number of Worker</th>
            <th>Cheque Number and date / RTGS/NEFT transfer date</th>
            <th>Amount Deposited Rs.</th>
            <th>Signature / Thumb Impression of the worker (if required)</th>
        </tr>
        <tr>
            <th>1</th>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
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
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                    $epf = (float) ($row['epf'] ?? 0);
                    $vpf = (float) ($row['vpf'] ?? 0);
                    $calculation1=$epf+$vpf;

                    $other_deduction = (float) ($row['other_deductions'] ?? 0);
                    $esi = (float) ($row['esi'] ?? 0);
                    $ptax = (float) ($row['ptax'] ?? 0);
                    $it_tds = (float) ($row['it_tds'] ?? 0);
                    $advance_recovery = (float) ($row['advance_recovery'] ?? 0);
                    $calculation2 =  $other_deduction - $epf - $esi - $ptax - $it_tds - $advance_recovery ;
                ?>
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
            <td>As Per the Act</td>
            <td>Nil</td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
            <td><?= number_format($calculation1, 2) ?></td>
            <td>Nil</td>
            <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['ptax'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['it_tds'] ?? '') ?></td>
            <td>Nil</td>
            <td><?= htmlspecialchars($row['advance_recovery'] ?? '') ?></td>
            <td><?= number_format($calculation2, 2) ?></td>
            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['bank_account_no'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td></td>
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