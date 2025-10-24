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
$currentState = 'Karnataka'; // Hardcoded for this state template

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
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
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
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="17" class="title">FORM I<br>
            The Karnataka Minimum Wages Rules, 1958, [See Rule 22(4)]<br>
            Register of Fines, Deductions for Damages or Loss and Advances</th>
        </tr>
        <tr>
            <th colspan="17" style="text-align: left;">
                1. Forms I, II, and III under Rule 3(1) (fine) and deduction and 19(4) Advances Karnataka Payment of Wages Rules, 1963.<br>
                2. Forms XXI, XX and XXIII under Rule 78(1) of Contract Labour (Regulation and Abolition) (Karnataka) Rules, 1974.<br>
                3. Forms I and II under Rule 22(4) of Karnataka Minimum Wages Rules, 1958.<br>
                4. Form 9 under Rule 29 of Karnataka Labour Welfare Fund Rules, 1968.<br>
                5. Forms XIX, XX, XXI under Rules 46(2) (c) and 48(2) (c) of Inter-State Migrant Workmen (Regulation of Employment) and Conditions of Service (Karnataka) Rules, 1981
            </th>
        </tr>
        <tr>
            <th  colspan="8" style="text-align: left;">Name and Address of the Factory/ Industrial Establishment :</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name and address of the Principal Employer</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?></td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Month & Year :</th>
            <td colspan="9" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name and address of the contractor (if any)/place of work</th>
            <td colspan="9" style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th rowspan="2">Serial. No</th>
            <th rowspan="2">Employee Code</th>
            <th rowspan="2">Name and employees of the Factory/Establishment</th>
            <th rowspan="2">Sex<br>M/F</th>
            <th rowspan="2">Designation/Employment No.</th>
            <th rowspan="2">Nature and date of offence for which fine imposed</th>
            <th rowspan="2">Whether workman showed cause against fine or not, if so, enter date</th>
            <th rowspan="2">Date and particular of damages/loss caused</th>
            <th rowspan="2">Date and purpose for which advance was made</th>
            <th rowspan="2">Whether worker show cause against fine/deduction</th>
            <th rowspan="2">Amount of fine imposed deduction advance made</th>
            <th rowspan="2">No. of instalments granted for repayment for fined deductions or advances</th>
            <th rowspan="2">Wages period rate of wages payable</th>
            <th colspan="2">Date of recovery of fine deduction advance</th>
            <th rowspan="2">Designation and signature of the employee</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>First instalment</th>
            <th>Last instalment</th>
        </tr>
        <tr>
            <th>1</th>
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
        </tr>
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
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
        </tr>
    </table>
</body>
</html>