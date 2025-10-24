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
$currentState = 'Andhra Pradesh'; // Hardcoded for this state template

try {
    // First get all employees who have ML in any day field
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
    $allEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter employees who have ML in any day field
    $mlEmployees = [];
    foreach ($allEmployees as $employee) {
        for ($i = 1; $i <= 31; $i++) {
            $dayField = 'day_' . $i;
            if (isset($employee[$dayField]) && strpos($employee[$dayField], 'ML') !== false) {
                $mlEmployees[] = $employee;
                break; // No need to check other days if we found at least one ML
            }
        }
    }

    $first_row = !empty($allEmployees) ? reset($allEmployees) : [];

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
    $branch_address = $first_row['branch_address'] ?? '';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// If no employees with ML, show a single form with Nil values
if (empty($mlEmployees)) {
    $mlEmployees = [null]; // Dummy entry to show one form
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
            margin: 0;
            padding: 20px;
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
            page-break-inside: avoid;
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

        .empty-row {
            height: 25px;
        }

        .note {
            font-size: 0.9em;
            margin-top: 10px;
        }

        .signature-line {
            margin-top: 30px;
            text-align: right;
        }

        .col-1 {
            width: 5%;
        }

        .col-2 {
            width: 30%;
        }

        .label {
            font-weight: bold;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-row-group;
        }

        tr {
            page-break-inside: avoid;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <?php foreach ($mlEmployees as $index => $row): ?>
        <?php
        // Count ML days for this employee
        $mlCount = 0;
        if ($row) {
            for ($i = 1; $i <= 31; $i++) {
                $dayField = 'day_' . $i;
                if (isset($row[$dayField]) && strpos($row[$dayField], 'ML') !== false) {
                    $mlCount++;
                }
            }
        }
        ?>
        <table>
            <thead>
                <tr>
                    <td class="form-header" colspan="6">
                        Form ‘A’ <br>
                        (See Rule 3) Muster Roll <br>
                        The Maternity Benefit Act, 1961 And Rules, 1967
                    </td>
                </tr>
                <tr>
                    <th style="text-align: left;" colspan="2">Name and address of the Establishment</th>
                    <td colspan="4" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
                </tr>
                <tr>
                    <th style="text-align: left;" colspan="2">Month & Year</th>
                    <td colspan="4" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>1</th>
                    <th style="text-align: left;">Serial Number</th>
                    <td colspan="4" style="text-align: left;"><?= htmlspecialchars($row ? ($row['employee_code'] ?? '') : 'Nil') ?></td>
                </tr>
                <tr>
                    <th>2</th>
                    <th style="text-align: left;">Name of woman and her father's (or if married, husband's) name</th>
                    <td colspan="4" style="text-align: left;"><?= $row ? htmlspecialchars($row['father_name'] ?? '') : 'Nil' ?></td>
                </tr>
                <tr>
                    <th>3</th>
                    <th style="text-align: left;">Date of appointment</th>
                    <td colspan="4" style="text-align: left;"><?= $row ? htmlspecialchars($row['date_of_joining'] ?? '') : 'Nil' ?></td>
                </tr>
                <tr>
                    <th>4</th>
                    <th style="text-align: left;">Nature of work</th>
                    <td colspan="4" style="text-align: left;"><?= $row ? htmlspecialchars($row['designation'] ?? '') : 'Nil' ?></td>
                </tr>
                <tr>
                    <th>5</th>
                    <th colspan="5" style="text-align: left;">Dates with month and year in which she is employed, laid off or not employed</th>
                </tr>
                <tr>
                    <th style="text-align: center;" colspan="2">Month</th>
                    <th>No. of days employed</th>
                    <th>No. of days laid off</th>
                    <th>No. of days not employed</th>
                    <th>Remarks</th>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;"><?= htmlspecialchars($month . '-' . $year) ?></td>
                    <td><?= $row ? htmlspecialchars($row['total_worked_days'] ?? '') : 'Nil' ?></td>
                    <td>Nil</td>
                    <td><?= $mlCount > 0 ? $mlCount : 'Nil' ?></td>
                    <td></td>
                </tr>
                <tr>
                    <th>6</th>
                    <th style="text-align: left;">Date on which the woman gives notice under section 6</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>7</th>
                    <th style="text-align: left;">Date of discharge/dismissal, if any</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>8</th>
                    <th style="text-align: left;">Date of production of proof of pregnancy under section 6</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>9</th>
                    <th style="text-align: left;">Date of birth of child</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>10</th>
                    <th style="text-align: left;">Date of production of proof of delivery/miscarriage/death</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>11</th>
                    <th style="text-align: left;">Date of production of proof of illness referred to in section 10</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>12</th>
                    <th style="text-align: left;">Date with amount of maternity benefit paid in advance of expected delivery</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>13</th>
                    <th style="text-align: left;">Date with the amount of subsequent payment of maternity benefit</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>14</th>
                    <th style="text-align: left;">Date with the amount of bonus, if paid under section 8</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>15</th>
                    <th style="text-align: left;">Date with the amount of wages paid on account of leave under section 9</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>16</th>
                    <th style="text-align: left;">Date with the amount of wages paid on account of leave under section 10 and period of leave granted</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>17</th>
                    <th style="text-align: left;">Name of the person nominated by the woman under section 6</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>18</th>
                    <th style="text-align: left;">maternity benefit and/or other amount was paid, the amount thereof, and the date of payment</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>19</th>
                    <th style="text-align: left;">If the woman dies and the child survives, the name of the person to whom the amount of maternity benefit was paid on behalf of the child and the period for which it was paid</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>20</th>
                    <th style="text-align: left;">Signature of the employer of the establishment authenticating the entries in the muster-roll</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th>21</th>
                    <th style="text-align: left;">Remarks column for the use of the Inspector</th>
                    <td colspan="4" style="text-align: left;">Nil</td>
                </tr>
                <tr>
                    <th colspan="6" style="text-align: left;">
                        Note * TF - Transfer From, TT - Transfer To, DOL - Date of Leaving<br>
                        If the woman dies, the date of her death, the name of the person to whom
                    </th>
                </tr>
                <tr>
                    <th colspan="6" style="text-align: right;">Signature of Employer / Manager / Contractor / Authorised Person</th>
                </tr>
            </tbody>
        </table>

        <?php if ($index < count($mlEmployees) - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</body>

</html>