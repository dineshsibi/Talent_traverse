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
$currentState = 'Gujarat';

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
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .form-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .act-reference {
            font-size: 14px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        .info-row {
            background-color: #ffffff;
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            background-color: #ffffff;
            text-align: center;
        }

        .sub-section {
            font-weight: bold;
            text-align: center;
        }

        .signature-section {
            margin-top: 50px;
            text-align: right;
        }

        .dashed-line {
            border-bottom: 1px dashed black;
            display: inline-block;
            min-width: 80px;
        }

        .center {
            text-align: center;
        }

        .empty-row {
            height: 20px;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;

        // Process leave data to find PL, SL, and CL days
        $plDays = [];
        $slDays = [];
        $clDays = [];

        // Check all day fields (day_1 to day_31) for leave types
        for ($day = 1; $day <= 31; $day++) {
            $dayKey = 'day_' . $day;
            if (!empty($row[$dayKey])) {
                $value = strtoupper(trim($row[$dayKey])); // normalize
                if ($value === 'PL') {
                    $plDays[] = $day;
                } elseif ($value === 'SL') {
                    $slDays[] = $day;
                } elseif ($value === 'CL') {
                    $clDays[] = $day;
                }
            }
        }

        // Format days for display (e.g., "5,6")
        $plDaysDisplay = !empty($plDays) ? implode(',', $plDays) : '';
        $slDaysDisplay = !empty($slDays) ? implode(',', $slDays) : '';
        $clDaysDisplay = !empty($clDays) ? implode(',', $clDays) : '';

    ?>
        <table>
            <tr>
                <th class="form-header" colspan="18">
                    Form – 'N'<br>
                    The Gujarat Shops and Establishments (Regulation of Employment and Conditions of Service) Act, 2019 and Rules, 2020, [See Rule 17]<br>
                    LEAVE BOOK
                </th>
            </tr>
            <tr>
                <th colspan="2">Name and address of the establishment</th>
                <td colspan="2"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                <th colspan="2">Name of the Employer</th>
                <td colspan="2"><?= htmlspecialchars($row['employer_name'] ?? '') ?></td>
                <th colspan="2">Receipt of leave book -</th>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th colspan="3">Name of the worker</th>
                <td colspan="3"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <th colspan="3">Employee Code </th>
                <td colspan="3"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="3">Description of the Department</th>
                <td colspan="3"><?= htmlspecialchars($row['department'] ?? '') ?></td>
                <th colspan="3">Date of entry into service</th>
                <td colspan="3"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            </tr>
            <tr>
                <th class="form-header" colspan="18">(if applicable)</th>
            </tr>
            <tr>
                <th colspan="2">Accumulation of leave</th>
                <th colspan="2">Leave allowed</th>
                <th colspan="2">Payment for leave made on</th>
                <th colspan="2">Refusal of leave</th>
                <th colspan="4">Payment for leave on discharge of a worker quitting employment, if admissible</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th colspan="2">3</th>
                <th colspan="2">4</th>
                <th colspan="2">5</th>
                <th colspan="3">6</th>
                <th>7</th>
            </tr>
            <tr>
                <th>Leave due on</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>1st Moiety</th>
                <th>2nd Moiety</th>
                <th>Application Date</th>
                <th>Date Of Refusal</th>
                <th>Date of discharge</th>
                <th>Date and amount paid</th>
                <th>Signature or left hand thumb impression of worker</th>
                <th>Remarks</th>
            </tr>
            <tr>
            <tr>
                <td><?= htmlspecialchars(date('F Y', mktime(0, 0, 0, $month, 1, $year))) ?></td>
                <td><?= cal_days_in_month(CAL_GREGORIAN, $month, $year) ?></td>
                <td colspan="2"><?= htmlspecialchars($plDaysDisplay).'-' ?></td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td></td>
            </tr>
            <tr class="empty-row">
                <td colspan="26"></td>
            </tr>
            <tr class="section-title">
                <td colspan="18">DETAILS OF FESTIVAL LEAVE</td>
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
                <?php foreach ($allHolidays as $index => $holiday):
                    // Find the holiday description
                    $holidayDesc = '';
                    foreach ($holidayData as $h) {
                        if ($h['holiday_date'] === $holiday) {
                            $holidayDesc = $h['description'];
                            break;
                        }
                    }
                ?>
                    <tr>
                        <td colspan="2"><?= htmlspecialchars($holiday) ?></td>

                        <?php if ($index === 0): ?>
                            <td rowspan="<?= $rowspan ?>"><?= count($allHolidays) ?></td>
                            <th rowspan="<?= max(count($allHolidays), 1) ?>"><?= count($availedHolidays) ?></th>
                            <th rowspan="<?= max(count($allHolidays), 1) ?>"><?= count($balanceHolidays) ?></th>
                        <?php endif; ?>

                        <td colspan="2">0</td>
                        <td colspan="7"><?= htmlspecialchars($holidayDesc) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="14" style="text-align: center;">No festival leave data available</td>
                </tr>
            <?php endif; ?>
            <tr class="empty-row">
                <td colspan="18"></td>
            </tr>
            <tr class="section-title">
                <th colspan="18">DETAILS OF CASUAL LEAVE</th>
            </tr>
            <tr>
                <th colspan="2">Period</th>
                <th rowspan="2">Total Leave</th>
                <th rowspan="2">Availed Leave</th>
                <th rowspan="2">Balance Leave</th>
                <th rowspan="2" colspan="2">Payment made in lieu of Casual Leave, when called for work</th>
                <th rowspan="2" colspan="9">Remarks</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
            </tr>
            <tr>
                <td><?= date('01-M-y', mktime(0, 0, 0, $month, 1, $year)) ?></td>
                <td><?= date('t-M-y', mktime(0, 0, 0, $month, 1, $year)) ?></td>
                <td><?= count($clDays) ?></td>
                <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
                <td colspan="2">NIL</td>
                <td colspan="9"></td>
            </tr>
            <tr>
                <th style="text-align: right;" colspan="18">Name and Signature of Authority</td>
            </tr>
        </table>
        <?php if ($currentEmployee < $totalEmployees): ?>
            <div style="page-break-after: always;"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</body>

</html>