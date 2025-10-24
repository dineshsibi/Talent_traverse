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
$pdo = require($configPath); // This gets the PDO connection from config.php

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Dadra and Nagra Haveli'; // Hardcoded for this state template

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


    $employee = !empty($stateData) ? reset($stateData) : [];
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
            font-family: "Times New Roman", Times, serif;
            margin: 15px;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            background-color: white;
            font-size: 12px;
        }

        th {
            font-weight: bold;
        }

        .main-heading {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 8px;
        }

        .input-field {
            display: block;
            min-height: 18px;
            border-bottom: 1px dotted #999;
            margin-top: 3px;
        }

        .section-heading {
            font-weight: bold;
            text-align: center;
        }

        .note {
            font-size: 11px;
            padding: 5px;
            font-style: italic;
            text-align: left;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="11" class="main-heading">
                    FORM XXI <br>
                    The Goa, Daman and Dieu Shops and Establishments Act, 1973<br>
                    The Dadra and Nagar Haveli Shops and Establishments Rules, 2000, [See Rule 31(1)]<br>
                    Register of Employment
                </th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name of Establishment and address:</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name) . ' , ' . htmlspecialchars($employee['branch_address'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Registration Number:</th>
                <td colspan="5" style="text-align: left;">-</td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Name of Employer and address:</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?> , <?= htmlspecialchars($first_row['employer_address'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Month & Year</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left;">Working Hours & Rest Interval</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($first_row['shift_details'] ?? '') ?></td>
            </tr>

            <!-- Column headers -->
            <tr>
                <th rowspan="2">Sr.No.</th>
                <th rowspan="2">Name of employee</th>
                <th rowspan="2">Employee Code</th>
                <th rowspan="2">Father's/Husband's name</th>
                <th rowspan="2">Age</th>
                <th rowspan="2">Nature of work or designation</th>
                <th rowspan="2">Date of Appointment</th>
                <th rowspan="2">Hours worked during wage period ending</th>
                <th colspan="2">Total hours worked</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Normal</th>
                <th>Overtime</th>
            </tr>

            <!-- Column numbers -->
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
                <?php $i = 1;
                foreach ($stateData as $row): ?>
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
                    // Calculate total hours worked for this employee
                    $totalHoursWorked = 0;
                    for ($day = 1; $day <= 31; $day++) {
                        $dayColumn = 'day_' . $day;
                        if (isset($row[$dayColumn]) && is_numeric($row[$dayColumn])) {
                            $totalHoursWorked += (float)$row[$dayColumn];
                        }
                    }
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($age) ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td><?= $totalHoursWorked ?></td>
                        <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" style="text-align:center;">No data available for Dadra and Nagra Haveli</td>
                </tr>
            <?php endif; ?>
            <tr>
                <td colspan="11" class="note">
                    Note: - Mark 'H' shall be made on any day on which holiday is given.
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>