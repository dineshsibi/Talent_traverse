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
$pdo = require($configPath);

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentPrincipal = $_SESSION['current_principal'] ?? '';
$currentState = $_SESSION['current_state'] ?? 'Sikkim';
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
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            font-size: smaller;
            margin-bottom: 20px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 400px;
            vertical-align: top;
        }
        .info-content {
            display: inline-block;
            width: calc(100% - 410px);
        }
        .month-year {
            text-align: right;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffff;
        }
        *{
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
     <table>
        <thead>
            <tr>
            <td class="form-title" colspan="28">
                Form XIII <br>
                [See rule 77(2) (a)] <br>
                Register of Wages
            </td>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left;">Name and address of Contractor </th>
                <td colspan="18" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left;">Nature and location of work </th>
                <td colspan="18" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left;">Name and address of establishment in/under which contract is carried on </th>
                <td colspan="18" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left;">Name and address of Principal Employer </th>
                <td colspan="18" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left;">Month & Year</th>
                <td colspan="18" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">Sl. No</th>
                <th rowspan="2">Name and surname of workman</th>
                <th rowspan="2">Serial No. in the Register of workmen employed by contractor</th>
                <th rowspan="2">Nature of Employment/Designation</th>
                <th rowspan="2">No. of working days</th>
                <th rowspan="2">Wage Rate</th>
                <th rowspan="2">Units of work done</th>
                <th rowspan="2">Piece Rate</th>
                <th rowspan="2">Basic Wages</th>
                <th rowspan="2">Dearness Allowance</th>
                <th rowspan="2">HRA</th>
                <th rowspan="2">Over Time ( amount to be transfer to be mentioned)</th>
                <th colspan="2">Amount of Wages Earned</th>
                <th colspan="5" style="text-align: center;">Deductions</th>
                <th rowspan="2">Other Dedcutions</th>
                <th rowspan="2">Total Deduction</th>
                <th rowspan="2">Net Amount Paid</th>
                <th rowspan="2">Pay Mode Name</th>
                <th rowspan="2">Account. No</th>
                <th rowspan="2">Name of the bank</th>
                <th rowspan="2">Signature/ thumb impression of workmen</th>
                <th rowspan="2">Initial of contractor or his representative</th>
                <th rowspan="2">Initials of authorised representative of the principal employer</th>
            </tr>
            <tr>
                <th>Other case payments</th>
                <th>Total (Gross)</th>
                <th>ESI</th>
                <th>PF</th>
                <th>Cab Deduction</th>
                <th>LWF</th>
                <th>PT</th>
            </tr>
        </thead>
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
            <?php
                $pf = (float)($row['pf'] ?? 0);
                $vpf = (float)($row['vpf'] ?? 0);
                $gross = (float)($row['earned_gross'] ?? 0);
                $basic = (float)($row['basic'] ?? 0);
                $da = (float)($row['da'] ?? 0);
                $over_time = (float)($row['over_time_wages'] ?? 0);
                $hra = (float)($row['hra'] ?? 0);
                $total_deductions = (float)($row['total_deductions'] ?? 0);
                $esi = (float)($row['esi'] ?? 0);
                $p_tax = (float)($row['p_tax'] ?? 0);
                $lwf = (float)($row['lwf'] ?? 0);

                $p_f = $pf + $vpf;
                $other_allowances = $gross - $basic + $da + $hra + $over_time;
                $other_deductions = $total_deductions - $esi + $vpf + $p_f + $lwf + $p_tax;
                ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
                <td>Nil</td>
                <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['over_time_wages'] ?? '') ?></td>
                <td><?= htmlspecialchars($other_allowances ?? '') ?></td>
                <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                <td><?= htmlspecialchars($p_f ?? '') ?></td>
                <td>Nil</td>
                <td><?= htmlspecialchars($row['lwf'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['p_tax'] ?? '') ?></td>
                <td><?= htmlspecialchars($other_deductions ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_deductions'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
                <td>Bank Transfer</td>
                <td><?= htmlspecialchars($row['bank_account_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['bank_name'] ?? '') ?></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="28" style="text-align:center;">No data available for Sikkim</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>