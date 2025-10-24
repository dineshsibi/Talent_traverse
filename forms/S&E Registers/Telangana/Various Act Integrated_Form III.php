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
$currentState = 'Telangana'; // Hardcoded for this state template

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

// Get one sample row to extract address
$branch_address = $first_row['branch_address'] ?? '';
$employer_name = $first_row['employer_name'] ?? '';

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
            text-align: left;
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
        .left-align {
            text-align: left;
        }
        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            width: 20px;
        }
        .small-text {
            font-size: 10px;
        }
        .center-align {
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
       <thead>
            <tr>
                <th colspan="27" class="title">
                    Form-III<br>
                    Various Labour Laws <br>
                    Muster Roll-Cum Register of Wages/Deductions/Overtime/Advances
                </th>
            </tr>
            
            <!-- Establishment Information -->
            <tr>
                <th colspan="7" class="left-align">For the month:</th>
                <td colspan="20" class="left-align"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th colspan="7" class="left-align">Name of the Establishment and address:</th>
                <td colspan="20" class="left-align"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="7" class="left-align">Location of work</th>
                <td colspan="20" class="left-align"><?= htmlspecialchars($first_row['location_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="7" class="left-align">Name and address of Employer / Manager</th>
                <td colspan="20" class="left-align"><?= htmlspecialchars($first_row['employer_name'] ?? ''). ' , ' . htmlspecialchars($first_row['employer_address'] ?? '')?></td>
            </tr>
            <tr>
                <th colspan="7" class="left-align">Nature of Establishment / Production / Business etc.</th>
                <td colspan="20" class="left-align"><?= htmlspecialchars($first_row['nature_of_business'] ?? '') ?></td>
            </tr>
            
            <!-- Main Header Row -->
            <tr>
                <th>Sl. No</th>
                <th>Employee ID</th>
                <th>Name of the worker (ID/Token No. if any)</th>
                <th>Age/ Date of Birth</th>
                <th>Address</th>
                <th>Education / Skill</th>
                <th>Sex (M / F)</th>
                <th>Father's/ husband's Name</th>
                <th>Name & address of nominee</th>
                <th>Designation / category / nature of work performed</th>
                <th>Total No. of days worked</th>
                <th>Category of Leave</th>
                <th>Leaves availed (No. of days)</th>
                <th>Total Balance Leaves</th>
                <th>Wage rate / pay or (piece rate / wages per unit)</th>
                <th>Other allowances</th>
                <th>Overtime worked (Number of hours in the month)</th>
                <th>Amount of over time wages</th>
                <th>Amount of Maternity benefit (if any)</th>
                <th>Any other amount (Please mention)</th>
                <th>Total/ gross wages / earnings</th>
                <th>Amount of advances/loans if any and purpose of advance</th>
                <th>Deductions of fines imposed If any</th>
                <th>Deductions like EPF/ESI/Welfare Fund etc.(if any)</th>
                <th>Net amount payable</th>
                <th>Signature/thumb impression</th>
                <th>Remarks, if any</th>
            </tr>
            <tr>
                <th>(1)</th>
                <th>(1)</th>
                <th>(2)</th>
                <th>(3)</th>
                <th>(4)</th>
                <th>(5)</th>
                <th>(6)</th>
                <th>(7)</th>
                <th>(8)</th>
                <th>(9)</th>
                <th>(10)</th>
                <th>(11)</th>
                <th>(12)</th>
                <th>(13)</th>
                <th>(14)</th>
                <th>(15)</th>
                <th>(16)</th>
                <th>(17)</th>
                <th>(18)</th>
                <th>(19)</th>
                <th>(20)</th>
                <th>(21)</th>
                <th>(22)</th>
                <th>(23)</th>
                <th>(24)</th>
                <th>(25)</th>
                <th>(26)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
            <?php $Gross= (float)($row['gross_wages'] ?? 0);
                $Overtime = (float)($row['over_time_allowance'] ?? 0);
                $normal_earnings=  $Gross - $Overtime;

                $overtimeDays = [];
                ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
                <td>-</td>
                <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                <td>-</td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                <td>PL <br> CL <br> SL</td>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?> <br>
                    <?= htmlspecialchars($row['cl_availed'] ?? '') ?> <br>
                    <?= htmlspecialchars($row['sl_availed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?> <br>
                    <?= htmlspecialchars($row['cl_closing'] ?? '') ?> <br>
                    <?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                <td>-</td>
                <td>-</td>
                <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                <td>-</td>
                <td>-</td>
                <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                <td></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="27" class="no-data">No contractor data available for Telangana</td>
            </tr>
        <?php endif; ?>
            <tr>
                <th colspan="27" style="text-align: right;">
                    Signature of the employer / contractor<br>
                    Name of signatory<br>
                    Certificate by the Principal Employer if the employer is contractor
                </th>
            </tr>
            <tr>
                <th colspan="27" class="center-align">
                    This is to certify that the contractor has paid wages to workmen employed by him as shown in this register in his / in the presence of his authorized representatives.
                </th>
            </tr>
            <tr>
                <th colspan="27" style="text-align: right;">
                    Signature of Representative of Principal employer<br>
                    Name of signatory<br>
                    Designation in the Establishment
                </th>
            </tr>
        </tbody>
    </table>
</body>
</html>