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
$currentState = 'Uttar Pradesh'; // Hardcoded for this state template

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

    $location_name = $first_row['location_name'] ?? '';
    $branch_address = $first_row['branch_address'] ?? '';
    $location = $first_row['location_name'] ?? '';
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

        .form-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
            page-break-after: always;
        }

        .form-container:last-child {
            page-break-after: auto;
        }

        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-header {
            margin-bottom: 20px;
        }

        .form-header div {
            margin-bottom: 10px;
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
            text-align: center;
        }

        .address {
            margin-left: 20px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <?php foreach ($stateData as $row): ?>
        <div class="form-container">
            <table>
                <thead>
                    <tr>
                        <th colspan="17">
                            Form G <br>
                            (Utter Pradesh Dookan Aur Vanijya Adhishthan Niyamavali, 1963) [See Rule 18(1)(b) and (c)] <br>
                            Register of Attendance and Wages
                        </th>
                    </tr>
                    <tr>
                        <th colspan="7" style="text-align: left;">Name and address of the Establishment </th>
                        <td colspan="10" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Employee Code</th>
                        <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <th style="text-align: left;" colspan="5">Man/Woman/Young Person/Child</th>
                        <td style="text-align: left;" colspan="5"><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Name of the Employee</th>
                        <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <th style="text-align: left;" colspan="5">Nature of employment </th>
                        <td style="text-align: left;" colspan="5"><?= htmlspecialchars($row['nature_of_business'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Date of Employment</th>
                        <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <th style="text-align: left;" colspan="5">Whether employed on daily </th>
                        <td style="text-align: left;" colspan="5">Monthly</td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Father's/Husband's name</th>
                        <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <th style="text-align: left;" colspan="5">contract or piece-rate wages with rate Rs. /-, Wage </th>
                        <td style="text-align: left;" colspan="5"><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: left;">Address</th>
                        <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
                        <th style="text-align: left;" colspan="5">Period</th>
                        <td style="text-align: left;" colspan="5"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                    </tr>
                    <tr>
                        <th rowspan="3">Date</th>
                        <th rowspan="3">Work Begins</th>
                        <th colspan="2" rowspan="2">Rest</th>
                        <th rowspan="3">Work Ends</th>
                        <th rowspan="3">Overtime Worked</th>
                        <th colspan="3" rowspan="2">Wages Earned</th>
                        <th rowspan="3">Signature or thumb impression of employee</th>
                        <th colspan="4">Advanced</th>
                        <th rowspan="3">Fines or other deduction vide Forms D and E</th>
                        <th rowspan="3">Net Amount due</th>
                        <th rowspan="3">Signature or thumb-impression of employee</th>
                    </tr>
                    <tr>
                        <th colspan="2">Amount Advanced</th>
                        <th rowspan="2">Amount recovered</th>
                        <th rowspan="2">Balance</th>
                    </tr>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Basic</th>
                        <th>D.F.A</th>
                        <th>Overtime</th>
                        <th>Amount</th>
                        <th>Date</th>
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
                    </tr>
                </thead>
                <tbody>
                    <?php for ($day = 1; $day <= 31; $day++): ?>
                        <tr>
                            <td>Day <?= $day ?></td>
                            <?php if ($day === 1): ?>
                                <!-- First row - show data with rowspan for all 31 days -->
                                <td colspan="4" rowspan="31"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
                                <td rowspan="31"><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                                <td rowspan="31"><?= htmlspecialchars($row['basic'] ?? '') ?></td>
                                <td rowspan="31"><?= htmlspecialchars($row['da'] ?? '') ?></td>
                                <td rowspan="31"><?= htmlspecialchars($row['da'] ?? '') ?></td>
                                <td rowspan="31"></td>
                                <td rowspan="31">Nil</td>
                                <td rowspan="31">Nil</td>
                                <td rowspan="31">Nil</td>
                                <td rowspan="31">Nil</td>
                                <td rowspan="31"><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
                                <td rowspan="31"><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                                <td rowspan="31"></td>
                            <?php endif; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</body>

</html>