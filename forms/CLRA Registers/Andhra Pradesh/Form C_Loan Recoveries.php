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
$currentState = $_SESSION['current_state'] ?? 'Andhra Pradesh';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
        AND state LIKE :state
        AND location_code = :location_code
        AND month = :month 
        AND year = :year";

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
    $employee_name = $first_row['employee_name'] ?? '';
    $father_name = $first_row['father_name'] ?? '';
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
            text-align: center;
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
    <table>
        <thead>
            <tr>
                <th class="form-title" colspan="14">
                    FORM C <br>
                    FORMAT OF REGISTER OF LOAN/ RECOVERIES
                </th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name and address of Contractor </th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Nature and location of work </th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name and address of establishment in/under which contract is carried on </th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">Name and address of Principal Employer </th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">LIN</th>
                <td colspan="10">-</td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: left;">For the Month Year</th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl. No</th>
                <th>Sl. Number In Employee register</th>
                <th>Name</th>
                <th>Recovery Type (Damage/loss/fine/advance/loans)</th>
                <th>Particulars</th>
                <th>Date of damage/Loss*</th>
                <th>Amount</th>
                <th>Whether show cause<br>issued*</th>
                <th>Explanation heard in<br> presence of*</th>
                <th>Number of Instalments</th>
                <th>First Month/Year</th>
                <th>Last Month/Year</th>
                <th>Date of Complete Recovery</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
                <th>11</th>
                <th>12</th>
                <th>13</th>
                <th>14</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td>Damage</td>
                        <td><?= htmlspecialchars($row['particulars_of_damage_loss'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_damage'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['amount_of_deduction_imposed'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['whether_worker_showed_cause_against_deduction'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['name_of_person_in_whose_presence_employees_explanation_was_heard'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['no_of_installment'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['first_installment_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['last_installment_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['last_installment_date'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="14" style="text-align:center;">No data available for Andhra Pradesh</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="4" style="text-align:left">Date</th>
                <th colspan="10" style="text-align:right">Authorised Signatory</th>
            </tr>
        </tbody>
    </table>
</body>

</html>