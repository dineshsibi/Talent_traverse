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
$currentState = 'Meghalaya'; // Hardcoded for this state template

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


    // Employer data with array handling
    $employer_name = safe($first_row['employer_name'] ?? '');
    $employer_address = safe($first_row['employer_address'] ?? '');
    $branch_address = safe($first_row['branch_address'] ?? '');
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
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 5px;
            font-size: 6px;
            line-height: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 1px;
            text-align: center;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffffff;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .main-heading {
            text-align: center;
            font-size: 8px;
            padding: 2px;
            border: 1px solid #000;
            background-color: #ffffffff;
        }

        .info-row {
            text-align: left;
            font-weight: bold;
            background-color: #ffffffff;
        }

        .info-data {
            text-align: left;
        }

        .section-heading {
            background-color: #ffffffff;
            font-weight: bold;
            text-align: center;
        }

        .sub-heading {
            background-color: #ffffffff;
            font-weight: bold;
        }

        .signature {
            text-align: right;
            margin-top: 5px;
            font-style: italic;
            font-size: 7px;
        }

        .note {
            font-style: italic;
            font-size: 6px;
            text-align: left;
            margin-top: 3px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;

        // Get the month number from month name
        $monthNum = date('m', strtotime($month));

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

        // Merge SL + CL cleanly (no stray commas)
        $slClDisplay = '';
        if ($slDaysDisplay !== '' && $clDaysDisplay !== '') {
            $slClDisplay = $slDaysDisplay . ',' . $clDaysDisplay;
        } elseif ($slDaysDisplay !== '') {
            $slClDisplay = $slDaysDisplay;
        } elseif ($clDaysDisplay !== '') {
            $slClDisplay = $clDaysDisplay;
        }


        $fromDate = '';
        $toDate = '';
        if (!empty($month) && !empty($year)) {
            // Create a date object for the first day of the month
            $dateObj = DateTime::createFromFormat('!m Y', $month . ' ' . $year);

            if ($dateObj) {
                // First day of month
                $fromDate = $dateObj->format('01-M-Y');

                // Clone and get last day of month
                $toDate = $dateObj->format('t-M-Y'); // 't' gives number of days in the month
            }
        }

    ?>
        <table>
            <!-- Main Heading -->
            <tr>
                <th colspan="25" class="main-heading">
                    FORM ‘T’ <br>
                    The Meghalaya Shops and Establishments Rules, 2004, [See Rule 54 and 55] <br>
                    Register of leave with wages
                </th>
            </tr>

            <!-- Establishment Details -->
            <tr>
                <td colspan="5" class="info-row">Name of the Establishment:</td>
                <td colspan="20" class="info-data"><?= htmlspecialchars($client_name) ?></td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Name of the Employer:</td>
                <td colspan="20" class="info-data"><?= htmlspecialchars($employer_name) ?></td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Address:</td>
                <td colspan="20" class="info-data"><?= htmlspecialchars($branch_address) ?></td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Registration No:</td>
                <td colspan="20" class="info-data">-</td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Name of the Employee:</td>
                <td colspan="20" class="info-data"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Father's Name:</td>
                <td colspan="20" class="info-data"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Permanent Address:</td>
                <td colspan="20" class="info-data"><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Leave Address, if any:</td>
                <td colspan="20" class="info-data">NIL</td>
            </tr>
            <tr>
                <td colspan="5" class="info-row">Date of Entry into Service:</td>
                <td colspan="20" class="info-data"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            </tr>

            <!-- General Leave Section -->
            <tr>
                <td colspan="2" class="sub-heading">Period of twelve month's continuous service</td>
                <td colspan="3" class="sub-heading">LEAVE DUE</td>
                <td colspan="1" class="sub-heading" rowspan="2">Date of Application for leave</td>
                <td colspan="3" class="sub-heading">Leave applied for</td>
                <td colspan="3" class="sub-heading">Leave Allowed</td>
                <td colspan="3" class="sub-heading">Leave Availed</td>
                <td colspan="2" class="sub-heading">Extended</td>
                <td colspan="8" class="sub-heading">Balance Leave to Credit, if any</td>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>No. of days of accumulated leave due, if any</th>
                <th>No. of days for which leave is due currently</th>
                <th>Total number of days of leave to credit</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th colspan="8">No. of days</th>

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
                <th colspan="8">18</th>

            </tr>
            <tr>
                <td><?= htmlspecialchars($fromDate) ?></td>
                <td><?= htmlspecialchars($toDate) ?></td>
                <td></td>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_credit'] ?? '') ?></td>
                <td><?= htmlspecialchars($plDaysDisplay) ?></td>
                <td colspan="2"><?= htmlspecialchars($plDaysDisplay) ?></td>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                <td colspan="2"><?= htmlspecialchars($plDaysDisplay) ?></td>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                <td colspan="2"><?= htmlspecialchars($plDaysDisplay) ?></td>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                <td>Nil</td>
                <td>Nil</td>
                <td colspan="8"></td>

            </tr>

            <!-- Medical Leave Section -->
            <tr>
                <td colspan="25" class="section-heading">Medical Leave</td>
            </tr>
            <tr>
                <td colspan="2" class="sub-heading">Period of twelve month's continuous service</td>
                <td class="sub-heading" rowspan="2">No. of days of leave of credit</td>
                <td class="sub-heading" rowspan="2">Date of application for leave</td>
                <td colspan="3" class="sub-heading">Leave applied</td>
                <td colspan="3" class="sub-heading">Leave allowed</td>
                <td colspan="3" class="sub-heading">Leave availed</td>
                <td colspan="3" class="sub-heading">Leave extended</td>
                <td class="sub-heading" rowspan="2">Balance of leave to credit during the period of twelve month's if any</td>
                <td colspan="3" class="sub-heading">Leave, if refused in part or full</td>
                <td colspan="2" class="sub-heading">Wages</td>
                <td class="sub-heading" rowspan="2">Wages paid during the leave period</td>
                <td class="sub-heading" rowspan="2">Remarks</td>
                <td class="sub-heading" rowspan="2">Signature of the Employee in acquittance of payment received</td>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>

                <th>From</th>
                <th>To</th>
                <th>Reasons of refusal</th>
                <th>Normal rate of wages including dearness allowance, etc., if any</th>
                <th>Cash equivalent to supply of meal and sale for food grains of other articles at concessional rates</th>
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
                <th>22</th>
                <th>23</th>
                <th>24</th>
                <th>25</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($fromDate) ?></td>
                <td><?= htmlspecialchars($toDate) ?></td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td></td>
            </tr>


            <!-- Casual Leave Section -->
            <tr>
                <td colspan="25" class="section-heading">Casual Leave</td>
            </tr>
            <tr>
                <td colspan="2" class="sub-heading">Period of twelve month's continuous service</td>
                <td class="sub-heading" rowspan="2">No. of days of leave of credit</td>
                <td class="sub-heading" rowspan="2">Date of application for leave</td>
                <td colspan="3" class="sub-heading">Leave applied</td>
                <td colspan="3" class="sub-heading">Leave allowed</td>
                <td colspan="3" class="sub-heading">Leave availed</td>
                <td colspan="3" class="sub-heading">Leave extended</td>
                <td class="sub-heading" rowspan="2">Balance of leave to credit if any</td>
                <td colspan="3" class="sub-heading">Leave, if refusal</td>
                <td colspan="2" class="sub-heading">Wages</td>
                <td class="sub-heading" rowspan="2">Remarks</td>
                <td class="sub-heading" rowspan="2">Wages paid</td>
                <td class="sub-heading" rowspan="2">Signature of the Employee in acquittance of payment received</td>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>No. of days</th>
                <th>From</th>
                <th>To</th>
                <th>Reasons of refusal</th>
                <th>Normal rate of wages including dearness allowance, etc., if any</th>
                <th>Cash equivalent to supply of meal and sale for food grains of other articles at concessional rates</th>

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
                <th>22</th>
                <th>23</th>
                <th>24</th>
                <th>25</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($fromDate) ?></td>
                <td><?= htmlspecialchars($toDate) ?></td>
                <td><?= htmlspecialchars($row['cl_credit'] ?? '') ?></td>
                <td><?= htmlspecialchars($clDaysDisplay) ?></td>
                <td colspan="2"><?= htmlspecialchars($clDaysDisplay) ?></td>
                <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
                <td colspan="2"><?= htmlspecialchars($clDaysDisplay) ?></td>
                <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
                <td colspan="2"><?= htmlspecialchars($clDaysDisplay) ?></td>
                <td><?= htmlspecialchars($row['cl_availed'] ?? '') ?></td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td>Nil</td>
                <td><?= htmlspecialchars($row['fixed_gross'] ?? '') ?></td>
                <td></td>
                <td></td>
                <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
                <td></td>
            </tr>
            <tr>
                <th colspan="25" style="text-align: right;">Signature of the Employer:</th>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">N.B. : For each employee separate pages in the Register containing columns for each kind of leave shall be allotted.</th>
                <th colspan="10" style="text-align: right;"> Date:</th>
            </tr>
        </table>
        <?php
        // Add page break except for last employee
        if ($currentEmployee < $totalEmployees):
        ?>
            <div class="page-break"></div>
        <?php endif; ?>

    <?php endforeach; ?>
</body>

</html>