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
$currentState = $_SESSION['current_state'] ?? 'Haryana';
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffff;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }

        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .footer {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-title" colspan="30">
                    <center>FORM B <br>
                        FORMAT FOR WAGE REGISTER
                    </center>
                </th>
            </tr>
            <tr>
                <th colspan="11">Name and address of Contractor </th>
                <td colspan="19"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="11">Nature and location of work </th>
                <td colspan="19"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
            </tr>
            <tr>
                <th colspan="11">Name and address of establishment in/under which contract is carried on </th>
                <td colspan="19"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="11">Name and address of Principal Employer </th>
                <td colspan="19"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="11">LIN</th>
                <td colspan="19">-</td>
            </tr>
            <tr>
                <th colspan="11">Wage period</th>
                <td colspan="19"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th style="text-align: center;" colspan="30">Rate of Minimum Wages and since the date: APRIL-2025</th>
            </tr>
            <tr>
                <td colspan="4"></td>
                <th colspan="7">Highly Skilled</th>
                <th colspan="6">Skilled</th>
                <th colspan="7">Semi-Skilled</th>
                <th colspan="4">Un Skilled</th>
            </tr>
            <tr>
                <th colspan="4">Basic</th>
                <td colspan="7">0</td>
                <td colspan="6">0</td>
                <td colspan="7">0</td>
                <td colspan="4">0</td>
            <tr>
                <th colspan="4">DA</th>
                <td colspan="7">0</td>
                <td colspan="6">0</td>
                <td colspan="7">0</td>
                <td colspan="4">0</td>
            </tr>
            <tr>
                <th colspan="4">Overtime</th>
                <td colspan="7">0</td>
                <td colspan="6">0</td>
                <td colspan="7">0</td>
                <td colspan="4">0</td>
            </tr>
            <tr>
                <th>Sl. No</th>
                <th>Sl. No. in<br>Employee<br>Register</th>
                <th>Name</th>
                <th>Rate of<br>Wage</th>
                <th>No. of Days</th>
                <th>Overtime hours<br>worked</th>
                <th>Basic Pay</th>
                <th>Special<br>Basic Pay</th>
                <th>DA</th>
                <th>Payments<br>Overtime</th>
                <th>HRA</th>
                <th>*Others</th>
                <th>Total</th>
                <th>PF Deduction</th>
                <th>ESIC Deduction</th>
                <th>Society Deduction</th>
                <th>Income Tax Deduction</th>
                <th>Insurance Deduction</th>
                <th>PT Deduction</th>
                <th>LWF Deduction</th>
                <th>Other Deduction</th>
                <th>Recoveries Deduction</th>
                <th>Total Deduction</th>
                <th>Net<br>Payment</th>
                <th>Employer PF Welfare Fund</th>
                <th>Receipt by Employee/Bank<br>Transaction ID</th>
                <th>Date of Payment</th>
                <th>Remarks</th>
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
                <th>23</th>
                <th>24</th>
                <th>25</th>
                <th>26</th>
                <th>27</th>
                <th>28</th>
                <th>29</th>
                <th>30</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td>-</td>
                        <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['overtime_wages'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['vpf'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                        <td>-</td>
                        <td><?= htmlspecialchars($row['tds'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['medical_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['p_tax'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['lwf'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['other_deduction'] ?? '') ?></td>
                        <td>-</td>
                        <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['pf'] ?? '') ?></td>
                        <td></td>
                        <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                        <td></td>
                    </tr>

                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No data available for Delhi</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="5">Date </th>
                <td colspan="6"></td>
                <th colspan="7">Authorised Signatory</th>
                <td colspan="10"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>