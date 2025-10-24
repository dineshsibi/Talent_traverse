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
$currentState = 'Rajastan'; // Hardcoded for this state template

try {
    // First get all employees who have ML in any day field
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
    $allEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter employees who have ML in any day field
    $mlEmployees = [];
    foreach ($allEmployees as $employee) {
        for ($i = 1; $i <= 31; $i++) {
            $dayField = 'day_' . $i;
            if (isset($employee[$dayField]) && strpos($employee[$dayField], 'ML') !== false) {
                $mlEmployees[] = $employee;
                break; // No need to check other days if we found at least one ML
            }
        }
    }

    $first_row = !empty($allEmployees) ? reset($allEmployees) : [];

    $client_name = safe($filters['client_name'] ?? '');
    $month = safe($filters['month'] ?? '');
    $year = safe($filters['year'] ?? '');
    $branch_address = $first_row['branch_address'] ?? '';
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// If no employees with ML, show a single form with Nil values
if (empty($mlEmployees)) {
    $mlEmployees = [null]; // Dummy entry to show one form
}
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .header {
            font-weight: bold;
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <?php foreach ($mlEmployees as $index => $row): ?>
        <table>
            <tr>
                <th colspan="15" style="text-align: center;">
                    The Rajasthan Shops and Commercial Establishments Rules, 1959 (See Rule 20)<br>
                    Muster Roll for Woman employee
                </th>
            </tr>
            <tr>
                <td class="bold" colspan="9">Name & Address of the Establishment</td>
                <td colspan="6" style='text-align:left'><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <td class="bold" colspan="9">Month&Year</td>
                <td colspan="6" style='text-align:left'><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <td class="bold">A</td>
                <td class="bold" colspan="8">Name of Woman</td>
                <td colspan="6"><?= $row ? htmlspecialchars($row['employee_name'] ?? '') : 'Nil' ?></td>
            </tr>
            <tr>
                <td class="bold">B</td>
                <td class="bold" colspan="8">Department and name of the establishment in which employed</td>
                <td colspan="6"><?= $row ? htmlspecialchars($row['department'] ?? '') : 'Nil' ?></td>
            </tr>
            <tr>
                <td class="bold">C</td>
                <td class="bold" colspan="8">Dates with month and year on which employed and not employed</td>
                <td colspan="6"><?= $row ? htmlspecialchars(($row['month'] ?? '') . ' - ' . ($row['year'] ?? '')) : 'Nil' ?></td>
            </tr>
            <tr>
                <td class="bold">D</td>
                <td class="bold" colspan="8">Total days employed in the payment period</td>
                <td colspan="6">
                    <?php
                    if ($row) {
                        $mlCount = 0;
                        for ($i = 1; $i <= 31; $i++) {
                            $dayField = 'day_' . $i;
                            if (!empty($row[$dayField]) && strpos($row[$dayField], 'ML') !== false) {
                                $mlCount++;
                            }
                        }
                        echo $mlCount > 0 ? $mlCount : 'Nil';
                    } else {
                        echo 'Nil';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="bold">E</td>
                <td class="bold" colspan="8">Date on which the woman gives notice under section 24 of the Act</td>
                <td colspan="6">Nil</td>
            </tr>
            <tr>
                <td class="bold">F</td>
                <td class="bold" colspan="8">Date of birth of child</td>
                <td colspan="6">Nil</td>
            </tr>
            <tr>
                <td class="bold">G</td>
                <td class="bold" colspan="8">Date of production of certificate signed by a registered Medical Practitioner certifying that</td>
                <td colspan="6">Nil</td>
            </tr>
            <tr>
                <td class="bold">H</td>
                <td class="bold" colspan="8">Date of production of certified extract from birth register</td>
                <td colspan="6">Nil</td>
            </tr>
            <tr>
                <td class="bold">I</td>
                <td class="bold" colspan="8">Date of first payment of maternity benefit and amount of the same</td>
                <td colspan="6">Nil</td>
            </tr>
            <tr>
                <td class="bold">J</td>
                <td class="bold" colspan="8">Date of subsequent payments of maternity benefit and amounts of the same</td>
                <td colspan="6">Nil</td>
            </tr>
            <tr>
                <td class="bold">K</td>
                <td class="bold" colspan="8">If the woman dies, amount of maternity benefit paid and date of payment and the names of persons to whom paid</td>
                <td colspan="6">Nil</td>
            </tr>
            <tr>
                <td class="bold">L</td>
                <td class="bold" colspan="8">Remarks column for the use of Inspector only</td>
                <td colspan="6">Nil</td>
            </tr>
        </table>

        <?php if ($index < count($mlEmployees) - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</body>

</html>