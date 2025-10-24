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
$currentState = 'Pondicherry'; // Hardcoded for this state template

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
            <th colspan="20" class="title">
                FORM VI <br>
                The Puducherry Shops and Establishments (Amendment) Rules, 2010, [See sub-rule (4) of rule 17 and sub-rule (4) of rule 19] <br>
                REGISTER OF ADVANCE PAID, DEDUCTIONS FOR DAMAGES OR LOSSES AND FINES										
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Name and address of the Establishment</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . ($first_row['branch_address'] ?? '')) ?></td>
            <th colspan="5" style="text-align: left;">Month & Year :</th>
            <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        <tr>
            <th rowspan="2">Sl. No</th>
            <th rowspan="2">Name of the Employee</th>
            <th rowspan="2">Father's / Husband's name</th>
            <th rowspan="2">Employee number</th>
            <th rowspan="2">Designation</th>
            <th colspan="4">Advance Paid</th>
            <th colspan="6">Deduction for Damages/Loss</th>
            <th colspan="5">Fines</th>
        </tr>
        <tr>
            <th>Date of Payment</th>
            <th>Amount Paid</th>
            <th>No. of instalments to be recovered</th>
            <th>Date on whIch recovery Completed</th>
            <th>Damage Loss/Caused</th>
            <th>Date of show casue notice</th>
            <th>Total amount of dedcutions imposed</th>
            <th>No. of instalments to be recovered</th>
            <th>Date on which dedcution completed</th>
            <th>Act or Omission</th>
            <th>Date of show casue notice</th>
            <th>Amount of fine imposed</th>
            <th>Date on which fine recovery completed</th>
            <th>Signature/Thumb - impression of the employee</th>
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
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
            <td></td>
        </tr>
    </table>
</body>
</html>