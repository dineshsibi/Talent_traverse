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
$currentState = 'Andhra Pradesh';

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
        <div class="wage-slip-container">
            <table>
                <tr>
                    <td colspan="5" class="header">
                        Form XI<br>
                        The Andhra Pradesh Minimum Wages Rules, 1960 [Rule 30 (2) (1)]<br>
                        Wages Slip
                    </td>
                </tr>
                <tr>
                    <td class="subheader" colspan="2">Name of the Establishment:</td>
                    <td colspan="3"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
                </tr>
                <tr>
                    <td class="subheader" colspan="2">Place:</td>
                    <td colspan="3"><?= htmlspecialchars(($first_row['location_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <td class="bold">1</td>
                    <th>Employee Code</th>
                    <td colspan="3"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">2</td>
                    <th>Name of the employee with Father's/Husband's Name:</th>
                    <td colspan="3">
                        <?= htmlspecialchars($row['employee_name'] ?? '') ?> , <?= htmlspecialchars($row['father_name'] ?? '') ?>
                    </td>

                </tr>
                <tr>
                    <td class="bold">3</td>
                    <th>Designation:</th>
                    <td colspan="3"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">4</td>
                    <th>Wage Period:</th>
                    <td colspan="3"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                </tr>
                <tr>
                    <td class="bold">5</td>
                    <th>Total Attendance/Units of work done:</th>
                    <td colspan="3"><?= htmlspecialchars($row['total_worked_days'] ?? '') ?> days</td>
                </tr>
                <tr>
                    <td class="bold">6</td>
                    <th>Over-time wages :</th>
                    <td colspan="3"><?= number_format($row['over_time_allowance'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold">7</td>
                    <th>Gross Wages payable:</th>
                    <td colspan="3"><?= number_format($row['gross_wages'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold" rowspan="5">8</td>
                    <th colspan="4">Details of deduction</th>
                </tr>
                <tr>
                    <th class="indent">(a) Nature of deductions</th>
                    <th>Rs.</th>
                    <th colspan="2">Np.</th>
                </tr>
                <tr>
                    <th class="indent">1. PF</th>
                    <td><?= number_format($contribution_pf ?? 0, 2) ?></td>
                    <td colspan="2">0</td>
                </tr>
                <tr>
                    <th class="indent">2. ESI</th>
                    <td><?= number_format($ESI ?? 0, 2) ?></td>
                    <td colspan="2">0</td>
                </tr>
                <tr>
                    <th class="indent">3. Other Deductions</th>
                    <td><?= number_format($other_deduction ?? 0, 2) ?></td>
                    <td colspan="2">0</td>
                </tr>
                <tr>
                    <td class="bold">9</td>
                    <th>Total deductions:</th>
                    <td colspan="3"><?= number_format($row['total_deduction'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold">10</td>
                    <th>Net wages paid:</th>
                    <td colspan="3"><?= number_format($row['net_pay'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold">11</td>
                    <th>Date of issue of wage slip</th>
                    <td colspan="3"><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="2"><br>Pay-in charge</th>
                    <th colspan="3" style="text-align: right;"><br>Employee's Signature<br>Thumb Impression</th>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
</body>

</html>