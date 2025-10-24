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
$currentState = 'Uttar Pradesh';

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
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 0;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .header {
            font-weight: bold;
            text-align: center;
        }

        .subheader {
            font-weight: bold;
        }

        .bold {
            font-weight: bold;
            text-align: left;
        }

        .indent {
            padding-left: 20px;
        }

        .wage-slip-container {
            page-break-after: always;
            height: 100vh;
            /* Ensure each form takes full page */
        }

        .wage-slip-container:last-child {
            page-break-after: auto;
            /* No break after last form */
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;
        $EPF = (float)($row['epf'] ?? 0);
        $VPF = (float)($row['vpf'] ?? 0);
        $contribution_pf = $EPF + $VPF;
        $ESI = (float)($row['esi'] ?? 0);
        $Total = (float)($row['total_deduction'] ?? 0);
        $other_deduction = $Total - ($EPF + $VPF + $ESI);
    ?>
        <table>
            <tr>
                <td colspan="5" class="header">
                    Form IX <br>
                    The Uttar Pradesh Minimum Wages Rules, 1952, [Rule 26 (2)] <br>
                    Wages Slip
                </td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Name of the Establishment:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Place:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars(($row['location_name'] ?? '')) ?></td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Month & Year:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <td class="bold">1</td>
                <th style="text-align: left;">Employee Code</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="bold">1</td>
                <th style="text-align: left;">Name of the employee with Father's/Husband's Name:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?> , <?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="bold">2</td>
                <th style="text-align: left;">Designation:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="bold">3</td>
                <th style="text-align: left;">Wage Period:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <td class="bold">4</td>
                <th colspan="4" style="text-align: left;" style="text-align: left;">Rate of Wages payable:</th>
            </tr>
            <tr>
                <td></td>
                <th style="text-align: left;">(a) Basic wages :</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['fixed_basic'] ?? '') ?></td>
            </tr>
            <tr>
                <td></td>
                <th style="text-align: left;">(b) D.A:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['fixed_da'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="bold">5</td>
                <th style="text-align: left;">Total Attendance/Units of work done:</th>
                <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['total_worked_days'] ?? '') ?> days</td>
            </tr>
            <tr>
                <td class="bold">6</td>
                <th style="text-align: left;">Over-time wages :</th>
                <td colspan="3" style="text-align: left;"><?= number_format($row['over_time_allowance'] ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="bold">7</td>
                <th style="text-align: left;">Gross Wages payable:</th>
                <td colspan="3" style="text-align: left;"><?= number_format($row['gross_wages'] ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="bold">8</td>
                <th style="text-align: left;">Total deductions:</th>
                <td colspan="3" style="text-align: left;"><?= number_format($row['total_deduction'] ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td class="bold">9</td>
                <th style="text-align: left;">Net wages paid:</th>
                <td colspan="3" style="text-align: left;"><?= number_format($row['net_pay'] ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th colspan="2"><br>Pay-in charge</th>
                <th colspan="3" style="text-align: right;"><br>Employee's Signature<br>Thumb Impression</th>
            </tr>
        </table>
    <?php endforeach; ?>
</body>

</html>