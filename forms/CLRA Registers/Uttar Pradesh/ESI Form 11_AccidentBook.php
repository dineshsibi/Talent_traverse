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

        .sub-title {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ffffff;
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th class="form-title" colspan="18">FORM 11 <br>
                    Accident Book <br>
                    Employee State Insurance Corporation (Regulation 66)
                </th>
            </tr>
            <tr>
                <th rowspan="2">Sl.No</th>
                <th rowspan="2">Date of Notice</th>
                <th rowspan="2">Time of Notice</th>
                <th rowspan="2">Name and address of injured person</th>
                <th rowspan="2">Sex</th>
                <th rowspan="2">Age</th>
                <th rowspan="2">Insurance No.</th>
                <th rowspan="2">Shift, department and occupation of the employee</th>
                <th colspan="5">Details of Injury</th>
                <th rowspan="2">What exactly was the injured person doing at the time of accident</th>
                <th rowspan="2">Name, Occupation, address and signature or the thumb impression of the person(s) giving notice</th>
                <th rowspan="2">Signature and designation of the person who makes the entry in the Accident Book</th>
                <th rowspan="2">Name, address and occupation of two witnesses</th>
                <th rowspan="2">Remarks, if any</th>
            </tr>
            <tr>
                <th>Cause</th>
                <th>Nature</th>
                <th>Date</th>
                <th>Time</th>
                <th>Place</th>
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
                <th>13</th>
                <th>14</th>
                <th>15</th>
                <th>16</th>
                <th>17</th>
                <th>18</th>
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
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['date_of_notice'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['time_of_notice'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['name_and_address_of_injured_person'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($age) ?></td>
                        <td><?= htmlspecialchars($row['insurance_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['shift_department_and_occupation_of_the_employee'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['cause_of_injury'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nature_of_injury'] ?? '') ?></td>
                        <td colspan="2"><?= htmlspecialchars($row['injury_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['injury_place'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['what_exactly_was_the_injured_person_doing'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['name_occupation_address_and_signature_of_notice_givers'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['signature_and_designation_of_the_person_who_makes_the_entry'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['name_address_and_occupation_of_two_witnesses'] ?? '') ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="25" style="text-align:center;">No data available for Uttar Pradesh</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>