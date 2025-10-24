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
            background-color: #ffffffff;
            font-weight: bold;
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

        .note {
            font-style: italic;
            font-size: 11px;
            text-align: left;
        }
    </style>
</head>

<body>
    <table>
        <!-- Main Heading -->
        <tr>
            <th colspan="16" class="main-heading">
                FORM S <br>
                Register of Employment <br>
                [See Rule 53]
            </th>
        </tr>

        <!-- Establishment Details -->
        <tr>
            <td colspan="8" class="info-row">Name of the Establishment:</td>
            <td colspan="8" class="info-data">
                <?= htmlspecialchars($client_name) ?>
            </td>
        </tr>
        <tr>
            <td colspan="8" class="info-row">Registration No:</td>
            <td colspan="8" class="info-data">-</td>
        </tr>
        <tr>
            <td colspan="8" class="info-row">Address:</td>
            <td colspan="8" class="info-data">
                <?= htmlspecialchars($branch_address) ?>
            </td>
        </tr>
        <tr>
            <td colspan="8" class="info-row">Month & Year</td>
            <td colspan="8" class="info-data">
                <?= htmlspecialchars($month . ' - ' . $year) ?>
            </td>
        </tr>
        <tr>
            <td colspan="8" class="info-row">Name of the Employer:</td>
            <td colspan="8" class="info-data">
                <?= htmlspecialchars($employer_name) ?>
            </td>
        </tr>

        <!-- Column Headers -->
        <tr>
            <th>Serial No.</th>
            <th>Name of the Employee</th>
            <th>Employee Code</th>
            <th>Father's name or Husband's name in case of married</th>
            <th>Date of Birth</th>
            <th>Post held or nature of job performed</th>
            <th>Date of appointment</th>
            <th>Status Probationer/ Temporary/Casual/ Permanent</th>
            <th>Scale of pay, if any</th>
            <th>Rate of Increment</th>
            <th>Basic Pay</th>
            <th>Dearness allowance</th>
            <th>Other allowances</th>
            <th>Free Board and/or Lodging</th>
            <th>Concessional supply of food grains and/or other articles, if any</th>
            <th>Signature of the employee</th>
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
        </tr>

        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1;
                foreach ($stateData as $row): ?>

            <?php
                $gross = (float)($row['gross_wages'] ?? 0);
                    $basic = (float)($row['basic'] ?? 0);
                    $da = (float)($row['da'] ?? 0);
                    $other_allowance =  $gross-($basic+$da);
                ?>
            <tr>
                <td>
                    <?= $i++ ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['employee_name'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['employee_code'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['father_name'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['date_of_birth'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['designation'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['date_of_joining'] ?? '') ?>
                </td>
                <td>Permanent</td>
                <td>NIL</td>
                <td>-</td>
                <td>
                    <?= htmlspecialchars($row['basic'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['da'] ?? '') ?>
                </td>
                <td>
                    <?= htmlspecialchars($other_allowance ?? '') ?>
                </td>
                <td>Nil</td>
                <td>Nil</td>
                <td></td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="16" class="no-data">No  data available for Meghalaya</td>
            </tr>
            <?php endif; ?>
            <tr>
                <th colspan="16" style="text-align: left;">*According to School records or Birth Register of a Local
                    Authority</th>
            </tr>
    </table>
</body>

</html>