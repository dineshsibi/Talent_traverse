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
$currentState = 'Telangana';

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

        /* Force each wage slip to be exactly one page */
        .wage-slip-page {
            height: 100vh;
            display: block;
            position: relative;
        }

        /* CSS-only solution */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .wage-slip-page {
                page-break-inside: avoid;
                /* never split a slip */
                display: block;
            }

            /* Every slip after the first starts on a new page */
            .wage-slip-page+.wage-slip-page {
                page-break-before: always;
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
        $EPF = (float)($row['epf'] ?? 0);
        $VPF = (float)($row['vpf'] ?? 0);
        $contribution_pf = $EPF + $VPF;
        $ESI = (float)($row['esi'] ?? 0);
        $Total = (float)($row['total_deduction'] ?? 0);
        $other_deduction = $Total - ($EPF + $VPF + $ESI);
    ?>
        <div class="wage-slip-page">
            <table>
                <tr>
                    <td colspan="5" class="header">
                        Form XI <br>
                        The Andhra Pradesh Minimum Wages Rules, 1960 [Rule 30 (2) (1)] <br>
                        Wage Slips
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
                    <td class="subheader" colspan="2">Month & Year</td>
                    <td colspan="3"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                </tr>
                <tr>
                    <td class="bold">1</td>
                    <th>Employee Code</th>
                    <td colspan="3"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">2</td>
                    <th>Name of the employee</th>
                    <td colspan="3"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">3</td>
                    <th>Designation with token No</th>
                    <td colspan="3"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">4</td>
                    <th>No. of days worked/units of work done</th>
                    <td colspan="3"><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">5</td>
                    <th>Overtime earned</th>
                    <td colspan="3"><?= number_format($row['over_time_allowance'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold">6</td>
                    <th>Gross wages earned</th>
                    <td colspan="3"><?= number_format($row['gross_wages'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold" rowspan="6">7</td>
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

                    <th class="indent">(b)Total deductions:</th>
                    <td><?= number_format($row['total_deduction'] ?? 0, 2) ?></td>
                    <td colspan="2">0</td>
                </tr>
                <tr>
                    <td class="bold">8</td>
                    <th>Net wages payable</th>
                    <td colspan="3"><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">9</td>
                    <th>Date of issue of wage slip</th>
                    <td colspan="3"><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">10</td>
                    <th>Signature of the pay in charge</th>
                    <td colspan="3"></td>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
</body>

</html>