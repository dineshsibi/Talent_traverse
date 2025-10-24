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
$currentState = $_SESSION['current_state'] ?? 'Punjab';
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
            margin-bottom: 20px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Force equal column distribution */
            font-size: 10px;
            /* Reduce font size for fitting */
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
            background-color: #ffffff;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }

        .signatory {
            text-align: right;
            margin-top: 30px;
        }

        .info-content {
            display: inline-block;
            width: calc(100% - 410px);
        }

        * {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <td class="form-title" colspan="31">SCHEDULE <br>
                    [See rule 2(1)] <br>
                    FORM A <br>
                    FORMAT OF EMPLOYEE REGISTER <br>
                    [Part-A: For all Establishments]
                </td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Name and address of Contractor</th>
                <td colspan="16" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Nature and location of work</th>
                <td colspan="16" style="text-align: left;"><?= htmlspecialchars($location_code . ' , ' . $nature_of_business) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Name and address of establishment in/under which contract is carried on</th>
                <td colspan="16" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">Name and address of Principal Employer</th>
                <td colspan="16" style="text-align: left;"><?= htmlspecialchars($principal_employer . ' , ' . $principal_employer_address) ?></td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">LIN</th>
                <td colspan="16">-</td>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left;">For the Year</th>
                <td colspan="16" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
            </tr>
            <tr>
                <th>Sl No</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Gender</th>
                <th>Father's/Spouse</th>
                <th>Date of Birth</th>
                <th>Nationality</th>
                <th>Education Level</th>
                <th>Date of Joining</th>
                <th>Designation</th>
                <th>Category Address *(HS/S/SS/US)</th>
                <th>Type of Employment</th>
                <th>Mobile</th>
                <th>UAN</th>
                <th>PAN</th>
                <th>ESIC IP</th>
                <th>LWF</th>
                <th>AADHAAR</th>
                <th>Bank A/c Number</th>
                <th>Payment Mode</th>
                <th>Branch (IFSC)</th>
                <th>Present Address</th>
                <th>Permanent Address</th>
                <th>Servie Book No.</th>
                <th>Date of Exit</th>
                <th>Reason for Exit</th>
                <th>Mark of Identification</th>
                <th>Photo</th>
                <th>Specimen Signature/ Thumb Impression</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stateData)): ?>
                <?php $i = 1;
                foreach ($stateData as $row): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
                        <td>-</td>
                        <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nationality'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['education_level'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                        <td>Contractor</td>
                        <td></td>
                        <td><?= htmlspecialchars($row['uan_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['pan_no'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['ip'] ?? '') ?></td>
                        <td></td>
                        <td><?= htmlspecialchars($row['aadhaar'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['bank_account_number'] ?? '') ?></td>
                        <td>Bank Transfer</td>
                        <td><?= htmlspecialchars($row['ifsc_code'] ?? '') ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td><?= htmlspecialchars($row['date_of_resign'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['reason_for_exit'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['identification_marks'] ?? '') ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="19" style="text-align:center;">No data available for Punjab</td>
                </tr>
            <?php endif; ?>
            <tr>
                <th class="signatory" style="text-align: right;" colspan="31">Authorised Signatory</th>
            </tr>
        </tbody>
    </table>
</body>

</html>