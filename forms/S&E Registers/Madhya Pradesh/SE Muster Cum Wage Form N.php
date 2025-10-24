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
$currentState = 'Madhya Pradesh'; // Hardcoded for this state template

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


    // Get one sample row to extract address
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
            margin: 0;
            padding: 20px;
        }
        .form-container {
            width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 12px;
            word-wrap: break-word;
        }
        th {
            background-color: #ffffff;
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .header-info {
            text-align: left;
            padding-left: 10px;
        }
        .rotate {
            transform: rotate(-90deg);
            transform-origin: left top 0;
            white-space: nowrap;
            display: block;
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 100px;
            height: 20px;
        }
        .vertical-header {
            position: relative;
            height: 120px;
        }
        .vertical-header span {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%) rotate(-90deg);
            transform-origin: center;
            white-space: nowrap;
        }
        @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <?php
    $totalEmployees = count($stateData);
    $currentEmployee = 0;

    foreach ($stateData as $row):
        $currentEmployee++;
        ?>
        <?php
                $dob = $row['date_of_birth'] ?? '';
                $age = '';

                if (!empty($dob)) {
                    // Create DOB from your DB format (d-M-y, e.g., 20-Jan-04)
                    $dobDate = DateTime::createFromFormat('d-M-y', $dob);

                    if ($dobDate) {
                        // ✅ Get last day of the selected month & year
                        $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year); 
                        $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);

                        if ($referenceDate) {
                            $age = $dobDate->diff($referenceDate)->y;
                        }
                    }
                }
            ?>
        
    <table>
        <tr>
            <th colspan="22" class="title">
            Form N <br>
            The Madhya Pradesh Shops and Establishment Act, 1958 and Rules, 1959 [See Rules 20 (1) and 21 (2)] <br>
            Register of Employees Attendance, Wages, Overtime, Fine or Other Deductions etc.																					

            </th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name and/or the address of the Establishment</th>
            <td colspan="14"><?= htmlspecialchars($client_name . ' , '. ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th>1</th>
            <th colspan="7" style="text-align: left;">Employee Code</th>
            <td colspan="14"><?= htmlspecialchars($first_row['employee_code'] ?? '') ?></td>
        </tr>
        <tr>
            <th>2</th>
            <th colspan="7" style="text-align: left;">Name of Employee</th>
            <td colspan="14"><?= htmlspecialchars($first_row['employee_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th>3</th>
            <th colspan="7" style="text-align: left;">Father's/Husband's name</th>
            <td colspan="14"><?= htmlspecialchars($first_row['father_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th>4</th>
            <th colspan="7" style="text-align: left;">Age</th>
            <td colspan="14"><?= htmlspecialchars($age) ?></td>
        </tr>
        <tr>
            <th>5</th>
            <th colspan="7" style="text-align: left;">Address of the Employer</th>
            <td colspan="14"><?= htmlspecialchars($first_row['present_address'] ?? '') ?></td>
        </tr>
        <tr>
            <th>6</th>
            <th colspan="7" style="text-align: left;">Nature of Employment</th>
            <td colspan="14"><?= htmlspecialchars($first_row['nature_of_business'] ?? '') ?></td>
        </tr>
        <tr>
            <th>7</th>
            <th colspan="21" style="text-align: left;">Rate of wages (State whether daily, monthly or piece rated)</th>
        </tr>
        <tr>
            <th>8</th>
            <th colspan="7" style="text-align: left;">Wage period</th>
            <td colspan="14"><?= htmlspecialchars($month . ' - ' . $year)?></td>
        </tr>
        <tr>
            <th rowspan="2">Sl. No</th>
            <th rowspan="2">Date</th>
            <th rowspan="2">Time at which employment commenced</th>
            <th colspan="2">Intervals for rest or meals if any</th>
            <th rowspan="2">Time at which employment ceased</th>
            <th colspan="3">Over-time work if any</th>
            <th colspan="3">Wages Earned</th>
            <th rowspan="2">Total</th>
            <th colspan="3">Advance Amount Advanced</th>
            <th rowspan="2">Balance</th>
            <th rowspan="2">Fine (Details to be given in remarks col.)</th>
            <th rowspan="2">Deductions (Details to be given in remarks col.)</th>
            <th rowspan="2">Net amount</th>
            <th rowspan="2">Signature or thumb impression of employee</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>From</th>
            <th>To</th>
            <th>From Hours</th>
            <th>To Hours</th>
            <th>OT Worked in Hrs.</th>
            <th>Basic</th>
            <th>DA</th>
            <th>OT</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Amount recovered</th>
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
        </tr>
        <tr>
            <td>1</td>
            <td><?= htmlspecialchars($month . ' - ' . $year)?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td colspan="2"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
            <td colspan="3"><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['basic'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['da'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['over_time_allowance'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gross_wages'] ?? '') ?></td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td><?= htmlspecialchars($row['fines_damage_or_loss'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['total_deduction'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['net_pay'] ?? '') ?></td>
            <td></td>
            <td></td>
        </tr>
        <!-- Additional rows can be added here as needed -->
    </table>
    <?php 
    // Add page break after each employee except the last one
    if ($currentEmployee < $totalEmployees) {
        echo '<div class="page-break"></div>';
    }
    ?>
    
    <?php endforeach; ?>
</body>
</html>