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
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Kerala';

try {
    $sql = "SELECT * FROM input 
            WHERE client_name = :client_name
            AND state LIKE :state
            AND location_code = :location_code";

    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }

    $stmt = $pdo->prepare($sql);
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

    $branch_address = $first_row['branch_address'] ?? '';
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
            margin: 0;
            padding: 15px;
            font-size: 12px;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }

        .employee-form {
            height: 100%;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .employee-form {
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
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
?>
    <?php
    $dob = $row['date_of_birth'] ?? '';
    $age = '';
    if (!empty($dob)) {
        $dobDate = DateTime::createFromFormat('d-M-y', $dob);
        if ($dobDate) {
            $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);
            if ($referenceDate) {
                $age = $dobDate->diff($referenceDate)->y;
            }
        }
    }
    ?>
    <div class="employee-form" style="<?= !$isLast ? 'page-break-after: always;' : '' ?>">
        <table>
            <tr>
                <td class="form-header" colspan="3">
                    Form BB<br>
                    The Kerala Shops & Commercial Establishments Rules, 1961 [SEE-RULE 10(1)A]<br>
                    Service Record to Employee
                </td>
            </tr>
            <tr>
                <th style="text-align: left;">Month/Year:</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(1) Name of the Establishment and Address:</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(2) Name of employee</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(3) Name of the Father/Husband</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(4) Age</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($age) ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(5) Full residential address</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(6) Sex</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(7) Date of entry into service</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            </tr>
            <tr>
                <th style="text-align: left;">(8) Category/Designation</th>
                <td colspan="2" style="text-align: left;"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Operations Assistant</th>
            </tr>
            <tr>
                <th style="text-align: left;">(9) Pay</th>
                <th style="text-align: left;">DA</th>
                <th style="text-align: left;">Other emoluments</th>
            </tr>
            <tr>
                <td style="text-align: left;">0</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <th style="text-align: left;">(10) Date of Retrenchment/Discharge/Dismissal/Retirement/Resignation</th>
                <td colspan="2" style="text-align: left;"></td>
            </tr>
            <tr>
                <th style="text-align: left;">(11) Signature of Employee</th>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th style="text-align: left;">(12) Signature of Employer</th>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th style="text-align: left;">(13) Counter Signature of Inspector</th>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: left;">
                    Note - Whenever there is change in designation and wages, 
                    the changes shall be noted in the columns 8 and 9 respectively 
                    with the date of such changes
                </td>
            </tr>
        </table>
    </div>
<?php endforeach; ?>
</body>
</html>
