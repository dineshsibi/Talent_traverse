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
$currentState = 'Tamilnadu'; // Hardcoded for this state template

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

        th,
        td {
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
        <tr>
            <td colspan="21" class="form-header">
                FORM–X<br>
                REGISTER OF LEAVE AND SOCIAL SECURITY BENEFITS<br>
                The Tamil Nadu Shops and Establishments Rules, 1948 Rule 16(1)(a)(iv)
            </td>
        </tr>
        <tr>
            <th colspan="7" style="text-align: left;">Name and Address of the Establishment:</th>
            <td colspan="14" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="7" style="text-align: left;">Name and Address of the Employer:</th>
            <td colspan="14" style="text-align: left;"><?= htmlspecialchars(($first_row['employer_name'] ?? '') . ' , ' . ($first_row['employer_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="7" style="text-align: left;">Name of the manager/In-charge:</th>
            <td colspan="14" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="7" style="text-align: left;">Registration No:</th>
            <td colspan="14" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="7" style="text-align: left;">For the month of Year:</th>
            <td colspan="14" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th rowspan="2">Serial Number</th>
            <th rowspan="2">Name of the employee</th>
            <th rowspan="2">Employee Identification No.</th>
            <th colspan="4">Earned leave</th>
            <th colspan="3">Medical leave</th>
            <th colspan="3">Other leave</th>
            <th colspan="5">Maternity benefit</th>
            <th colspan="2">Gratuity benefits</th>
            <th>Remarks</th>

        </tr>
        <tr>
            <th>Leave at the beginning of the month</th>
            <th>Leave earned during the period</th>
            <th>leave availed during the Month</th>
            <th>Leave balance at the end of the month</th>
            <th>Leave at beginning of the month</th>
            <th>Leave availed during the month</th>
            <th>Leave balance at the end of the month</th>
            <th>Leave at beginning of the month</th>
            <th>Leave availed during the month</th>
            <th>Leave balance at the end of the month</th>
            <th>Date of giving notice of pregnancy/delivery</th>
            <th>Amount of maternity benefit paid in advance of expected delivery and date of payment</th>
            <th>Subsequent payment of maternity benefit and date of payment</th>
            <th>Amount paid a medical bonus and date of payment</th>
            <th>leave with wages as per sections 9 or 10 the maternity benefit act, 1961</th>
            <th>Whether nomination received from the Employee</th>
            <th>Amount paid as Gratuity in case of demise/exit of the Employee</th>
            <td></td>

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
            <th>19</th>
            <th>20</th>
            <th>21</th>
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <?php
                    $cl_opening = (float)($row['cl_opening'] ?? 0);
                    $sl_opening = (float)($row['cl_opening'] ?? 0);
                    $opening =  $cl_opening + $sl_opening;

                    $cl_availed = (float)($row['cl_availed'] ?? 0);
                    $sl_availed = (float)($row['cl_availed'] ?? 0);
                    $availed =  $cl_availed + $sl_availed;

                    $cl_closing = (float)($row['cl_closing'] ?? 0);
                    $sl_closing = (float)($row['cl_closing'] ?? 0);
                    $closing =  $cl_closing + $sl_closing;


                    // Count ML days for this employee
                    $mlCount = 0;
                    if ($row) {
                        for ($d = 1; $d <= 31; $d++) {   // 👈 changed to $d
                            $dayField = 'day_' . $d;
                            if (isset($row[$dayField]) && strpos($row[$dayField], 'ML') !== false) {
                                $mlCount++;
                            }
                        }
                    }


                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['pl_opening'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['pl_credit'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td><?= htmlspecialchars($opening ?? '') ?></td>
                        <td><?= htmlspecialchars($availed ?? '') ?></td>
                        <td><?= htmlspecialchars($closing ?? '') ?></td>
                        <td>-</td>
                        <td>0</td>
                        <td>-</td>
                        <td>-</td>
                        <td><?= $mlCount > 0 ? $mlCount : 'Nil' ?></td>
                        <td>Yes</td>
                        <td>0</td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="42" class="no-data">No data available for Tamilnadu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>