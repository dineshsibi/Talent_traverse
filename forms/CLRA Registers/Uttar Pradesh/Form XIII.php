<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__.'/../../../includes/config.php';
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
$currentState = $_SESSION['current_state'] ?? 'Uttar Pradesh';
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
        .month-year {
            text-align: right;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffff;
        }
        *{
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
     <table>
        <thead>
            <tr>
            <td class="form-title" colspan="12">FORM XIII<br>
                                (See Rule 75)<br>
                        Register of Workmen Employed by Contractor
            </td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name and address of Contractor </th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Nature and location of work </th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name and address of establishment in/under which contract is carried on </th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name and address of Principal Employer </th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Month & Year</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl No</th>
                <th>Name and surname of workman</th>
                <th>Age and Sex</th>
                <th>Father's / Husband's name</th>
                <th>Nature of Employment/Designation</th>
                <th>Permanent House Address of workman</th>
                <th>Local Address</th>
                <th>Date of commencement of employment</th>
                <th>Signature of thumb impression of workman</th>
                <th>Date of termination of employment</th>
                <th>Reasons for termination</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
             <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                $dob = $row['date_of_birth'] ?? '';
                $age = '';

                if (!empty($dob)) {
                    // Create DOB from your DB format (d-M-y, e.g., 20-Jan-04)
                    $dobDate = DateTime::createFromFormat('d-M-y', $dob);

                    if ($dobDate) {
                        // ✅ Get last day of the selected month & year
                        $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year); 
                        $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);

                        if ($referenceDate) {
                            $age = $dobDate->diff($referenceDate)->y;
                        }
                    }
                }
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($age) .' & '. ($row['gender'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                <td>Nil</td>
                <td>Nil</td>
                <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                <td></td>
                <td><?= htmlspecialchars($row['date_of_resign'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['reason_for_exit'] ?? '') ?></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="12" style="text-align:center;">No data available for Uttar Pradesh</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>