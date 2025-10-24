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
$currentState = $_SESSION['current_state'] ?? 'Maharashtra';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
    $sql = "SELECT * FROM combined_data 
            WHERE client_name = :client_name
            AND principal_employer_name = :principal_employer
            AND state LIKE :state
            AND location_code = :location_code";

    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }

    $stmt = $pdo->prepare($sql);

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
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
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
    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
        vertical-align: top;
    }
    th {
        font-weight: bold;
    }
    .form-header {
        text-align: center;
        font-weight: bold;
    }
    .page-break {
        page-break-after: always;
    }
</style>
</head>
<body>
<?php
$totalEmployees = count($stateData);
foreach ($stateData as $index => $row):
    $rate_of_wage = (float) ($row['rate_of_wage'] ?? 0);
    $piece_rate = $rate_of_wage / 31;
?>
<div class="employee-form <?= ($index < $totalEmployees - 1) ? 'page-break' : '' ?>">
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="2" style="text-align: center;">
                    Attendance card-cum-wage slip<br>
                    [See Rule 77(2)(b)]
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Name and address of Contractor :</th>
                <td><?= htmlspecialchars($client_name . ' , ' . $address) ?></td>
            </tr>
            <tr>
                <th>Nature and location of work :</th>
                <td><?= htmlspecialchars($nature . ' , ' . $currentLocation) ?></td>
            </tr>
            <tr>
                <th>Name and address of establishment in/under which contract is carried on :</th>
                <td><?= htmlspecialchars($client_name . ' , ' . $address) ?></td>
            </tr>
            <tr>
                <th>Name and address of Principal Employer :</th>
                <td><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th>Name and Father’s/Husband’s name of the workman :</th>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') . ' , ' . htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th>For the Month ending :</th>
                <td><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>1. No. of days worked</th>
                <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
            </tr>
            <tr>
                <th>2. No. of units worked in case of piece-rate workers</th>
                <td>Nil</td>
            </tr>
            <tr>
                <th>3. Rate of daily wages/piece-rate</th>
                <td><?= number_format($piece_rate) ?></td>
            </tr>
            <tr>
                <th>4. Amount of overtime wages</th>
                <td><?= htmlspecialchars($row['overtime_earnings'] ?? '') ?></td>
            </tr>
            <tr>
                <th>5. Gross wages payable</th>
                <td><?= htmlspecialchars($row['earned_gross'] ?? '') ?></td>
            </tr>
            <tr>
                <th>6. Deductions, if any</th>
                <td><?= htmlspecialchars($row['total_deductions'] ?? '') ?></td>
            </tr>
            <tr>
                <th>7. Net amount of wages paid</th>
                <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
            </tr>
            <tr>
                <th>Date: </th>
                <th>Initials of the Contractor or his Representative</th>
            </tr>
        </tbody>
    </table>
</div>
<?php endforeach; ?>
</body>
</html>
