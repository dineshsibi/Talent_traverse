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
$currentState = 'Manipur'; // Hardcoded for this state template

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
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 10px;
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
            padding: 6px;
            word-wrap: break-word;
            vertical-align: top;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        .form-header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>

<body>

    <table>
        <tbody>
            <tr>
                <th colspan="10" class="form-header">
                    FORM I <br>
                    The Minimum Wages (Central) Rules, 1950, [See Rule 21 (4)] <br>
                    Register of Fines
                </th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">Name and Address of the Factory/ Establishment</th>
                <td colspan="5" style="text-align: left;">
                    <?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?>
                </td>
            </tr>
            <tr>
                <th colspan="5" style="text-align: left;">For the Month of</th>
                <td colspan="5" style="text-align: left;">
                    <?= htmlspecialchars($month . ' - ' . $year) ?>
                </td>
            </tr>

            <!-- Table Headers -->
            <tr>
                <th>SL No</th>
                <th>Name</th>
                <th>Father's/Husband's Name</th>
                <th>Department</th>
                <th>Act or omission for which fine was imposed</th>
                <th>Whether workman showed cause against fine or not, if so, enter date</th>
                <th>Rate of wages</th>
                <th>Fine imposed</th>
                <th>Fine realised or remitted</th>
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
                <td></td>
            </tr>
        </tbody>
    </table>

</body>

</html>