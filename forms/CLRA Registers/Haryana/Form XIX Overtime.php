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
$currentState = $_SESSION['current_state'] ?? 'Haryana';
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
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            font-size: smaller;
            margin-bottom: 20px;
        }

        .header-info {
            margin-bottom: 20px;
        }

        .header-info div {
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Force equal width distribution */
            word-wrap: break-word;
            /* Prevent text overflow */
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 12px;
            vertical-align: middle;
        }

       

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .month-input {
            display: inline-block;
            margin-left: 10px;
        }

        .signature-section {
            margin-top: 30px;
            text-align: right;
            width: 100%;
        }

        input[type="text"] {
            width: 100%;
            box-sizing: border-box;
            border: none;
            background: transparent;
        }

        .column-header {
            white-space: nowrap;
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
                <th class="form-title" colspan="12">FORM XIX<br>
                    See Rule 78(1)(a)(iii)<br>
                    Register of Overtime
                </th>
            </tr>
            <tr>
                <th colspan="6" >Name and address of Contractor</th>
                <td colspan="6"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th colspan="6">Nature and location of work</th>
                <td colspan="6"><?= htmlspecialchars($nature . ' & ' . $currentLocation) ?></td>
            </tr>
            <tr>
                <th colspan="6">Name and address of establishment in/under which contract is carried on</th>
                <td colspan="6"><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
            </tr>
            <tr>
                <th colspan="6">Name and address of Principal Employer</th>
                <td colspan="6"><?= htmlspecialchars($principal_employer . ' & ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="6">For the Month of</th>
                <td colspan="6"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl. No</th>
                <th>Name of workman</th>
                <th>Father's/ Husband's name</th>
                <th>Sex</th>
                <th>Designation/ nature of employment</th>
                <th>Dates on which overtime worked</th>
                <th>Total overtime worked or production in case of piece-rated</th>
                <th>Normal rates of wages</th>
                <th>Overtime rate of wages</th>
                <th>Overtime earnings</th>
                <th>Date on which overtime wages paid</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $overtimeDays = [];
                    for ($day = 1; $day <= 31; $day++) {
                        $dayColumn = 'ot_day_' . $day;
                        if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                            $hours = (float)$row[$dayColumn];
                            if ($hours > 0) {
                                $overtimeDays[] = $day;
                            }
                        }
                    }
                    $overtimeDaysStr = !empty($overtimeDays) ? implode(',', $overtimeDays) : '-';
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($overtimeDaysStr) ?></td>
                        <td>NIL</td>
                        <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['overtime_rate_of_wages'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['overtime_earnings'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_on_ot_payment'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12" class="no-data">No contractor data available for Haryana</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th colspan="6" style="text-align: left;">Date</th>
                <th colspan="6" style="text-align: left;">Authorised Signatory</th>
            </tr>
        </tbody>
    </table>

</body>

</html>