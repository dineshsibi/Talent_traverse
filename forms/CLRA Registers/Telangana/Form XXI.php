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
$currentState = $_SESSION['current_state'] ?? 'Telangana';
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
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
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
            <td class="form-title" colspan="12">FORM XXI<br>
                            (See rule 78(2)(d))<br>
                            Register of Fines
            </td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name and address of Contractor</th> 
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Nature and location of work</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name and address of establishment in/under which contract is carried on</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td> 
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name and address of Principal Employer</th> 
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Month Year</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl. No</th>
                <th>Name of workman</th>
                <th>Father's/Husband's name</th>
                <th>Designation/Nature of Employment</th>
                <th>Act/Omission for which fine imposed</th>
                <th>Date of offence</th>
                <th>Whether workman showed cause against fine</th>
                <th>Name of person in whose presence employee's explanation was heard</th>
                <th>Wage periods and wages payable</th>
                <th>Amount of fine imposed</th>
                <th>Date on which fine realised</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['act_omission_for_which_fine_is_imposed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date_of_offences'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['whether_workman_showed_cause_against_fine'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['name_of_person_in_whose_presence_employees_explanation_was_heard'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['wage_period_and_wages_payable'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['amount_of_fine_imposed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date_on_which_fine_realised'] ?? '') ?></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="19" style="text-align:center;">No data available for Telangana</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>