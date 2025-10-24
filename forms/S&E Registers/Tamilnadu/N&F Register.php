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
$currentState = 'Tamilnadu';

try {
    // Fetch employees for the selected location
    $employeeSql = "SELECT * FROM input 
                    WHERE client_name = :client_name
                    AND state LIKE :state
                    AND location_code = :location_code";

    if (!empty($filters['month']) && !empty($filters['year'])) {
        $employeeSql .= " AND month = :month AND year = :year";
    }

    $employeeStmt = $pdo->prepare($employeeSql);
    $employeeStmt->bindValue(':client_name', $filters['client_name']);
    $employeeStmt->bindValue(':state', "%$currentState%");
    $employeeStmt->bindValue(':location_code', $currentLocation);

    if (!empty($filters['month']) && !empty($filters['year'])) {
        $employeeStmt->bindValue(':month', $filters['month']);
        $employeeStmt->bindValue(':year', $filters['year']);
    }

    $employeeStmt->execute();
    $employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch holidays for the selected location
    $holidaySql = "SELECT DISTINCT holiday_date FROM nfh 
                   WHERE location_code = :location_code
                   ORDER BY holiday_date
                   LIMIT 20";

    $holidayStmt = $pdo->prepare($holidaySql);
    $holidayStmt->bindValue(':location_code', $currentLocation);
    $holidayStmt->execute();
    $holidays = $holidayStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get location details from first employee record
    $first_row = !empty($employees) ? reset($employees) : [];
    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
    $location_name = $first_row['location_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';

    // Selected month/year for logic
    $selectedMonth = !empty($month) ? (int)$month : 0;
    $selectedYear  = !empty($year) ? (int)$year : 0;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Calculate total columns for colspan (Sl.No + Name + Ticket + Holidays + Remarks)
$totalColumns = count($holidays) + 4;
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
            font-size: 12px;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .employee-name {
            text-align: left;
            min-width: 120px;
        }

        .employee-code {
            min-width: 80px;
        }

        .holiday-col {
            min-width: 25px;
        }

        .remarks-col {
            min-width: 80px;
        }

        .holiday-header {
            white-space: nowrap;
            padding: 5px;
            font-size: 11px;
            transform: none;
            writing-mode: horizontal-tb;
        }

        .signature-row td {
            border: none;
            height: 40px;
            padding-top: 20px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <td class="form-header" colspan="<?php echo $totalColumns; ?>">
                    Form VI <br>
                    Register of National and Festival Holidays for the Year <?php echo $year ?: '2024'; ?> <br>
                    The Tamil Nadu Industrial Establishments (National, Festival and Special Holidays) Act, 1958<br>
                    [See sub-rule (1) of rule 7]
                </td>
            </tr>
            <tr>
                <th colspan="<?php echo $totalColumns; ?>">
                    Note: Days, dates and months of the year on which National and Festival Holidays are allowed under section 3 of the Tamil Nadu Industrial Establishments National and Festival Holidays Act, 1958 (Tamil Nadu Act XXXIII of 1958)
                </th>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Name of the Factory/Plantation/Establishment:</th>
                <td style="text-align: left;" colspan="<?php echo $totalColumns - 2; ?>">
                    <?= htmlspecialchars($client_name . ' , ' . $branch_address) ?>
                </td>
            </tr>
            <tr>
                <th rowspan="2">Sl. No</th>
                <th rowspan="2">Name of the Employee</th>
                <th rowspan="2">Ticket number or father's name</th>
                <?php foreach ($holidays as $holiday): ?>
                    <th class="holiday-header" rowspan="2">
                        <?php
                        $date = date_create($holiday['holiday_date']);
                        echo date_format($date, 'd-m-Y');
                        ?>
                    </th>
                <?php endforeach; ?>
                <th rowspan="2">Remarks</th>
            </tr>
            <!-- This empty row was causing the alignment issue -->
            <tr style="display: none;"></tr>
        </thead>
        <tbody>
            <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="<?php echo $totalColumns; ?>" style="text-align: center;">
                        No employees found for this location
                    </td>
                </tr>
            <?php else: ?>
                <?php $slNo = 1; ?>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?php echo $slNo++; ?></td>
                        <td class="employee-name"><?php echo safe($employee['employee_name'] ?? ''); ?></td>
                        <td class="employee-code"><?php echo safe($employee['employee_code'] ?? ''); ?></td>

                        <?php foreach ($holidays as $holiday): ?>
                            <?php
                            $holidayDate  = date_create($holiday['holiday_date']);
                            $holidayMonth = (int)date_format($holidayDate, 'm');
                            $holidayYear  = (int)date_format($holidayDate, 'Y');

                            $mark = '';
                            if ($selectedYear > 0 && $selectedMonth > 0) {
                                if ($holidayYear < $selectedYear) {
                                    $mark = 'H';
                                } elseif ($holidayYear == $selectedYear && $holidayMonth <= $selectedMonth) {
                                    $mark = 'H';
                                }
                            }
                            ?>
                            <td class="holiday-col"><?= $mark ?></td>
                        <?php endforeach; ?>

                        <td class="remarks-col"></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <tr>
                <th colspan="<?php echo $totalColumns; ?>" style="text-align: left;">
                    <br>
                    To be marked as follows:<br>
                    'H' for holidays allowed<br>
                    'W/D' for work on double wages<br>
                    'W/H' for work with substituted holiday<br>
                    'N/E' if not eligible for the wages<br>
                </th>
            </tr>
        </tbody>
    </table>
</body>

</html>