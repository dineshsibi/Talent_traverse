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
$currentState = $_SESSION['current_state'] ?? 'Tamilnadu';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
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

        .subtitle {
            text-align: center;
            font-size: smaller;
            margin-bottom: 20px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 400px;
            vertical-align: top;
        }

        .info-content {
            display: inline-block;
            width: calc(100% - 410px);
        }

        .month-year {
            text-align: right;
            font-weight: bold;
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
                <th class="form-title" colspan="20">
                    FORM XXIX<br>
                    [See Rule 78 (1) (d)]<br>
                    Register Of Advances Deductions For Damages Or Loss And Fines
                </th>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Name and Address of the Establishment:</th>
                <td colspan="7" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                <th colspan="4" style="text-align: left;">Month & Year</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">Sl. No.</th>
                <th rowspan="2">Name of the Workman</th>
                <th rowspan="2">Father’s/ Husband’s Name</th>
                <th rowspan="2">Employee Number</th>
                <th rowspan="2">Designation</th>
                <th colspan="4" style="text-align: center;">Advance Paid</th>
                <th colspan="5" style="text-align: center;">Damages</th>
                <th colspan="4" style="text-align: center;">Fines</th>
                <th rowspan="2">Signature or Thumb Impression of the Workman</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Date of Payment</th>
                <th>Amount Paid</th>
                <th>Number of Instalments to be recovered</th>
                <th>Date on which recovery completed</th>
                <th>Damage or/ loss caused</th>
                <th>Date of Show Cause Notice</th>
                <th>Total amount of deduction imposed</th>
                <th>Number of instalments to be recovered</th>
                <th>Date on which deduction completed</th>
                <th>Act or Omission</th>
                <th>Date of Show Cause Notice</th>
                <th>Amount of fine imposed</th>
                <th>Date on which fine recovery completed</th>
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
                <th>15</th>
                <th>16</th>
                <th>17</th>
                <th>18</th>
                <th>19</th>
                <th>20</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['payment_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['advance_loan'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['no_of_installments_by_which_advance_to_be_repaid'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_on_which_last_installment_was_paid'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_on_which_last_installment_was_paid'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_damage'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['amount_of_deduction_imposed'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['no_of_installment'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['last_installment_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['act_omission_for_which_fine_is_imposed'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_offences'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['amount_of_fine_imposed'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_on_which_fine_realised'] ?? '') ?></td>
                        <td></td>
                        <td></td>
                    </tr>

                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="20" style="text-align:center;">No data available for Tamilnadu</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="20" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
            </tr>
        </tbody>
    </table>
</body>

</html>