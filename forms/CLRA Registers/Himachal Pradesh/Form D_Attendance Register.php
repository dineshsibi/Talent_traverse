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
$currentState = $_SESSION['current_state'] ?? 'Himachal Pradesh';
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
                <th class="form-title" colspan="41">
                    FORM D <br>
                    FORMAT OF ATTENDANCE REGISTER
                </th>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Name and address of Contractor </th>
                <td colspan="26" style="text-align: left;"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Nature and location of work </th>
                <td colspan="26" style="text-align: left;"><?= htmlspecialchars($nature . ' & ' . $currentLocation) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Name and address of establishment in/under which contract is carried on </th>
                <td colspan="26" style="text-align: left;"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Name and address of Principal Employer </th>
                <td colspan="26" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' & ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">LIN</th>
                <td colspan="26">-</td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">For the Year</th>
                <td colspan="26" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl. Number in Employee register</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Relay or set work</th>
                <th>Place of work</th>
                <th>Day-1</th>
                <th>Day-2</th>
                <th>Day-3</th>
                <th>Day-4</th>
                <th>Day-5</th>
                <th>Day-6</th>
                <th>Day-7</th>
                <th>Day-8</th>
                <th>Day-9</th>
                <th>Day-10</th>
                <th>Day-11</th>
                <th>Day-12</th>
                <th>Day-13</th>
                <th>Day-14</th>
                <th>Day-15</th>
                <th>Day-16</th>
                <th>Day-17</th>
                <th>Day-18</th>
                <th>Day-19</th>
                <th>Day-20</th>
                <th>Day-21</th>
                <th>Day-22</th>
                <th>Day-23</th>
                <th>Day-24</th>
                <th>Day-25</th>
                <th>Day-26</th>
                <th>Day-27</th>
                <th>Day-28</th>
                <th>Day-29</th>
                <th>Day-30</th>
                <th>Day-31</th>
                <th>IN</th>
                <th>OUT</th>
                <th>Summary No. of Days</th>
                <th>Remarks No. of hours</th>
                <th>**Signature of Register Keeper</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th colspan="31" style="text-align:center;">6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
                <th>11</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row):
                    // Calculate sum of numeric values in day_1 to day_31
                    $totalHours = 0;
                    for ($day = 1; $day <= 31; $day++) {
                        $dayValue = $row['day_' . $day] ?? '';
                        if (is_numeric($dayValue)) {
                            $totalHours += (float)$dayValue;
                        }
                    }
                ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($currentLocation) ?></td>
                        <td><?= htmlspecialchars($row['day_1'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_2'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_3'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_4'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_5'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_6'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_7'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_8'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_9'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_10'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_11'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_12'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_13'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_14'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_15'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_16'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_17'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_18'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_19'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_20'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_21'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_22'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_23'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_24'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_25'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_26'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_27'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_28'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_29'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_30'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['day_31'] ?? '') ?></td>
                        <td colspan="2"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
                        <td><?= $totalHours ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="25" class="no-data">No contractor data available for Himachal Pradesh</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>