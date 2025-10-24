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
$currentState = $_SESSION['current_state'] ?? 'Telangana';
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

 // Get one sample row to extract address
    $branch_address = $first_row['branch_address'] ?? '';
    $employer_name = $first_row['employer_name'] ?? '';

    // Calculate total male and female employees
    $totalMale = 0;
    $totalFemale = 0;
    
    foreach ($stateData as $row) {
        if (isset($row['gender'])) {
            if (strtolower($row['gender']) === 'male') {
                $totalMale++;
            } elseif (strtolower($row['gender']) === 'female') {
                $totalFemale++;
            }
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid black;
            padding: 3px 5px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        th {
            font-weight: bold;
        }
        .title {
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        .section-title {
            font-weight: bold;
        }
        .sub-item {
            padding-left: 15px;
            text-align: left;
        }
        .center {
            text-align: center;
        }
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <table>
        <!-- Title Row -->
        <tr>
            <th colspan="12" class="title">Form – II<br>INTEGRATED REGISTER</th>
        </tr>
        
        <!-- Month -->
        <tr>
            <th colspan="2">For the month:</th>
            <td colspan="10"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
        </tr>
        
        <!-- Section 1 -->
        <tr>
            <th colspan="12" class="section-title">(1) Details of Establishment</th>
        </tr>
        <tr>
            <th colspan="2" class="sub-item">a. Name of the Establishment:</th>
            <td colspan="10"><?= htmlspecialchars($first_row['client_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" class="sub-item">b. Address:</th>
            <td colspan="10"><?= htmlspecialchars($first_row['branch_address'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" class="sub-item">c. Telephone no(s):</th>
            <td colspan="10">-</td>
        </tr>
        <tr>
            <th colspan="2" class="sub-item">d. Fax no(s):</th>
            <td colspan="10">-</td>
        </tr>
        <tr>
            <th colspan="2" class="sub-item">e. Mobile No:</th>
            <td colspan="10">-</td>
        </tr>
        
        <!-- Section 2 -->
        <tr>
            <th colspan="2" class="sub-item">(2)a. Nature of business:</th>
            <td colspan="10"><?= htmlspecialchars($first_row['nature_of_business'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" class="sub-item">b. Location of work:</th>
            <td colspan="10"><?= htmlspecialchars($first_row['location_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th colspan="2" class="sub-item">c. Minimum Wages GO. Applicable to the unit:</th>
            <td colspan="10"></td>
        </tr>
        
        <!-- Section 3 -->
        <tr>
            <th colspan="2" class="section-title">(3) Name and address of Employer/Principal Employer (in case of Contractor):</th>
            <td colspan="10">Nil</td>       
        </tr>
        
        <!-- Section 4 -->
        <tr>
            <th colspan="2" class="section-title">(4) Name of Contractor / Contractors engaged:</th>
            <td colspan="10">Nil</td>
        </tr>
        
        <!-- Section 5 -->
        <tr>
            <td colspan="2" class="section-title">(5) Registration / License No. and Date of Registrations / Licenses issued /Renewal under various Labour Laws (Mention Act wise details):-</td>
       
           <th colspan="2">S&E RC No. and Date of Registrations</th>
            <td colspan="2">-</td>

            <th colspan="3">CLRA RC No. and Issued Date</th>
            <td colspan="3">Not Applicable</td>
        </tr>
        
        <!-- Section 6 -->
        <tr>
            <td colspan="2" class="section-title">(6) No. of Workers:</td>

            <th colspan="2">Regular:</th>
            <td colspan="2"><?= $totalMale + $totalFemale ?></td>

            <th colspan="3">Contract:</th>
            <td colspan="3">Nil</td>
        </tr>
        
        <!-- Category Wise -->
        <tr>
            <td colspan="12" class="section-title">(i) Category Wise No. of Workers</td>
        </tr>
        <tr>
            <th colspan="2">Permanent</th>
            <th colspan="2">Temporary</th>
            <th colspan="2">Trainee</th>
            <th colspan="2">Apprentice</th>
            <th colspan="2">Contract</th>
            <th colspan="2">Total</th>
        </tr>
        <tr class="center">
            <th colspan="2">(1)</th>
            <th colspan="2">(2)</th>
            <th colspan="2">(3)</th>
            <th colspan="2">(4)</th>
            <th colspan="2">(5)</th>
            <th colspan="2">(6)</th>
        </tr>
        <tr>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
        </tr>
        <tr>
            <td><?= $totalMale ?></td>
            <td><?= $totalFemale ?></td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td><?= $totalMale ?></td>
            <td><?= $totalFemale ?></td>
        </tr>
        
        <!-- Class Wise -->
        <tr>
            <td colspan="12" class="section-title">(ii) Class Wise No. of Workers</td>
        </tr>
        <tr>
            <th colspan="2">Highly Skilled</th>
            <th colspan="2">Skilled</th>
            <th colspan="2">Semi-Skilled</th>
            <th colspan="2">Unskilled</th>
            <th colspan="4">Total</th>
        </tr>
        <tr class="center">
            <th colspan="2">(1)</th>
            <th colspan="2">(2)</th>
            <th colspan="2">(3)</th>
            <th colspan="2">(4)</th>
            <th colspan="4">(5)</th>
        </tr>
        <tr>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th>Male</th>
            <th>Female</th>
            <th colspan="2">Male</th>
            <th colspan="2">Female</th>
        </tr>
        <tr>
            <td>Nil</td>
            <td>Nil</td>
            <td><?= $totalMale ?></td>
            <td><?= $totalFemale ?></td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td>Nil</td>
            <td colspan="2"><?= $totalMale ?></td>
            <td colspan="2"><?= $totalFemale ?></td>
        </tr>
        
        <!-- Adolescents -->
        <tr>
            <td colspan="2" class="section-title">(iii) Adolescents (14 to 18 years)</td>
            <td colspan="10">Nil</td>
        </tr>
        
        <!-- Section 7 -->
        <tr>
            <td colspan="2" class="section-title">7. Date of Cleaning / White Washing:</td>
            <td colspan="10">Every Six month Once</td>
        </tr>
        <!-- Section 8 -->
        <tr>
            <td colspan="2" class="section-title">8. Date of Inspection under Various Labour Laws:</td>
            <td colspan="10">Nil</td>
        </tr>
        <!-- Section 10 -->
        <tr>
            <td colspan="2" class="section-title">10. Date and Time of Accident (if any)</td>
            <td colspan="10">Nil</td>
        </tr>
        <!-- Section 11 -->
        <tr>
            <td colspan="2" class="section-title">11. No. of Workers injured in the Accident (if any):</td>
             <td colspan="10">Nil</td>
        </tr>
        <!-- Section 12 -->
        <tr>
            <td colspan="2" class="section-title">12. No. of Workers died in the Accident (if any):</td>
            <td colspan="10">Nil</td>
        </tr>
    </table>
</body>
</html>