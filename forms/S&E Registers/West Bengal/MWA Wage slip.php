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
$currentState = 'West Bengal';

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
            page-break-inside: avoid;
            /* Prevent table split */
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

        /* Ensure each employee slip starts on a new page */
        .page-break {
            page-break-before: always;
        }

        /* Avoid blank page after last employee */
        .page-break:last-child {
            page-break-before: avoid;
        }

        @media print {
            .page-break {
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

        $employee_name = safe($row['employee_name'] ?? '');
        $father_name = safe($row['father_name'] ?? '');

        $EPF = (float)($row['epf'] ?? 0);
        $VPF = (float)($row['vpf'] ?? 0);
        $ptax = (float)($row['ptax'] ?? 0);
        $it_tds = (float)($row['it_tds'] ?? 0);
        $contribution_pf = $EPF + $VPF;
        $ESI = (float)($row['esi'] ?? 0);
        $Total = (float)($row['total_deduction'] ?? 0);
        $other_deduction = $Total - ($EPF + $VPF + $ESI + $ptax + $it_tds);
    ?>
        <table>
            <tr>
                <td colspan="5" class="header">
                    FORM XV <br>
                    The West Bengal Minimum Wages Rules, 1951, [See rule 21 (i) (iv)] <br>
                    Wage Slip
                </td>
            </tr>
            <tr>
                <td class="subheader" colspan="2">Name of the Establishment:</td>
                <td colspan="3"><?= htmlspecialchars($client_name . ' , ' . ($row['branch_address'] ?? '')) ?></td>
            </tr>
            <tr>
                <td class="subheader" colspan="2">Place:</td>
                <td colspan="3"><?= htmlspecialchars(($row['location_name'] ?? '')) ?></td>
            </tr>
            <tr>
                <td class="bold">1</td>
                <th>Name of the employee with Father's/Husband's Name:</th>
                <td colspan="3"><?= htmlspecialchars($employee_name . ' , ' . $father_name) ?></td>
            </tr>
            <tr>
                <td class="bold">2</td>
                <th>Designation:</th>
                <td colspan="3"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="bold">3</td>
                <th>Wage Period:</th>
                <td colspan="3"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <td class="bold">4</td>
                <th colspan="4" style="text-align: left;">Rate of Wages payable:</th>
            </tr>
            <tr>
                <td></td>
                <th>(a) Basic :</th>
                <td colspan="3"><?= htmlspecialchars($row['fixed_basic'] ?? '') ?></td>
            </tr>
            <tr>
                <td></td>
                <th>(b) D.A :</th>
                <td colspan="3"><?= htmlspecialchars($row['fixed_da'] ?? '') ?></td>
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
                <td class="bold">8</td>
                <th colspan="4">Total deductions:</th>
            </tr>
            <tr>
                <th class="bold">(1)</th>
                <th>PF</th>
                <td colspan="3"><?= htmlspecialchars($contribution_pf) ?></td>
            </tr>
            <tr>
                <th class="bold">(2)</th>
                <th>PT</th>
                <td colspan="3"><?= htmlspecialchars($row['ptax'] ?? '') ?></td>
            </tr>
            <tr>
                <th class="bold">(3)</th>
                <th>ESI</th>
                <td colspan="3"><?= htmlspecialchars($row['esi'] ?? '') ?></td>
            </tr>
            <tr>
                <th class="bold">(4)</th>
                <th>IT</th>
                <td colspan="3"><?= htmlspecialchars($row['it_tds'] ?? '') ?></td>
            </tr>
            <tr>
                <th class="bold">(5)</th>
                <th>Other Deduction</th>
                <td colspan="3"><?= htmlspecialchars($other_deduction) ?></td>
            </tr>
            <tr>
                <td></td>
                <th>Total Deduction</th>
                <td colspan="3"><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="bold">9</td>
                <th>Net wages paid:</th>
                <td colspan="3"><?= number_format($row['net_pay'] ?? 0, 2) ?></td>
            </tr>
            <tr>
                <th colspan="2"><br>Pay-in charge</th>
                <th colspan="3" style="text-align: right;"><br>Employee's Signature<br>Thumb Impression</th>
            </tr>
        </table>
        <?php if ($currentEmployee < $totalEmployees): ?>
            <div class="page-break"></div>
        <?php endif; ?>

    <?php endforeach; ?>
</body>

</html>