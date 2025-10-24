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
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Maharashtra';

try {
    // Main employee data query
    $sql = "SELECT * FROM input 
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

    // Holiday data query
    $holidaySql = "SELECT holiday_date, description, leave_type 
                  FROM nfh 
                  WHERE location_code = :location_code
                  AND client_name = :client_name
                  ORDER BY holiday_date";
    
    $holidayStmt = $pdo->prepare($holidaySql);
    $holidayStmt->bindValue(':location_code', $currentLocation);
    $holidayStmt->bindValue(':client_name', $filters['client_name']);
    $holidayStmt->execute();
    $holidayData = $holidayStmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    // Process holiday data
    $allHolidays = [];
    $availedHolidays = [];
    $balanceHolidays = [];

    $selectedMonth = (int)($filters['month'] ?? date('n'));
    $selectedYear  = (int)($filters['year'] ?? date('Y'));

    foreach ($holidayData as $holiday) {
        $holidayDate = $holiday['holiday_date'];
        $allHolidays[] = $holidayDate;

        $holidayDt = DateTime::createFromFormat('d-M-y', $holidayDate);
        if ($holidayDt) {
            $holidayYear  = (int)$holidayDt->format('Y');
            $holidayMonth = (int)$holidayDt->format('n');

            // ✅ AVAILED: selected month + previous month (+ special case Jan previous year)
            if (
                ($holidayYear == $selectedYear && ($holidayMonth == $selectedMonth || $holidayMonth == $selectedMonth - 1))
                || ($selectedMonth == 1 && $holidayYear == $selectedYear - 1 && ($holidayMonth == 12 || $holidayMonth == 1))
            ) {
                $availedHolidays[] = $holidayDate;
            }

            // ✅ BALANCE: strictly future months (not including selected month)
            if ($holidayYear == $selectedYear && $holidayMonth > $selectedMonth) {
                $balanceHolidays[] = $holidayDate;
            }
        }
    }



    // Special Case: If selected month is January, include December of previous year in Availed
    if ($selectedMonth == 1) {
        $prevYear = $selectedYear - 1;
        foreach ($holidayData as $holiday) {
            $holidayDt = DateTime::createFromFormat('d-M-y', $holiday['holiday_date']);
            if ($holidayDt) {
                $holidayYear  = $holidayDt->format('Y');
                $holidayMonth = $holidayDt->format('n');
                if ($holidayYear == $prevYear && $holidayMonth == 12) {
                    $availedHolidays[] = $holiday['holiday_date'];
                }
            }
        }
    }


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
<?php 
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row): 
        $currentEmployee++;

        // Query to get PL (Paid Leave) data
        $leaveSql = "SELECT day_1, day_2, day_3, day_4, day_5, day_6, day_7, day_8, day_9, day_10,
                            day_11, day_12, day_13, day_14, day_15, day_16, day_17, day_18, day_19, day_20,
                            day_21, day_22, day_23, day_24, day_25, day_26, day_27, day_28, day_29, day_30, day_31
                    FROM input
                    WHERE employee_code = :emp_code 
                    AND month = :month 
                    AND year = :year";
        
        $leaveStmt = $pdo->prepare($leaveSql);
        $leaveStmt->bindValue(':emp_code', $row['employee_code']);
        $leaveStmt->bindValue(':month', $month);
        $leaveStmt->bindValue(':year', $year);
        $leaveStmt->execute();
        $leaveData = $leaveStmt->fetch(PDO::FETCH_ASSOC);

        // Count PL days
        $plCount = 0;
        
        if ($leaveData) {
            for ($day = 1; $day <= 31; $day++) {
                $dayKey = 'day_' . $day;
                if (isset($leaveData[$dayKey]) && $leaveData[$dayKey] == 'PL') {
                    $plCount++;
                }
            }
        } 
?> 

<table>
        <tr>
            <td class="form-header" colspan="14">
                Form - 'O'<br>
                LEAVE BOOK<br>
                The Maharashtra Shops and Establishments (Regulation of Employment and Conditions of Service) Rules, 2018. (See rule 19)
            </td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name and Address of the establishment :</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' .$branch_address)?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Employee Code</th>
            <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <th colspan="2" style="text-align: left;">Receipt of leave book </th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars(($row['month'] ?? '').' - '.($row['year'] ?? '')) ?></td>
            <th colspan="3" rowspan="2">(Signature & thumb impression of worker)</th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Name of the worker</th>
            <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <th colspan="2" style="text-align: left;">Name of the Employer :</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row['employer_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Description of the Department :</th>
            <td colspan="3" style="text-align: left;"><?= htmlspecialchars($row['department'] ?? '') ?></td>
            <th colspan="2" style="text-align: left;">Date of entry into service :</th>
            <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="14">(if applicable)</td>
        </tr>
        <tr>
            <th colspan="3">Accumulation of leave</th>
            <th colspan="2">Leave allowed</th>
            <th colspan="2">Payment for leave made on</th>
            <th colspan="3">Refusal of leave</th>
            <th colspan="4">Payment for leave on discharge of an worker quitting employment, if admissible</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
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
        </tr>
        <tr>
            <th>Leave due on</th>
            <th>Opening Balance</th>
            <th>No. of days</th>
            <th>From</th>
            <th>To</th>
            <th>1st Moiety</th>
            <th>2nd Moiety</th>
            <th>Application Date</th>
            <th>Date Of Refusal</th>
            <th>Reason for refusal</th>
            <th>Date of discharge</th>
            <th>Date and amount paid</th>
            <th>Signature or left hand thumb impression of worker</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="2"><?= htmlspecialchars($plCount) ?></td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
            <td></td>
        </tr>
        <!-- In your HTML, modify the FESTIVAL LEAVE section like this: -->
<tr>
    <th colspan="14" style="text-align: left;">DETAILS OF FESTIVAL LEAVE</th>
</tr>
<tr>
    <th colspan="2">Period</th>
    <th rowspan="2">Total Leave</th>
    <th rowspan="2">Availed Leave</th>
    <th rowspan="2">Balance Leave</th>
    <th colspan="2" rowspan="2">Payment made in lieu of Festival Leave, when called for work</th>
    <th colspan="7" rowspan="2">Remarks</th>
</tr>
<tr>
    <th>From</th>
    <th>To</th>
</tr>

<?php if (!empty($allHolidays)): ?>
    <?php $rowspan = count($allHolidays); ?>
    <?php foreach ($allHolidays as $index => $holiday): ?>
        <tr>
            <td colspan="2"><?= htmlspecialchars($holiday) ?></td>
            
            <?php if ($index === 0): ?>
                <td rowspan="<?= $rowspan ?>"><?= count($allHolidays) ?></td>
                 <th rowspan="<?= max(count($allHolidays), 1) ?>"><?= count($availedHolidays) ?></th>
                <th rowspan="<?= max(count($allHolidays), 1) ?>"><?= count($balanceHolidays) ?></th>
            <?php endif; ?>

            <td colspan="2">0</td>
            <td colspan="7"></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="14" style="text-align: center;">No festival leave data available</td>
</tr>
<?php endif; ?>

            <th colspan="14" style="text-align: left;">DETAILS OF CASUAL LEAVE</th>
        </tr>
        <tr>
            <th colspan="2">Period</th>
            <th  rowspan="2">Total Leave</th>
            <th  rowspan="2">Availed Leave</th>
            <th  rowspan="2">Balance Leave</th>
            <th colspan="2" rowspan="2">Payment made in lieu of Casual Leave, when called for work</th>
            <th colspan="7" rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th >From</th>
            <th >To</th>
        </tr>
        <tr>
            <td colspan="2"><?= htmlspecialchars(($row['month'] ?? '').' - '.($row['year'] ?? '')) ?></td>
            <td><?= htmlspecialchars($row['cl_opening'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
            <td colspan="2">NIL</td>
            <td colspan="7"></td>
        </tr>
        <tr>
            <th colspan="14" style="text-align: right;">Name and Signature of Authority</th>
        </tr>
    </table>
     <?php if ($currentEmployee < $totalEmployees): ?>
        <div style="page-break-after: always;"></div>
    <?php endif; ?>
<?php endforeach; ?>
</body>
</html>