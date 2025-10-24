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
$currentState = 'Tripura'; // Hardcoded for this state template

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
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
        }
        .form-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
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
            background-color: #ffffffff;
        }
        .empty-row {
            height: 30px;
        }
        .label-cell {
            font-weight: bold;
        }
         * {
            font-family: "Times New Roman", Times, serif;
        }
        .page-break {
            page-break-after: always;
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

        // Get the month number from month name
        $monthNum = date('m', strtotime($month));
        
        // Process leave data to find PL, SL, and CL days
        $plDays = [];
        $slDays = [];
        $clDays = [];
        $mlDays = [];
        
        // Check all day fields (day_1 to day_31) for leave types
        for ($day = 1; $day <= 31; $day++) {
            $dayKey = 'day_' . $day;
            if (isset($row[$dayKey])) {
                if ($row[$dayKey] == 'PL') {
                    $plDays[] = $day;
                } elseif ($row[$dayKey] == 'SL') {
                    $slDays[] = $day;
                } elseif ($row[$dayKey] == 'CL') {
                    $clDays[] = $day;
                } elseif ($row[$dayKey] == 'ML') {
                    $mlDays[] = $day;
                }    
            }
        }
        
        // Format days for display (e.g., "5,6")
        $plDaysDisplay = !empty($plDays) ? implode(',', $plDays) : '';
        $slDaysDisplay = !empty($slDays) ? implode(',', $slDays) : '';
        $clDaysDisplay = !empty($clDays) ? implode(',', $clDays) : '';
        $mlDaysDisplay = !empty($mlDays) ? implode(',', $mlDays) : '';
        
        // Calculate leave counts
        $plCount = count($plDays);
        $slCount = count($slDays);
        $clCount = count($clDays);
        $mlCount = count($mlDays);
        ?>
    <table>
        <tr>
            <td colspan="10" class="form-header">
                FORM J <br>
                The Tripura Shops and Establishments Rules, 1970, (See sub-rule (2) of Rule 18 and rule 21) <br>
                REGISTER OF LEAVE
            </td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of Shop /Establishment, if any</th>
            <td colspan="6"><?= htmlspecialchars($first_row['client_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of the Shopkeeper/Employer</th>
            <td colspan="6"><?= htmlspecialchars($first_row['employer_name'] ?? '') ?></td>    
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Address in full of shop/establishment:</th>
            <td colspan="6"><?= htmlspecialchars($first_row['branch_address'] ?? '') ?></td>    
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Registration No:</th>
            <td colspan="6">-</td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Name of the Employee</th>
            <td colspan="6"><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Father's/Husband's Name</th>
            <td colspan="6"><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="4" style="text-align: left;">Date of appointment</th>
            <td colspan="6"><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center;">PRIVILEGE LEAVE</th>
        </tr>
        <tr>
            <th rowspan="2">Date of Application</th>
            <th colspan="2">Applied</th>
            <th colspan="2">Leave granted</th>
            <th rowspan="2">Balance Due</th>
            <th colspan="3">If refused, in part or full</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>Reasons</th>
        </tr>
        <tr>
            <th>1</th>
            <th colspan="2">2</th>
            <th colspan="2">3</th>
            <th>4</th>
            <th colspan="3">5</th>
            <th>6</th>
        </tr>
        <tr>
            <td><?= $plDaysDisplay ?></td>
            <td colspan="2"><?= $plDaysDisplay ?></td>
            <td colspan="2"><?= $plDaysDisplay ?></td>
            <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
            <td colspan="2">Nil</td>
            <td>Nil</td>
            <td></td>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center;">SICK LEAVE</th>
        </tr>
        <tr>
            <th rowspan="2">Date of Application</th>
            <th colspan="2">Applied</th>
            <th colspan="2">Leave granted</th>
            <th rowspan="2">Balance Due</th>
            <th colspan="3">If refused, in part or full</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>Reasons</th>
        </tr>
        <tr>
            <th>1</th>
            <th colspan="2">2</th>
            <th colspan="2">3</th>
            <th>4</th>
            <th colspan="3">5</th>
            <th>6</th>
        </tr>
        <tr>
            <td><?= $slDaysDisplay ?></td>
            <td colspan="2"><?= $slDaysDisplay ?></td>
            <td colspan="2"><?= $slDaysDisplay ?></td>
            <td><?= htmlspecialchars($row['sl_closing'] ?? '') ?></td>
            <td colspan="2">Nil</td>
            <td>Nil</td>
            <td></td>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center;">CASUAL LEAVE</th>
        </tr>
        <tr>
            <th rowspan="2">Date of Application</th>
            <th colspan="2">Applied</th>
            <th colspan="2">Leave granted</th>
            <th rowspan="2">Balance Due</th>
            <th colspan="3">If refused, in part or full</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>From (Date)</th>
            <th>To (Date)</th>
            <th>Reasons</th>
        </tr>
        <tr>
            <th>1</th>
            <th colspan="2">2</th>
            <th colspan="2">3</th>
            <th>4</th>
            <th colspan="3">5</th>
            <th>6</th>
        </tr>
        <tr>
            <td><?= $clDaysDisplay ?></td>
            <td colspan="2"><?= $clDaysDisplay ?></td>
            <td colspan="2"><?= $clDaysDisplay ?></td>
            <td><?= htmlspecialchars($row['cl_closing'] ?? '') ?></td>
            <td colspan="2">Nil</td>
            <td>Nil</td>
            <td></td>
        </tr>
        <tr>
            <th colspan="10" style="text-align:right;">Signature of Employer/Shop Keeper</th>
        </tr>
    </table>
    <?php
    // Only add page break if it's not the last employee
    if ($currentEmployee < $totalEmployees): ?>
        <div class="page-break"></div>
    <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>