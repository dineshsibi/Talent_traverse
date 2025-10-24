<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/../../../includes/functions.php';

// Load database connection
$configPath = __DIR__.'/../../../includes/config.php';
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
$currentState = 'Assam'; // Hardcoded for this state template

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
            font-size: 14px;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            font-family: 'Times New Roman', Times, serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: 'Times New Roman', Times, serif;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-family: 'Times New Roman', Times, serif;
        }
        th {
            background-color: #ffffff;
            font-family: 'Times New Roman', Times, serif;
        }
        .empty-row {
            height: 30px;
        }
        .colspan-18 {
            width: 100%;
        }
        .sub-header {
            font-weight: bold;
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
        }
        .no-data {
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th class="form-header" colspan="10">
                    Form I<br>
                    The Assam Industrial Establishments (Conferment of Permanent Status to Workmen) Rules, 1995 (See Rule 6 (1) )<br>
                    Register of Workmen
                </th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Name and Address of the Factory/ Establishment</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">For the Month of</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year) ?></td>
            </tr>
            <tr>
                <th>Serial No.</th>
                <th>Employee Code</th>
                <th>Name and address<br>of the workmen</th>
                <th>Designations of<br>the workmen</th>
                <th>Whether permanent,<br>temporary, casual,<br>Badli or apprentice<br>(other than those<br>covered under the<br>Apprentices Act, 1961)</th>
                <th>Date of the first<br>entry into service</th>
                <th>Date on which<br>he completed 180<br>days of Service</th>
                <th>Date on which<br>made permanent</th>
                <th>Remarks</th>
                <th>Signature of the<br>workmen with date<br>(to attest the entries)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1; foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars(($row['employee_name'] ?? '') . ','. ($row['present_address'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td>Permanent</td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td>
                            <?php 
                            if (!empty($row['date_of_joining'])) {
                                $doj = new DateTime($row['date_of_joining']);
                                $doj->add(new DateInterval('P180D'));
                                echo htmlspecialchars($doj->format('Y-m-d'));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['date_of_confirmation'] ?? '-') ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="no-data" colspan="10">No data available for Assam</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>