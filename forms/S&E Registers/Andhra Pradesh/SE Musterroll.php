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
$currentState = 'Andhra Pradesh'; // Hardcoded for this state template

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



    $location_name = $first_row['location_name'] ?? '';
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
        }
        .form-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 15px;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-header {
            margin-bottom: 20px;
        }
        .form-header div {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ffffff;
            text-align: center;
        }
        .address {
            margin-left: 20px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="44" style="text-align: center;">
                FORM - XXII<br>
               The Andhra Pradesh Shops and Establishments Act, 1988 and Rules, 1990 [ See Rule 29 (1) ]<br>
                REGISTER OF EMPLOYMENT
            </th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Name of the Establishment / Shop </th>
            <td colspan="36" style="text-align: left;"><?= htmlspecialchars($client_name)?></td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Address</th>
            <td colspan="36" style="text-align: left;"><?= htmlspecialchars($branch_address)?></td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Registration No </th>
            <td colspan="36" style="text-align: left;">-</td>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">For the month of</th>
            <td colspan="36" style="text-align: left;"><?= htmlspecialchars($month .' - ' . $year)?></td>
        </tr>
        <tr>
            <th rowspan="2">Sl No</th>
            <th rowspan="2">Employee code</th>
            <th rowspan="2">Name of the Employee</th>
            <th rowspan="2">Sex</th>
            <th rowspan="2">Age</th>
            <th rowspan="2">Time at which employment commernces</th>
            <th rowspan="2">Time at which employment ceases</th>
            <th rowspan="2">Rest intervals</th>
            <th colspan="31">Days of Months</th>
            <th colspan="4">Days on which overtime is done and extent of such overtime work in each day 7</th>
            <th rowspan="2">Remarks</th>
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
            <th>23</th>
            <th>24</th>
            <th>25</th>
            <th>26</th>
            <th>27</th>
            <th>28</th>
            <th>29</th>
            <th>30</th>
            <th>31</th>
            <th>Date</th>
            <th>From</th>
            <th>To</th>
            <th>Extent</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>a</th>
            <th>b</th>
            <th>c</th>
            <th colspan="31"></th>
            <th>a</th>
            <th>b</th>
            <th>c</th>
            <th>d</th>
            <th>8</th>
        </tr>
        <tbody> <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
            <td colspan="3"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['day_1'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_2'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_3'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_4'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_5'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_6'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_7'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_8'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_9'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_10'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_11'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_12'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_13'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_14'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_15'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_16'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_17'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_18'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_19'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_20'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_21'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_22'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_23'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_24'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_25'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_26'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_27'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_28'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_29'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_30'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['day_31'] ?? '') ?></td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td>NIL</td>
            <td></td>
           
        </tr>
        <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No data available for Andhra Pradesh</td>
                </tr>
            <?php endif; ?>
    </tbody>
    </table>
</body>
</html>