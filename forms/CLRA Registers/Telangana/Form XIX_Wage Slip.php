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
$currentState = $_SESSION['current_state'] ?? 'Telangana';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
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
            margin-bottom: 20px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Force equal column distribution */
            font-size: 10px;
            /* Reduce font size for fitting */
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: left;
            word-wrap: break-word;
            /* Wrap long text */
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }

        .signatory {
            text-align: right;
            margin-top: 30px;
        }

        .info-content {
            display: inline-block;
            width: calc(100% - 410px);
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;
        $isLast = ($currentEmployee === $totalEmployees);
    ?>
        <div class="employee-form <?= $isLast ? '' : 'page-break' ?>">
            <table>
                <tr>
                    <th class="form-title" colspan="4">
                        FORM XIX <br>
                        [See Rule 78 (2) (b)] <br>
                        Wage Slip
                    </th>
                </tr>
                <tr>
                    <th colspan="2">Name and address of Contractor</th>
                    <td colspan="2"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Nature and location of work</th>
                    <td colspan="2"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Name and address of establishment</th>
                    <td colspan="2"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Name and address of Principal Employer</th>
                    <td colspan="2"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Name and Father's/Husband's name of the workman</th>
                    <td colspan="2"><?= htmlspecialchars(($row['employee_name'] ?? '') . ' , ' . ($row['father_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th colspan="2">For the Month ending</th>
                    <td colspan="2"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                </tr>
                <tr>
                    <th colspan="2">1. No. of days worked</th>
                    <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="2">2. No. of units worked (piece-rate)</th>
                    <td colspan="2" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th colspan="2">3. Rate of daily wages/piece-rate</th>
                    <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?> / 31</td>
                </tr>
                <tr>
                    <th colspan="2">4. Amount of overtime wages</th>
                    <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['overtime_rate_of_wages'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="2">5. Gross wages payable</th>
                    <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="2">6. Deductions, if any</th>
                    <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['total_deductions'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="2">7. Net amount of wages paid</th>
                    <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="2" style="text-align: left;">Date :</th>
                    <th colspan="2" style="text-align: right;">Initials of the Contractor / Representative</th>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
</body>
</html>