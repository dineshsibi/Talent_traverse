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
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
    $principal_employer = safe($currentPrincipal);

    $address = safe($first_row['address'] ?? '');
    $nature = safe($first_row['nature_of_business'] ?? '');
    $principal_employer_address = safe($first_row['principal_employer_address'] ?? '');
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
            margin-bottom: 20px;
        }

        .subtitle {
            font-size: smaller;
            text-align: center;
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
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .header-info {
            margin-bottom: 15px;
        }

        .header-info div {
            margin-bottom: 5px;
        }

        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            text-align: center;
            padding: 5px;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-title" colspan="28">Form XIII<br>
                    [See Rule 77 (1) (a) (i)]<br>
                    Register of Wages for the month of
                </th>
            </tr>
            <tr>
                <th colspan="10">Name and Address of Contractor</th>
                <td colspan="18"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th colspan="10">Nature and Location of Work</th>
                <td colspan="18"><?= htmlspecialchars($nature . ' & ' . $currentLocation) ?></td>
            </tr>
            <tr>
                <th colspan="10">Name and Address of Principal Employer</th>
                <td colspan="18"><?= htmlspecialchars($principal_employer . ' & ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="10">Month & Year</th>
                <td colspan="18"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th colspan="10">Name and Address of Establishment in/under which Contract is carried on</th>
                <td colspan="18"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th rowspan="2">SI.No.</th>
                <th rowspan="2">Name and Surname of the Workmen</th>
                <th rowspan="2">Serial No. in the Register of workmen employed by contractor</th>
                <th rowspan="2">Designation / Nature of work done</th>
                <th rowspan="2">No. of working days</th>
                <th rowspan="2">Wage rate</th>
                <th rowspan="2">Units of work done</th>
                <th rowspan="2">Piece rate</th>
                <th rowspan="2">Basic Wages</th>
                <th rowspan="2">Dearness Allowance</th>
                <th rowspan="2">HRA</th>
                <th rowspan="2">Over Time (amount to be transfer to be mentioned)</th>
                <th colspan="2">Amount of Wages Earned</th>
                <th colspan="5">Deduction</th>
                <th rowspan="2">Other Deductions</th>
                <th rowspan="2">Total Deduction</th>
                <th rowspan="2">Net Amount Paid</th>
                <th rowspan="2">Pay Mode Name</th>
                <th rowspan="2">Account No.</th>
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
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $pf = (float) ($row['pf'] ?? 0);
                    $vpf = (float) ($row['vpf'] ?? 0);
                    $calculation1 = $pf + $vpf;
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['hra'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_wages'] ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['esi'] ?? '') ?></td>
                        <td><?= number_format($calculation1, 2) ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['lwf'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['p_tax'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['other_deductions'] ?? '') ?></td>
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
                    <td colspan="25" class="no-data">No contractor data available for Haryana</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>