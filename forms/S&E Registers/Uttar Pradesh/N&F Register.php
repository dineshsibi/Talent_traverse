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
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Uttar Pradesh';

try {
    // ✅ Fetch branch address from input table (correct by location_code)
    $branchQuery = "SELECT DISTINCT branch_address 
                    FROM input 
                    WHERE client_name = :client_name 
                    AND location_code = :location_code 
                    LIMIT 1";
    $branchStmt = $pdo->prepare($branchQuery);
    $branchStmt->bindValue(':client_name', $filters['client_name']);
    $branchStmt->bindValue(':location_code', $currentLocation);
    $branchStmt->execute();
    $branchResult = $branchStmt->fetch(PDO::FETCH_ASSOC);
    $branch_address = $branchResult['branch_address'] ?? '';

    // Fetch nfh holidays
    $sql = "SELECT * FROM nfh 
            WHERE client_name = :client_name
            AND state LIKE :state
            AND location_code = :location_code";

    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }

    $stmt = $pdo->prepare($sql);
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

    $location_name = $first_row['location_name'] ?? '';

    // --- Group holidays by month-year ---
    $grouped = [];
    foreach ($stateData as $row) {
        $key = $row['month'] . '-' . $row['year'];
        $grouped[$key][] = $row;
    }

    // --- Pre-calc employee counts ---
    $counts = [];
    foreach ($grouped as $key => $rows) {
        [$m, $y] = explode('-', $key);

        // Total employees
        $empQuery = "SELECT COUNT(DISTINCT employee_code) as total_employees 
                     FROM input 
                     WHERE client_name = :client_name 
                     AND location_code = :location_code 
                     AND month = :month 
                     AND year = :year";
        $empStmt = $pdo->prepare($empQuery);
        $empStmt->bindValue(':client_name', $filters['client_name']);
        $empStmt->bindValue(':location_code', $currentLocation);
        $empStmt->bindValue(':month', $m);
        $empStmt->bindValue(':year', $y);
        $empStmt->execute();
        $empResult = $empStmt->fetch(PDO::FETCH_ASSOC);
        $totalEmployees = $empResult['total_employees'] ?? 0;

        // NH employees
        $nhQuery = "SELECT COUNT(DISTINCT employee_code) as nh_employees 
                    FROM input 
                    WHERE client_name = :client_name 
                    AND location_code = :location_code 
                    AND month = :month 
                    AND year = :year 
                    AND (day_1 = 'NH' OR day_2 = 'NH' OR day_3 = 'NH' OR day_4 = 'NH' OR day_5 = 'NH' 
                         OR day_6 = 'NH' OR day_7 = 'NH' OR day_8 = 'NH' OR day_9 = 'NH' OR day_10 = 'NH'
                         OR day_11 = 'NH' OR day_12 = 'NH' OR day_13 = 'NH' OR day_14 = 'NH' OR day_15 = 'NH'
                         OR day_16 = 'NH' OR day_17 = 'NH' OR day_18 = 'NH' OR day_19 = 'NH' OR day_20 = 'NH'
                         OR day_21 = 'NH' OR day_22 = 'NH' OR day_23 = 'NH' OR day_24 = 'NH' OR day_25 = 'NH'
                         OR day_26 = 'NH' OR day_27 = 'NH' OR day_28 = 'NH' OR day_29 = 'NH' OR day_30 = 'NH'
                         OR day_31 = 'NH')";
        $nhStmt = $pdo->prepare($nhQuery);
        $nhStmt->bindValue(':client_name', $filters['client_name']);
        $nhStmt->bindValue(':location_code', $currentLocation);
        $nhStmt->bindValue(':month', $m);
        $nhStmt->bindValue(':year', $y);
        $nhStmt->execute();
        $nhResult = $nhStmt->fetch(PDO::FETCH_ASSOC);
        $nhEmployees = $nhResult['nh_employees'] ?? 0;

        // Employees without NH
        $notNhQuery = "SELECT COUNT(DISTINCT employee_code) as not_nh_employees 
                       FROM input 
                       WHERE client_name = :client_name 
                       AND location_code = :location_code 
                       AND month = :month 
                       AND year = :year 
                       AND employee_code NOT IN (
                           SELECT DISTINCT employee_code 
                           FROM input 
                           WHERE client_name = :client_name2 
                           AND location_code = :location_code2 
                           AND month = :month2 
                           AND year = :year2 
                           AND (day_1 = 'NH' OR day_2 = 'NH' OR day_3 = 'NH' OR day_4 = 'NH' OR day_5 = 'NH' 
                                OR day_6 = 'NH' OR day_7 = 'NH' OR day_8 = 'NH' OR day_9 = 'NH' OR day_10 = 'NH'
                                OR day_11 = 'NH' OR day_12 = 'NH' OR day_13 = 'NH' OR day_14 = 'NH' OR day_15 = 'NH'
                                OR day_16 = 'NH' OR day_17 = 'NH' OR day_18 = 'NH' OR day_19 = 'NH' OR day_20 = 'NH'
                                OR day_21 = 'NH' OR day_22 = 'NH' OR day_23 = 'NH' OR day_24 = 'NH' OR day_25 = 'NH'
                                OR day_26 = 'NH' OR day_27 = 'NH' OR day_28 = 'NH' OR day_29 = 'NH' OR day_30 = 'NH'
                                OR day_31 = 'NH')
                       )";
        $notNhStmt = $pdo->prepare($notNhQuery);
        $notNhStmt->bindValue(':client_name', $filters['client_name']);
        $notNhStmt->bindValue(':location_code', $currentLocation);
        $notNhStmt->bindValue(':month', $m);
        $notNhStmt->bindValue(':year', $y);
        $notNhStmt->bindValue(':client_name2', $filters['client_name']);
        $notNhStmt->bindValue(':location_code2', $currentLocation);
        $notNhStmt->bindValue(':month2', $m);
        $notNhStmt->bindValue(':year2', $y);
        $notNhStmt->execute();
        $notNhResult = $notNhStmt->fetch(PDO::FETCH_ASSOC);
        $notNhEmployees = $notNhResult['not_nh_employees'] ?? 0;

        $counts[$key] = [
            'total' => $totalEmployees,
            'nh' => $nhEmployees,
            'not_nh' => $notNhEmployees,
        ];
    }

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
        body { font-family: 'Times New Roman', Times, serif; margin: 20px; }
        .form-header { text-align: center; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #ffffff; }
    </style>
</head>
<body>
<table>
    <tr>
        <td colspan="12" class="form-header">
            Form I <br>
            The Uttar Pradesh Industrial Establishments (National Holidays) Act, 1961 and Rules, 1965 [See Rule 5] <br>
            Register of National Holidays
        </td>
    </tr>
    <tr>
        <th colspan="12">Register to be maintained regularly by the employer in the following manner</th>
    </tr>
    <tr>
        <th colspan="6" style="text-align: left;">Name and address of the establishment</th>
        <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
    </tr>
    <tr>
        <th colspan="6" style="text-align: left;">Month</th>
        <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
    </tr>
    <tr>
        <th colspan="12" style="text-align: center;">Total No. of employees</th>
    </tr>
    <tr>
        <th>Serial No.</th>
        <th>Name of the National Holiday with date</th>
        <th>Total no. of employees</th>
        <th>On leave on the National Holiday</th>
        <th>On lay off on the National Holiday</th>
        <th>Who availed the National Holiday</th>
        <th>Who were called for duty on the National Holiday by the employers</th>
        <th>Who actually attended duty on the National Holiday</th>
        <th>No. of employees who opted for twice the average wages for work on the National Holiday</th>
        <th>No. of employees who opted for single average wage for the National Holiday together with a substituted Holiday</th>
        <th>Date of substituted holiday</th>
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
    </tr>
    <tbody>
    <?php if (!empty($stateData)): ?>
        <?php $i = 1; ?>
        <?php foreach ($grouped as $key => $rows): ?>
            <?php $rowspan = count($rows); ?>
            <?php $first = true; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['description'] ?? '') . ' & ' . ($row['holiday_date'] ?? '') ?></td>

                    <?php if ($first): ?>
                        <td rowspan="<?= $rowspan ?>"><?= $counts[$key]['total'] ?></td>
                        <td rowspan="<?= $rowspan ?>"><?= $counts[$key]['nh'] ?></td>
                        <td>NIL</td>
                        <td rowspan="<?= $rowspan ?>"><?= $counts[$key]['nh'] ?></td>
                        <td rowspan="<?= $rowspan ?>"><?= $counts[$key]['not_nh'] ?></td>
                        <td rowspan="<?= $rowspan ?>"><?= $counts[$key]['not_nh'] ?></td>
                        <td rowspan="<?= $rowspan ?>"><?= $counts[$key]['not_nh'] ?></td>
                        <?php $first = false; ?>
                    <?php else: ?>
                        <td>NIL</td>
                    <?php endif; ?>

                    <td>NIL</td>
                    <td>NIL</td>
                    <td></td> <!-- Remarks per row -->
                </tr>
            <?php endforeach; ?>
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