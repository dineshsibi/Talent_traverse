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
$pdo = require($configPath);

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentLocation = $_SESSION['current_location'] ?? '';
$currentState = 'Maharashtra';

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM input 
            WHERE client_name = :client_name
            AND state LIKE :state
            AND location_code = :location_code";
    
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':client_name', $filters['client_name']);
    $stmt->bindValue(':state', "%$currentState%");
    $stmt->bindValue(':location_code', $currentLocation);
    
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $stmt->bindValue(':month', $filters['month']);
        $stmt->bindValue(':year', $filters['year']);
    }
    
    $stmt->execute();
    $allEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter employees who have ML in any of day_1 to day_31 fields
    $stateData = [];
    foreach ($allEmployees as $employee) {
        for ($i = 1; $i <= 31; $i++) {
            $dayField = 'day_' . $i;
            if (isset($employee[$dayField]) && strpos($employee[$dayField], 'ML') !== false) {
                $stateData[] = $employee;
                break;
            }
        }
    }
    
    // If no employees with ML found, create one empty record with month/year if available
    if (empty($stateData)) {
        $emptyRecord = array_fill_keys(array_keys($allEmployees[0] ?? []), '');
        if (!empty($filters['month']) || !empty($filters['year'])) {
            $emptyRecord['month'] = $filters['month'] ?? '';
            $emptyRecord['year'] = $filters['year'] ?? '';
        }
        $stateData = [$emptyRecord];
    }

    $first_row = !empty($stateData) ? reset($stateData) : [];

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');

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
            text-align: left;
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
        .col-number {
            font-weight: normal;
            font-size: 10px;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
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
    <table>
        <tr>
            <th colspan="3" class="title">Form 10<br>The Maternity Benefit Act, 1961 And (Maharashtra) Rules, 1965 [See Rule 12(1)]<br>Maternity Benefit Register</th>
        </tr>
        
        <tr>
            <th>1</th>
            <th style="text-align: left;">Employee Code </th>
            <td style="text-align: left;"><?= !empty($row['employee_code']) ? htmlspecialchars($row['employee_code']) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>1</th>
            <th style="text-align: left;">Name of the woman </th>
            <td style="text-align: left;"><?= !empty($row['employee_name']) ? htmlspecialchars($row['employee_name']) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>2</th>
            <th style="text-align: left;">Date of appointment </th>
            <td style="text-align: left;"><?= !empty($row['date_of_joining']) ? htmlspecialchars($row['date_of_joining']) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>3</th>
            <th style="text-align: left;">Department in which employed </th>
            <td style="text-align: left;"><?= !empty($row['department']) ? htmlspecialchars($row['department']) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>4</th>
            <th style="text-align: left;">Nature of work </th>
            <td style="text-align: left;"><?= !empty($row['designation']) ? htmlspecialchars($row['designation']) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>5</th>
            <th style="text-align: left;">Dates(with month and year) on which she is laid off and not employed </th>
            <td style="text-align: left;"><?= (!empty($row['month']) || !empty($row['year'])) ? htmlspecialchars(($row['month'] ?? '').' - '.($row['year'] ?? '')) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>6</th>
            <th style="text-align: left;">Total days employed in the: </th>
            <td style="text-align: left;"><?= !empty($row['total_worked_days']) ? htmlspecialchars($row['total_worked_days']) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>7</th>
            <th style="text-align: left;">Date on which woman gives payment period: Notice under section 6 of the Maternity Benefit Act,1961 </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>8</th>
            <th style="text-align: left;">Date of birth of child </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>9</th>
            <th style="text-align: left;">Date of production of proof of pregnancy under section 6 of the Maternity Act,1961 </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>10</th>
            <th style="text-align: left;">Date of production of proof of delivery/miscarriage/death </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>11</th>
            <th style="text-align: left;">Where the maternity benefit is paid in advance before delivery, the date on which it is paid and the amount thereof </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>12</th>
            <th style="text-align: left;">Date on which subsequent payment of maternity benefit is made and the amount thereof </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>13</th>
            <th style="text-align: left;">Where the medical bonus is paid, the date on which it is paid and amount thereof </th>
            <td style="text-align: left;"> NIL</td>
        </tr>
        <tr>
            <th>14</th>
            <th style="text-align: left;">Date on which wages on account of leave are paid and amount thereof </th>
            <td style="text-align: left;"><?= !empty($row['net_pay']) ? htmlspecialchars($row['net_pay']) : 'NIL' ?></td>
        </tr>
        <tr>
            <th>15</th>
            <th style="text-align: left;">Name of the person nominated by the woman </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>16</th>
            <th style="text-align: left;">If the woman dies, the date of her death, the name of the person to whom maternity benefit and/or other amount was paid, the amount thereof, and the date of payment </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>17</th>
            <th style="text-align: left;">If the woman dies and the child survives, the name of the person to whom the amount of maternity benefit was paid on behalf of the child and the period for which it was paid </th>
            <td style="text-align: left;">NIL</td>
        </tr>
        <tr>
            <th>18</th>
            <th style="text-align: left;">Remarks column for the use of Inspector </th>
            <td style="text-align: left;">NIL</td>
        </tr>
    </table>
    
    <?php 
    // Add page break except for last employee
    if ($currentEmployee < $totalEmployees): 
    ?>
    <div class="page-break"></div>
    <?php endif; ?>
    
    <?php endforeach; ?>
</body>
</html>