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
$currentState = 'Telangana';

try {
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
        .left-align {
            text-align: left;
        }
    </style>
</head>
<body>
    <table>
        <!-- Title Row -->
        <tr>
            <th colspan="11" class="title">
                FORM - XI<br>
                The Andhra Pradesh Shops and Establishments Act, 1988 and Rules, 1990 [See Rule 17(4)]<br>
                REGISTER OF DEDUCTIONS FOR THE DAMAGE OR LOSS CAUSED TO THE EMPLOYER BY THE<br>
                NEGLECT OR DEFAULT OF EMPLOYEES
            </th>
        </tr>
        
        <!-- Establishment Information -->
        <tr>
            <th colspan="5" class="left-align">Name and address of the Establishment :</th>
            <td colspan="6" class="left-align"><?= htmlspecialchars($client_name . ' , '. ($first_row['branch_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <th colspan="5" class="left-align">Month & Year :</th>
            <td colspan="6" class="left-align"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        
        <!-- Column Headers -->
        <tr>
            <th rowspan="2">SL No:</th>
            <th rowspan="2">Employee ID</th>
            <th rowspan="2">Name of the Employee</th>
            <th rowspan="2">Father's/Husband's name</th>
            <th rowspan="2">Damage or loss caused</th>
            <th rowspan="2">Whether worker showed cause against deduction or not and if so, date on which cause was shown</th>
            <th rowspan="2">Amount of deduction imposed</th>
            <th rowspan="2">Date on which deduction imposed</th>
            <th rowspan="2">No. of instalments if any</th>
            <th rowspan="2">Date on which total amount realised</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <!-- Empty row for numbered columns -->
        </tr>
        
        <!-- Numbered Columns -->
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
        </tr>
        
        <!-- Sample Data Row -->
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
            <td></td>
        </tr>
    </table>
</body>
</html>