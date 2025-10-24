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
$currentState = $_SESSION['current_state'] ?? 'Rajasthan';
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
        }

        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffff;
        }

        /* Default break for employee forms */
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
    $current = 0;

    foreach ($stateData as $row):
        $current++;
        $isLast = ($current === $totalEmployees);
    ?>
        <div class="employee-form" style="<?= $isLast ? '' : 'page-break-after: always;' ?>">
            <table>
                <tr>
                    <td class="form-title" colspan="3">Form X <br>
                        [See rule 75] <br>
                        Employment Card
                    </td>
                </tr>
                <tr>
                    <th colspan="2">Name and address of Contractor </th>
                    <td><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Nature and location of work </th>
                    <td><?= htmlspecialchars($nature . ' & ' . $currentLocation) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Name and address of establishment in/under which contract is carried on </th>
                    <td><?= htmlspecialchars($client_name . ' & ' . $address) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Name and address of Principal Employer </th>
                    <td><?= htmlspecialchars($principal_employer . ' & ' . $principal_employer_address) ?></td>
                </tr>
                <tr>
                    <th>1</th>
                    <th>Name of the Workman</th>
                    <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>2</th>
                    <th>Sl.No in the Register of Workman Employed</th>
                    <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>3</th>
                    <th>Nature of Employment / Designation</th>
                    <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>4</th>
                    <th>Date of Joining</th>
                    <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>5</th>
                    <th>Wage Rate (with particulars of unit, in case of piece work)</th>
                    <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>6</th>
                    <th>Wage Period</th>
                    <td><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                </tr>
                <tr>
                    <th>7</th>
                    <th>Tenure of Employment</th>
                    <td><?= htmlspecialchars(calculateTenure(
                            $row['date_of_joining'] ?? '',
                            $row['month'] ?? '',
                            $row['year'] ?? ''
                        )) ?></td>
                </tr>
                <tr>
                    <th>8</th>
                    <th>Remarks</th>
                    <td></td>
                </tr>
                <tr>
                    <th colspan="2">Date</th>
                    <th> Signature of the Contractor</th>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
</body>

</html>