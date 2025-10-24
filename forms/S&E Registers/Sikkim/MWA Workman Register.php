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
$pdo = require($configPath); // This gets the PDO connection from config.php

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Sikkim'; // Hardcoded for this state template

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



    $branch_address = $first_row['branch_address'] ?? '';
    $employer_name = $first_row['employer_name'] ?? '';
    $employer_address = $first_row['employer_address'] ?? '';
    $nature_of_business = safe($first_row['nature_of_business'] ?? 'Not specified');

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
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffffff;
        }
        .empty-row {
            height: 30px;
        }
        .label-cell {
            font-weight: bold;
        }
         * {
            font-family: "Times New Roman", Times, serif;
        }
    </style>
</head>
<body>
    <table>
        <thead>
        <tr>
            <th colspan="11" class="form-header">
                FORM XIV <br>
                The Sikkim Minimum Wages Rules, 2005, (See rule 24(6)) <br>
                REGISTER OF EMPLOYEES
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name and address of the Factory/ Establishment:</th>
            <td colspan="7"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of Employer:</th>
            <td colspan="7"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month & Year:</th>
            <td colspan="7"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>Serial No.</th>
            <th>Employee Code</th>
            <th>Name and surname of employee</th>
            <th>Age and sex</th>
            <th>Father’s/<br>Husband’s name</th>
            <th>Nature of employment designation</th>
            <th>Permanent house address of employee (village, taluk, district)</th>
            <th>Date of commencement of employment</th>
            <th>Date of termination of or leaving employment</th>
            <th>Signature of thumb-impression of employee</th>
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
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($age) ?> , <?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
            <td></td>
            <td></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11" class="no-data" style="text-align: center;">No data available for Sikkim</td>
            </tr>
        <?php endif; ?>
    </tbody>
    </table>
</body>
</html>