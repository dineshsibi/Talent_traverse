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
$pdo = require($configPath);

// Verify filter criteria exists
if (!isset($_SESSION['filter_criteria'])) {
    die("No filter criteria found. Please start from the search page.");
}

$filters = $_SESSION['filter_criteria'];
$currentPrincipal = $_SESSION['current_principal'] ?? '';
$currentState = $_SESSION['current_state'] ?? 'Gujarat';
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
            background-color: white;
            margin: 0;
            padding: 20px;
            color: #000;
            line-height: 1.4;
        }

        .form-container {
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid white;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }


        .form-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 20px;
            padding: 5px;
        }

        .form-subtitle {
            text-align: center;
            margin-bottom: 20px;
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* keeps columns evenly spread */
            font-size: 12px;
            /* adjust font size so it doesn’t look too tiny */
        }


        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            word-wrap: break-word;
            /* Wrap long text */
        }

        th {
            text-align: left;
            background-color: white;
        }

        .header-row {
            background-color: white;
            font-weight: bold;
            text-align: center;
        }

        @media print {
            body {
                padding: 0;
            }

            .form-container {
                border: none;
                box-shadow: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <table>
            <tr class="header-row">
                <td colspan="7">
                    <div class="form-title">FORM XII <br>
                    (See Rule 74) <br>
                    Register of Contractors
                </td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Name and address of the Principal Employer</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Name and address of the establishment</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left;">Month & Year</th>
                <td colspan="5" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th rowspan="2">Serial No</th>
                <th rowspan="2">Name and address of contractor</th>
                <th rowspan="2">Nature of work on contractor</th>
                <th rowspan="2">Location of contract work</th>
                <th colspan="2">Period of contract</th>
                <th rowspan="2">Maximum No. of workmen employed by contractor</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
            </tr>
            <tr>
                <td>1</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
                <td>NIL</td>
            </tr>
        </table>
    </div>
</body>
</html>