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
$currentState = 'Odisha'; // Hardcoded for this state template

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

        .page {
            width: 100%;
            page-break-after: always;
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
            background-color: #ffffff;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .no-border {
            border: none;
        }
    </style>
</head>

<body>
    <div class="page">
        <table>
            <tr>
                <th colspan="14" style="text-align: center; font-size: 16px; padding: 8px;">
                    Appendix 2(b)<br>
                    The Orissa Shops and Commercial Establishments (Amendment) Rules, 2009<br>
                    Combined Register of Fines, Deductions for Damage or Loss and Advances
                </th>
            </tr>
            <tr>
                <th colspan="4" class="text-left">Name and Address of the Factory/Establishment</th>
                <td colspan="10" style="text-align: left;">
                    <?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="4" class="text-left">Month / Year</th>
                <td colspan="10" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th colspan="14" class="text-left">
                    In lieu of<br>
                    1. Form No.I.II of Rule 21 (4) of Orissa Minimum Wages Rules, 1954<br>
                    2. Form Nos. XVII, XVI, XVIII of Rule, 78 (d) (fine), 77 (22) (d) (dedu.), 77 (2) (d) (adv.) of Orissa Contract Labour (R & A) Rules, 1975.<br>
                    3. Form Nos. I, II, III under Rule 3 (1) (fine), 4 (deductions) and 17 (3) (advances) of Orissa Payment of Wages Rules, 1936<br>
                    4. Form XIX, XX, XXI of Rule 52 (2) C of Orissa I.S.M.W (RE & CS) Rules, 1980.<br>
                    5. Form XX, XIX and XXI under Rule-239 (1) (b) of Orissa Building and Other Construction Workers (RE & CS) Rules, 2002
                </th>
            </tr>
            <tr>
                <th rowspan="2">Sl.No.</th>
                <th rowspan="2">Name of the Employee / Father's / Husband's name</th>
                <th rowspan="2">Designation/Employee ID</th>
                <th rowspan="2">Nature & date of offence for which fine imposed</th>
                <th rowspan="2">Date and particulars of damages/ loss caused</th>
                <th rowspan="2">Whether worker show cause against fine / Deduction</th>
                <th rowspan="2">Amount of the fine imposed / deduction made</th>
                <th rowspan="2">Date & purpose for which advance was made</th>
                <th rowspan="2">Amount of advance made & purpose thereof.</th>
                <th rowspan="2">No. of installments granted for repayment of fines/deductions/advances</th>
                <th rowspan="2">Date of Complete Recovery for Damage or Loss</th>
                <th colspan="2">Date of recovery of fine/ deduction</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>First Installment</th>
                <th>Last Installment</th>
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
            </tr>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td>NIL</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>