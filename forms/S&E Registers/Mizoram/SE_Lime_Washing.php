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
$currentState = 'Mizoram'; // Hardcoded for this state template

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
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffff !important;
            color: #000;
        }


        .text-left {
            text-align: left;
        }

        .main-heading {
            text-align: center;
            font-size: 14px;
            padding: 6px;
            border: 1px solid #000;
            background-color: #ffffffff;
        }

        .info-row {
            text-align: left;
            font-weight: bold;
        }

        .info-data {
            text-align: left;
        }

        .signature {
            text-align: right;
            margin-top: 30px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <th colspan="6" class="main-heading">
                FORM P <br>
                The Mizoram Shops and Establishments Act, 2010 and Rules, 2011, [See Rule 37(7)] <br>
                Record of Lime Washing Painting etc.
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="3" class="info-row">Name and Address of the Establishment :</td>
            <td colspan="3" class="info-data">
                <?= htmlspecialchars($client_name . ' , ' . $branch_address) ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="info-row">Month/Year :</td>
            <td colspan="3" class="info-data">
                <?= htmlspecialchars($month . ' - ' . $year) ?>
            </td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th>Description of part of the establishment i.e., name of room etc.</th>
            <th>Parts lime-washed, colour washed, painted, varnished e.g., walls, ceilings, woodworks etc.</th>
            <th>Treatment whether lime washed or colour washed or painted varnished</th>
            <th>Date on which lime washing, colour washing, painting or varnishing was carried out according to the English Calendar </th>
            <th>Signature of the employer</th>
            <th>Remarks</th>
        </tr>

        <!-- Column Numbers -->
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
        </tr>

        <!-- Data Row -->
        <tr>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
            <td></td>
        </tr>

    </table>
</body>

</html>