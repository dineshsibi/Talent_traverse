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
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffffff;
        }

        .empty-row {
            height: 30px;
        }

        .label-cell {
            font-weight: bold;
        }

        * {
            font-family: "Times New Roman", Times, serif;
        }
        
        @media print {
            .page-break {
                page-break-before: always;
            }
            
            body {
                margin: 0;
                padding: 15px;
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
    ?>
        <div class="wage-slip-container">
            <table>
                <tr>
                    <th colspan="4"  style="text-align: center;">
                        FORM XIII <br>
                        The Kerala Minimum Wages Rules, 1958 [See rule 29 (2)] <br>
                        Wage Slip
                    </th>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: left;">Name of the Establishment:</th>
                    <td style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: left;">Place:</th>
                    <td style="text-align: left;"><?= htmlspecialchars(($first_row['location_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <td class="bold" rowspan="2">1</td>
                    <th colspan="2" style="text-align: left;">Employee Code</th>
                    <td style="text-align: left;"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                </tr>
                <tr>
                    <th colspan="2" style="text-align: left;">Name of the employee with Father's/Husband's Name:</th>
                    <td style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">2</td>
                    <th colspan="2" style="text-align: left;">Designation:</th>
                    <td style="text-align: left;"><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">3</td>
                    <th colspan="2" style="text-align: left;">Wage Period:</th>
                    <td style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                </tr>
                <tr>
                    <th rowspan="3">4</th>
                    <th colspan="3" style="text-align: left;">Rate of wages payable:</th>
                </tr>
                <tr>
                    <th>(a)</th>
                    <th>Basic Wages</th>
                    <td style="text-align: left;"><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>(b)</th>
                    <th>D.A</th>
                    <td style="text-align: left;"><?= htmlspecialchars($row['da'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">5</td>
                    <th colspan="2" style="text-align: left;">Total Attendance / Units of work done:</th>
                    <td style="text-align: left;"><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="bold">6</td>
                    <th colspan="2" style="text-align: left;">Over-time wages :</th>
                    <td style="text-align: left;"><?= number_format($row['over_time_allowance'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold">7</td>
                    <th colspan="2" style="text-align: left;">Gross Wages payable:</th>
                    <td style="text-align: left;"><?= number_format($row['gross_wages'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold">8</td>
                    <th colspan="2" style="text-align: left;">Total deductions:</th>
                    <td style="text-align: left;"><?= number_format($row['total_deduction'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <td class="bold">9</td>
                    <th colspan="2" style="text-align: left;">Net wages paid:</th>
                    <td style="text-align: left;"><?= number_format($row['net_pay'] ?? 0, 2) ?></td>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: left;">Pay-in charge</th>
                    <th style="text-align: right;"><br>Employee's Signature<br>Thumb Impression</th>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
</body>

</html>