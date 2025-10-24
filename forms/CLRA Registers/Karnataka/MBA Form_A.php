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
$currentState = $_SESSION['current_state'] ?? 'Karnataka';
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
    $allData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter only employees with ML in any day column
    $stateData = [];
    foreach ($allData as $row) {
        $hasML = false;
        $mlCount = 0;

        // Check day_1 to day_31 columns for ML
        for ($i = 1; $i <= 31; $i++) {
            $dayCol = 'day_' . $i;
            if (isset($row[$dayCol]) && $row[$dayCol] === 'ML') {
                $hasML = true;
                $mlCount++;
            }
        }

        if ($hasML) {
            $row['ml_days_count'] = $mlCount; // Store the count in the row
            $stateData[] = $row;
        }
    }

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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffff;
        }

        .section-title {
            font-weight: bold;
            margin-top: 15px;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }

        .employee-form {
            margin: 15px;
        }

        @media print {
            .employee-form {
                margin: 0;
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
    <div class="employee-form" style="<?= $isLast ? '' : 'page-break-after: always;' ?>">
        <table>
            <tr>
                <th class="form-title" colspan="5">
                    <center>FORM 'A' <br> 
                            [See Rule 3]<br>
                            Muster-roll</center>
                </th>
            </tr>
            <tr>
                <th>1</th>
                <th>Name and Address of the Establishment</th>
                <td colspan="3"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th>2</th>
                <th>Month & Year</th>
                <td colspan="3"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>3</th>
                <th>Serial No.</th>
                <td colspan="3"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <th>4</th>
                <th>Name of woman and her father's (or if married husband's) name</th>
                <td colspan="3"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th>5</th>
                <th>Date of appointment</th>
                <td colspan="3"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            </tr>
            <tr>
                <th>6</th>
                <th>Nature of work</th>
                <td colspan="3"><?= htmlspecialchars($nature) ?></td>
            </tr>
            <tr>
                <th rowspan="3">7</th>
                <th colspan="4">Dates with month and year in which she is employed, laid off and not re-employed</th>
            </tr>
            <tr>
                <th>Month</th>
                <th>No. of days employed</th>
                <th>No. of days laid off</th>
                <th>No. of days not employed</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                <td><?= htmlspecialchars($row['paid_days'] ?? '') ?></td>
                <td>0</td>
                <td><?= htmlspecialchars($row['ml_days_count'] ?? '0') ?></td>
            </tr>
            <tr>
                <th>8</th>
                <th>Date on which the woman gives notice under Section 6</th>
                <td colspan="3"><?= htmlspecialchars($row['notice_date_section6'] ?? '') ?></td>
            </tr>
            <tr>
                <th>9</th>
                <th>Date of discharge/dismissal, if any</th>
                <td colspan="3"><?= htmlspecialchars($row['discharge_dismissal_date'] ?? '') ?></td>
            </tr>
            <tr>
                <th>10</th>
                <th>Date of production of proof of pregnancy under Section 6</th>
                <td colspan="3"><?= htmlspecialchars($row['pregnancy_proof_date_section6'] ?? '') ?></td>
            </tr>
            <tr>
                <th>11</th>
                <th>Date of birth of child</th>
                <td colspan="3"><?= htmlspecialchars($row['child_birth_date'] ?? '') ?></td>
            </tr>
            <tr>
                <th>12</th>
                <th>Date of production of proof of delivery/miscarriage/Medical Termination of pregnancy/ tubectomy operation/ death</th>
                <td colspan="3"><?= htmlspecialchars($row['delivery_proof_details'] ?? '') ?></td>
            </tr>
            <tr>
                <th>13</th>
                <th>Date of production of proof of illness referred to in Section 10</th>
                <td colspan="3"><?= htmlspecialchars($row['illness_proof_date_section10'] ?? '') ?></td>
            </tr>
            <tr>
                <th>14</th>
                <th>Date with the amount of maternity benefit paid in advance of expected delivery</th>
                <td colspan="3"><?= htmlspecialchars($row['advance_maternity_benefit_details'] ?? '') ?></td>
            </tr>
            <tr>
                <th>15</th>
                <th>Date with the amount of subsequent payment of maternity benefit</th>
                <td colspan="3"><?= htmlspecialchars($row['subsequent_maternity_benefit_details'] ?? '') ?></td>
            </tr>
            <tr>
                <th>16</th>
                <th>Date with the amount of bonus, if paid under Section 8</th>
                <td colspan="3"><?= htmlspecialchars($row['bonus_payment_details_section8'] ?? '') ?></td>
            </tr>
            <tr>
                <th>17</th>
                <th>Date with the amount of wages paid on account of leave under Section 9</th>
                <td colspan="3"><?= htmlspecialchars($row['leave_wages_details_section9'] ?? '') ?></td>
            </tr>
            <tr>
                <th>18</th>
                <th>Date with the amount of wages paid on account of leave under Section 10 and period of leave granted</th>
                <td colspan="3"><?= htmlspecialchars($row['leave_wages_details_section10'] ?? '') ?></td>
            </tr>
            <tr>
                <th>19</th>
                <th>Name of the person nominated by the woman under Section 6</th>
                <td colspan="3"><?= htmlspecialchars($row['nominated_person_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th>20</th>
                <th>If the woman dies, the date of her death, the name of the person to whom maternity benefit and/or other amount was paid, the amount thereof and the date of payment</th>
                <td colspan="3"><?= htmlspecialchars($row['death_details'] ?? '') ?></td>
            </tr>
            <tr>
                <th>21</th>
                <th>If the woman dies and the child survives, the name of the person to whom the amount of maternity benefit was paid on behalf of the child and the period for which it was paid</th>
                <td colspan="3"><?= htmlspecialchars($row['child_survival_details'] ?? '') ?></td>
            </tr>
            <tr>
                <th>22</th>
                <th>Signature of the employer of the establishment authenticating the entries in the muster-roll</th>
                <td colspan="3"></td>
            </tr>
            <tr>
                <th>23</th>
                <th>Remarks column for the use of the Inspector</th>
                <td colspan="3"></td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>
</body>

</html>