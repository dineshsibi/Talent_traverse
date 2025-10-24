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
$currentState = 'Bihar'; // Hardcoded for this state template

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

    $employer_name = $first_row['employer_name'] ?? '';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            font-family: "Times New Roman", Times, serif;
        }

        body {
            margin: 20px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            word-wrap: break-word;
            text-align: center;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
        }

        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }

        .signature-cell {
            height: 30px;
            vertical-align: bottom;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <th colspan="10" class="form-header">
                Form XI<br>
                The Bihar Shops and Establishments Rules, 1955, [Rule(21(4)]<br>
                Register of Fines and Deduction
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of the establishment</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of Employer</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($employer_name) ?> </td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Month & Year</th>
            <td colspan="6" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th style="width: 4%;">Sl.No</th>
            <th style="width: 7%;">Employee code</th>
            <th style="width: 10%;">Name of Employees</th>
            <th style="width: 10%;">Gender</th>
            <th style="width: 6%;">Nature and date of the offence/damage or loss for which the fine is imposed/ deduction is made</th>
            <th style="width: 7%;">Whether worker showed cause against fine /deduction if so enter date</th>
            <th style="width: 6%;">Rate of wages</th>
            <th style="width: 10%;">Date and amount of fine /Deduction imposed</th>
            <th style="width: 6%;">Date/s on which fine/deduction imposed is realised</th>
            <th style="width: 6%;">Remarks</th>
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
        </tr>
        <tr>
            <td>1</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
        </tr>
    </table>
</body>
</html>