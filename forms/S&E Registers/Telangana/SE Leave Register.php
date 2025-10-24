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
$currentState = 'Telangana';

try {
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

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            page-break-inside: avoid;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            font-weight: bold;
        }

        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }

        .left-align {
            text-align: left;
        }

        .employee-container {
            page-break-after: always;
            height: 100vh;
            display: block;
        }

        .employee-container:last-child {
            page-break-after: auto;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .employee-container {
                page-break-after: always;
                break-after: page;
            }

            .employee-container:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }

            table {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $index => $row):
        $currentEmployee++;
        $isLastEmployee = ($index === count($stateData) - 1);

        // Get the month number from month name
        $monthNum = date('m', strtotime($month));

        // Process leave data to find PL, SL, and CL days
        $plDays = [];
        $slDays = [];
        $clDays = [];

        // Check all day fields (day_1 to day_31) for leave types
        for ($day = 1; $day <= 31; $day++) {
            $dayKey = 'day_' . $day;
            if (isset($row[$dayKey])) {
                if ($row[$dayKey] == 'PL') {
                    $plDays[] = $day;
                } elseif ($row[$dayKey] == 'SL') {
                    $slDays[] = $day;
                } elseif ($row[$dayKey] == 'CL') {
                    $clDays[] = $day;
                }
            }
        }

        // Format days for display (e.g., "5,6")
        $plDaysDisplay = !empty($plDays) ? implode(',', $plDays) : '-';
        $slDaysDisplay = !empty($slDays) ? implode(',', $slDays) : '-';
        $clDaysDisplay = !empty($clDays) ? implode(',', $clDays) : '-';

        // Calculate leave counts
        $plCount = count($plDays);
        $slCount = count($slDays);
        $clCount = count($clDays);
    ?>
        <!-- Wrap each employee's table in a div and conditionally apply page break -->
        <div class="employee-container" style="<?= $isLastEmployee ? 'page-break-after: avoid;' : 'page-break-after: always;' ?>">
            <table>
                <!-- Title Row -->
                <tr>
                    <th colspan="26" class="title">
                        FORM - XXV<br>
                        The Andhra Pradesh Shops and Establishments Act, 1988 and Rules, 1990 [See Rule 29(6)]<br>
                        Register of Leave
                    </th>
                </tr>

                <!-- Establishment Information -->
                <tr>
                    <th colspan="3" class="left-align" rowspan="2">Name and Address of the Shop/establishment</th>
                    <td colspan="10" rowspan="2" class="left-align"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>

                    <th colspan="2" class="left-align">Name of Shop-Keeper/employer</th>
                    <td colspan="11" class="left-align"><?= htmlspecialchars(($first_row['employer_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th colspan="2" class="left-align">Registration Number</th>
                    <td colspan="11" class="left-align">-</td>
                </tr>
                <tr>
                    <th colspan="3" class="left-align">Employee I.D.</th>
                    <td colspan="10" class="left-align"><?= htmlspecialchars(($first_row['employee_code'] ?? '')) ?></td>

                    <th colspan="2" class="left-align">Father's/Husband's Name</th>
                    <td colspan="11" class="left-align"><?= htmlspecialchars(($first_row['father_name'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th colspan="3" class="left-align">Name of the employee</th>
                    <td colspan="10" class="left-align"><?= htmlspecialchars(($first_row['employee_name'] ?? '')) ?></td>

                    <th colspan="2" class="left-align">Date of appointment</th>
                    <td colspan="11" class="left-align"><?= htmlspecialchars(($first_row['date_of_joining'] ?? '')) ?></td>
                </tr>

                <!-- Leave Type Headers -->
                <tr>
                    <th colspan="8">PRIVILEGE LEAVE</th>
                    <th colspan="6">SICK LEAVE</th>
                    <th colspan="6">CASUAL LEAVE</th>
                    <th colspan="4">If refused, in part or full</th>
                    <th colspan="2">Signature of</th>
                </tr>
                <tr>
                    <!-- Privilege Leave Subheaders -->
                    <th rowspan="2">Month</th>
                    <th rowspan="2">Date Of Application</th>
                    <th colspan="2">Applied</th>
                    <th rowspan="2">No. of days to which the employee is entitled</th>
                    <th colspan="2">Leave granted</th>
                    <th rowspan="2">Balance</th>

                    <!-- Sick Leave Subheaders -->
                    <th colspan="2">Applied</th>
                    <th rowspan="2">No. of days to which the employee is entitled</th>
                    <th colspan="2">Leave granted</th>
                    <th rowspan="2">Balance</th>

                    <!-- Casual Leave Subheaders -->
                    <th colspan="2">Applied</th>
                    <th rowspan="2">No. of days to which the employee is entitled</th>
                    <th colspan="2">Leave granted</th>
                    <th rowspan="2">Balance</th>

                    <th rowspan="2">Date</th>
                    <th rowspan="2">No. of days</th>
                    <th rowspan="2">Reasons</th>
                    <th rowspan="2">Nature of Leave refused</th>
                    <th rowspan="2">Signature of Employee</th>
                    <th rowspan="2">Employer</th>
                </tr>
                <tr>
                    <!-- Applied Subheaders -->
                    <th>Date</th>
                    <th>No of Days</th>
                    <th>Date</th>
                    <th>No of Days</th>

                    <!-- Sick Leave Applied Subheaders -->
                    <th>Date</th>
                    <th>No. of days</th>
                    <th>Date</th>
                    <th>No. of days</th>

                    <!-- Casual Leave Applied Subheaders -->
                    <th>Date</th>
                    <th>No. of days</th>
                    <th>Date</th>
                    <th>No. of days</th>
                </tr>

                <!-- Column Numbers -->
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
                    <th>26</th>
                </tr>

                <!-- Sample Data Rows -->
                <tr>
                    <td><?= htmlspecialchars(($row['month'] ?? '')) . ' - ' . htmlspecialchars(($row['year'] ?? '')) ?></td>
                    <td><?= $plDaysDisplay ?></td>
                    <td><?= $plDaysDisplay ?></td>
                    <td><?= $plCount ?></td>
                    <td><?= $plDaysDisplay ?></td>
                    <td><?= $plDaysDisplay ?></td>
                    <td><?= $plCount ?></td>
                    <td><?= htmlspecialchars(($row['pl_closing'] ?? '')) ?></td>
                    <td><?= $slDaysDisplay ?></td>
                    <td><?= $slCount ?></td>
                    <td><?= $slCount ?></td>
                    <td><?= $slDaysDisplay ?></td>
                    <td><?= $slCount ?></td>
                    <td><?= htmlspecialchars(($row['sl_closing'] ?? '')) ?></td>
                    <td><?= $clDaysDisplay ?></td>
                    <td><?= $clCount ?></td>
                    <td><?= $clDaysDisplay ?></td>
                    <td><?= $clDaysDisplay ?></td>
                    <td><?= $clCount ?></td>
                    <td><?= htmlspecialchars(($row['cl_closing'] ?? '')) ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
</body>

</html>