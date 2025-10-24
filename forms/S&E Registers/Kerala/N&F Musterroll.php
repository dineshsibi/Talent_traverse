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
$currentState = 'Kerala'; // Hardcoded for this state template

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
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            min-width: 20px;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .empty-row td {
            height: 25px;
        }

        .left-align {
            text-align: left;
        }

        .date-col {
            width: 20px;
        }

        .signature-row td {
            border: none;
            height: 40px;
            padding-top: 20px;
        }

        .total-row {
            font-weight: bold;
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
                <td class="form-header" colspan="9">
                    Form VI<br>
                    The Kerala Industrial Establishments (National and Festival Holidays) Act, 1958 and Rules 1959, [See Rule 11]<br>
                    Register of Employment
                </td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Name of the Factory/Plantation/Establishment:</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Place</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($location) ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">District</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($first_row['city'] ?? '') ?></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: left;">Month & Year:</th>
                <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl . No</th>
                <th>Sl. No. in Employee</th>
                <th>Employee Name</th>
                <th>Father's/Mother's Name</th>
                <th>Nature of work</th>
                <th>Date of entry into service</th>
                <th>Date of termination of service</th>
                <th>Name of National and Festival holidays for which wages have been paid.</th>
                <th>Amount paid</th>
            </tr>
            <tr>
                <th>i</th>
                <th>ii</th>
                <th>iii</th>
                <th>iv</th>
                <th>v</th>
                <th>vi</th>
                <th>vii</th>
                <th>viii</th>
                <th>ix</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
                        <td>
                            <?php
                            if (!empty($row['nfh_wages']) && $row['nfh_wages'] > 0) {
                                $nfhSql = "SELECT description 
                             FROM nfh 
                             WHERE client_name = :client_name
                             AND state LIKE :state
                             AND location_code = :location_code
                     AND month = :month
                     AND year = :year";
                                $nfhStmt = $pdo->prepare($nfhSql);
                                $nfhStmt->bindValue(':client_name', $filters['client_name']);
                                $nfhStmt->bindValue(':state', "%$currentState%");
                                $nfhStmt->bindValue(':location_code', $currentLocation);
                                $nfhStmt->bindValue(':month', $filters['month']);
                                $nfhStmt->bindValue(':year', $filters['year']);
                                $nfhStmt->execute();
                                $holidays = $nfhStmt->fetchAll(PDO::FETCH_COLUMN);

                                echo htmlspecialchars(implode(", ", $holidays));
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['nfh_wages'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" class="no-data">No data available for Kerala</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>