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
    // Base SQL
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
        AND state LIKE :state
        AND location_code = :location_code";

    // Month/year condition
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }

    $stmt = $pdo->prepare($sql);

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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        * {
            font-family: 'Times New Roman', Times, serif;
        }

        /* Ensure each employee form is on a new page */
        .employee-form {
            margin: 15px;
        }

        .page-break {
            page-break-after: always;
        }
        
        /* Prevent page break after the last form */
        .employee-form:last-child {
            page-break-after: auto;
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    foreach ($stateData as $index => $row):

        // --- AGE CALCULATION ---
        $dob = $row['date_of_birth'] ?? '';
        $age = '';
        if (!empty($dob)) {
            $dobDate = DateTime::createFromFormat('d-M-y', $dob);
            if ($dobDate) {
                $lastDay = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);
                if ($referenceDate) {
                    $age = $dobDate->diff($referenceDate)->y;
                }
            }
        }
    ?>
        <div class="employee-form <?= ($index < $totalEmployees - 1) ? 'page-break' : '' ?>">
            <table>
                <thead>
                    <tr>
                        <td class="form-title" colspan="6">
                            FORM XV <br>
                            See Rule 77 <br>
                            Service Certificate
                        </td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Name and address of Contractor </th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Name and address of establishment in/under which contract is carried on </th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Nature and location of work </th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Name and address of the workman</th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Name and address of Principal Employer </th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Age or Date of Birth</th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($age) ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Identification Marks</th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['identification_marks'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Father's/Husband's Name</th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Month & Year</th>
                        <td colspan="3" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                    </tr>
                    <tr>
                        <th rowspan="2">Serial No</th>
                        <th colspan="2">Total Period for which employed</th>
                        <th rowspan="2">Nature of work done</th>
                        <th rowspan="2">Rate of wages (with particulars of unit in case of piece-work)</th>
                        <th rowspan="2">Remarks</th>
                    </tr>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                    </tr>
                    <tr>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_resign'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['rate_of_wage'] ?? '') ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</body>
</html>