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
            <td colspan="24" class="form-header">
                FORM–U <br>
                The Tamil Nadu Shops and Establishments Rules, 1948 Rule 16 (1)(a)(i)<br>
                Maintenance of Register of persons employed for shops and establishments
            </td>
        </tr>
        <tr>
            <th colspan="10" style="text-align:left;">Name and Address of the Catering Establishment:</th>
            <td colspan="14" style="text-align:left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="10" style="text-align:left;">Registration No:</th>
            <td colspan="14" style="text-align:left;">-</td>
        </tr>
        <tr>
            <th colspan="10" style="text-align:left;">Month & Year:</th>
            <td colspan="14" style="text-align:left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th>Sl. No</th>
            <th>Name of the employee</th>
            <th>Employee I.D.No.</th>
            <th>Gender</th>
            <th>Father/Spouse name</th>
            <th>Date of Birth</th>
            <th>Date of entry into service</th>
            <th>Designation</th>
            <th>Present Address</th>
            <th>Permanent Address</th>
            <th>Employee' Provident Fund – UAN</th>
            <th>Employees State Insurance corporation No.</th>
            <th>Aadhaar No.</th>
            <th>Date on which completion of 480 days of service</th>
            <th>Date on which made permanent</th>
            <th>Period of Suspension If any</th>
            <th>Bank A/C Number, Name of Bank,Branch (IFSC)</th>
            <th>Photo</th>
            <th>Mobile number</th>
            <th>E-mail ID</th>
            <th>Specimen Signature/Thumb Impression</th>
            <th>Date of Exit</th>
            <th>Reason of exit</th>
            <th>Remarks</th>
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
        </tr>
        <tbody> <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                    foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['present_address'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['permanent_address'] ?? '') ?></td>
                        <td>NIL</td>
                        <td>NIL</td>
                        <td>NIL</td>
                        <td>
                            <?php
                            if (!empty($row['date_of_joining'])) {
                                $doj = new DateTime($row['date_of_joining']);
                                $doj->add(new DateInterval('P480D'));  // add 480 days
                                echo htmlspecialchars($doj->format('d-M-y')); // format like 18-Jun-24
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['date_of_confirmation'] ?? '') ?></td>
                        <td>NA</td>
                        <td>NIL</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td></td>
                        <td><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
                        <td>-</td>
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