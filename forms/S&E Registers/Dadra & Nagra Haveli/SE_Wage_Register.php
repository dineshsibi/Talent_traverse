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
$pdo = require($configPath); // This gets the PDO connection from config.php

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Dadra and Nagra Haveli'; // Hardcoded for this state template

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM input 
            WHERE client_name = :client_name
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
    $branch_address = safe($first_row['branch_address'] ?? '');
    $employer_name = safe($first_row['employer_name'] ?? '');
    $employer_address = safe($first_row['employer_address'] ?? '');
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
            font-family: "Times New Roman", Times, serif;
            margin: 15px;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            background-color: white;
            font-size: 12px;
        }

        th {
            font-weight: bold;
        }

        .main-heading {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 8px;
        }

        .input-field {
            display: block;
            min-height: 18px;
            border-bottom: 1px dotted #999;
            margin-top: 3px;
        }

        .section-heading {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <th colspan="19" class="main-heading">
                FORM XXIII<br>
                The Goa, Daman and Dieu Shops and Establishments Act, 1973<br>
                The Dadra and Nagar Haveli Shops and Establishments Rules, 2000, [See Rule 31(2)]<br>
                Register of wages
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left;">Name of Establishment:</th>
            <td colspan="13" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="6">Name of Employer and address:</th>
            <td colspan="13" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' . $employer_address ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="6">Registration No:</th>
            <td colspan="13" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="6">Wage Period:</th>
            <td colspan="13" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year ?? '') ?></td>
        </tr>

        <!-- Column headers -->
        <tr>
            <th rowspan="2">Sr. No.</th>
            <th rowspan="2">Name of Employee</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Father's/ Husband's name</th>
            <th rowspan="2">Designation</th>
            <th colspan="3">Minimum rate of wages payable</th>
            <th colspan="4">Wages payable</th>
            <th colspan="4">Dedctions</th>
            <th rowspan="2">Net amount of wages paid</th>
            <th rowspan="2">Signature of thumb impression of Employee</th>
            <th rowspan="2">Date of payment</th>
        </tr>
        <tr>
            <th>Basic</th>
            <th>Dearness Allowance</th>
            <th>Basic Salary on Wage</th>
            <th>Dearness Allowances</th>
            <th>Other Allowances</th>
            <th>Overtime Wages</th>
            <th>Gross Wages payable</th>
            <th>Advance</th>
            <th>Provident Fund contributions</th>
            <th>Other authorised deductions</th>
            <th>Total deductions</th>
        </tr>

        <!-- Column numbers -->
        <tr class="section-heading">
            <td>1</td>
            <td>2</td>
            <td>3</td>
            <td>4</td>
            <td>5</td>
            <td>6</td>
            <td>7</td>
            <td>8</td>
            <td>9</td>
            <td>10</td>
            <td>11</td>
            <td>12</td>
            <td>13</td>
            <td>14</td>
            <td>15</td>
            <td>16</td>
            <td>17</td>
            <td>18</td>
            <td>19</td>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $gross = (float)($row['gross_wages'] ?? 0);
                    $da = (float)($row['da'] ?? 0);
                    $overtime = (float)($row['over_time_allowance'] ?? 0);
                    $other_allowances = $gross-($da+$overtime);

                    $epf = (float)($row['epf'] ?? 0);
                    $vpf = (float)($row['vpf'] ?? 0);
                    $provident =$epf+$vpf;

                    $total = (float)($row['total_deduction'] ?? 0);
                    $advance = (float)($row['advance_recovery'] ?? 0);
                    $other = $total-($advance+$epf+$vpf);


                    ?>
                    <tr>
                        <td> <?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td colspan="3">As Per Act</td>
                        <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
                        <td><?= htmlspecialchars($other_allowances ?? '') ?></td>
                        <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['advance_recovery'] ?? '') ?></td>
                        <td><?= htmlspecialchars($provident ?? '') ?></td>
                        <td><?= htmlspecialchars($other ?? '') ?></td>
                        <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="16" style="text-align:center;">No data available for Dadra and Nagra Haveli</td>
                </tr>
            <?php endif; ?>
            <!-- Signature row -->
            <tr>
                <th colspan="19" style="text-align: right;">Signature of Employer</th>
            </tr>
        </tbody>
    </table>
</body>

</html>