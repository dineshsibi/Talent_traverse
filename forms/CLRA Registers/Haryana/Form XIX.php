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
    // Build SQL query
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
        AND state LIKE :state
        AND location_code = :location_code";

    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }

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

    // Safe variables
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
            margin-bottom: 5px;
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
            background-color: #ffffff;
        }
        .employee-form {
            margin: 15px;
        }
        @media print {
            .employee-form {
                margin: 0;
            }
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

    $rate_of_wage = (float)($row['rate_of_wage'] ?? 0);
    $calculation = $rate_of_wage / 31;
?>
    <div class="employee-form" style="<?= $isLast ? '' : 'page-break-after: always;' ?>">
        <table>
            <tr>
                <th class="form-title" colspan="4">FORM XIX<br>
                    [See Rule 78 (2) (b)]<br>
                    Wage Slip
                </th>
            </tr>
            <tr>
                <th colspan="2">Name and address of Contractor </th>
                <td colspan="2"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th colspan="2">Nature and location of work </th>
                <td colspan="2"><?= htmlspecialchars($nature . ' & ' . $currentLocation) ?></td>
            </tr>
            <tr>
                <th colspan="2">Name and address of establishment in/under which contract is carried on </th>
                <td colspan="2"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th colspan="2">Name and address of Principal Employer </th>
                <td colspan="2"><?= htmlspecialchars($principal_employer . ' & ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="2">Name and Father's/Husband's name of the workman </th>
                <td colspan="2"><?= htmlspecialchars($row['employee_name'] ?? '') . ' & ' . ($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="2">For the Month ending </th>
                <td colspan="2"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th colspan="2">1. No. of days worked</th>
                <td colspan="2"><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="2">2. No. of units worked in case of piece-rate workers</th>
                <td colspan="2">NIL</td>
            </tr>
            <tr>
                <th colspan="2">3. Rate of daily wages/piece-rate</th>
                <td colspan="2"><?= number_format($calculation, 2) ?></td>
            </tr>
            <tr>
                <th colspan="2">4. Amount of overtime wages</th>
                <td colspan="2"><?= htmlspecialchars($row['overtime_earnings'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="2">5. Gross wages payable</th>
                <td colspan="2"><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="2">6. Deductions, if any</th>
                <td colspan="2"><?= htmlspecialchars($row['total_deductions'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="2">7. Net amount of wages paid</th>
                <td colspan="2"><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
            </tr>
            <tr>
                <th style="text-align: left;" colspan="2">Date</th>
                <th style="text-align: left;" colspan="2">Initials of the Contractor or his Representative</th>
            </tr>
        </table>
    </div>
<?php endforeach; ?>
</body>
</html>
