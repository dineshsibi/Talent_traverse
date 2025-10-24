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
$currentPrincipal = $_SESSION['current_principal'] ?? '';
$currentState = $_SESSION['current_state'] ?? 'Delhi';
$currentLocation = $_SESSION['current_location'] ?? '';

try {
    // Build the SQL query with parameters
    $sql = "SELECT * FROM combined_data 
        WHERE client_name = :client_name
        AND principal_employer_name = :principal_employer
        AND state LIKE :state
        AND location_code = :location_code
        AND month = :month 
        AND year = :year";
    
    // Add month/year filter if specified
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $sql .= " AND month = :month AND year = :year";
    }
    
    // Prepare and execute query
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
   $stmt->bindValue(':client_name', $filters['client_name']);
    $stmt->bindValue(':principal_employer', $currentPrincipal);
    $stmt->bindValue(':state', "%$currentState%");
    $stmt->bindValue(':location_code', $currentLocation);
    
    if (!empty($filters['month']) && !empty($filters['year'])) {
        $stmt->bindValue(':month', $filters['month']);
        $stmt->bindValue(':year', $filters['year']);
    }
    
    $stmt->execute();
    $stateData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $first_row = !empty($stateData) ? reset($stateData) : [];

    // Safe output variables
    $client_name = safe($filters['client_name'] ?? '');
    $branch_address = $first_row['address'] ?? '';
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
    $principal_employer = safe($currentPrincipal);
    $principal_employer_address = $first_row['principal_employer_address'] ?? '';
    $location_code = $first_row['location_code'] ?? '';
    $nature_of_business = $first_row['nature_of_business'] ?? '';
    $employee_name = $first_row['employee_name'] ?? '';
    $father_name = $first_row['father_name'] ?? '';

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
        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .sub-title {
            text-align: center;
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
            background-color: #ffffff;
        }
        .section-title {
            font-weight: bold;
            margin-top: 15px;
        }
        *{
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
            <th class="form-title" colspan="25">Form G<br>
                                (See Rule 14)<br>
                    REGISTER OF WAGES / DEDUCTIONS / OVERTIME/ ADVANCES<br>
                            For the month APRIL-2025
            </th>
            </tr>
            <tr>
                <th colspan="10">Name of the Establishment /Shop</th>
                <td colspan="15"><?= htmlspecialchars($client_name) ?></td>
            </tr>
            <tr>
                <th colspan="10">Address of Establishment /Shop</th>
                <td colspan="15"><?= htmlspecialchars($branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="10">Nature of Establishment / Manufacturing Process /Business etc</th>
                <td colspan="15"><?= htmlspecialchars($nature_of_business) ?></td>
            </tr>
            <tr>
                <th>Sl. No.</th>
                <th>Name of the employee (ID/Token No. if any)</th>
                <th>Age/Date of Birth</th>
                <th>Address</th>
                <th>Education/Skill</th>
                <th>Sex (M/F)</th>
                <th>Father's/husband's Name</th>
                <th>Designation/category /nature of work performed</th>
                <th>Total No. of days worked</th>
                <th>Category of Leave</th>
                <th>Leaves availed (No. of days)</th>
                <th>Leaves Rejected</th>
                <th>Total Balance Leaves</th>
                <th>Other allowances</th>
                <th>Overtime worked (Number of hours in the month)</th>
                <th>Amount of over time wages</th>
                <th>Any other amount (Please mention)</th>
                <th>Total/gross wages /earnings</th>
                <th>Amount of advances/ loans if any and purpose of advance</th>
                <th>Deductions of fines imposed. If any.</th>
                <th>Other deductions like EPF /ESI /Welfare. Fund etc.(if any)</th>
                <th>Net amount payable</th>
                <th>Signature /thumb impression</th>
                <th>Date of termination and reason</th>
                <th>Remarks, if any</th>
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
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
                <?php
                $FINE = (float)($row['fine'] ?? 0);
                $ADVANCE = (float)($row['advance_loan'] ?? 0);
                $DEDUCTIONS = (float)($row['total_deductions'] ?? 0);
                $contribution_deduction = $FINE+$ADVANCE-$DEDUCTIONS;                       
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
                <td>-</td>
                <td><?= htmlspecialchars($row['education_level'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_present_days'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['pl_availed'] ?? '') ?></td>
                <td>Nil</td>
                <td><?= htmlspecialchars($row['pl_closing'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['other_allowance'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['ot_hours'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['overtime_rate_of_wages'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['other_allowance'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['whether_worker_showed_cause_against_deduction'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['advance_loan'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['fine'] ?? '') ?></td>
                <td><?= htmlspecialchars($contribution_deduction ?? '') ?></td>
                <td><?= htmlspecialchars($row['net_salary'] ?? '') ?></td>
                <td></td>
                <td><?= htmlspecialchars($row['date_of_resign'] ?? ''). ' , ' .($row['reason_for_exit'] ?? '')?></td>
                <td></td>
            </tr>
            
            <?php endforeach; ?>
                <?php else: ?>
            <tr>
                <td colspan="25" style="text-align:center;">No data available for Delhi</td>
            </tr>
            <?php endif; ?>
            <tr>
                <th class="signatory" colspan="25" style="text-align: right;">Signature of the employer / occupier/manager Name of signatory</th>
            </tr>
        </tbody>
    </table>
</body>
</html>