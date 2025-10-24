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
$currentState = 'Punjab'; // Hardcoded for this state template

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
$location_code = $first_row['location_code'] ?? '';
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
    
    <style>
        body { 
           font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 10px;
            font-size: 12px;
            width: 100%;
            overflow: visible;
        }
        .form-header { 
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px;
            page-break-inside: avoid;
            table-layout: fixed;
        }
        th, td { 
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
            word-wrap: break-word;
            overflow: hidden; }
        th { 
            background-color: #ffffffff; 
        }
        .nill-row td {
            text-align: center;
        }
        .label-cell {
            font-weight: bold;
        }
        
    </style>
</head>
<body>
    <table>
  <thead>
    <tr>
      <th class="form-header" colspan="11">
        Form A<br>
        The Punjab Child Labour (Prohibition And Regulation) Rules, 1997 [See rule 4 (1)]<br>
        The Child Labour Register
      </th>
    </tr>

    <!-- Employer Info -->
    <tr>
      <th colspan="3" style="text-align: left;">Name and address of Establishment</th>
      <td colspan="8" style="text-align: left;"><?= htmlspecialchars($client_name . ' , ' . $branch_address) ?></td>
    </tr>
    <tr>
      <th colspan="3" style="text-align: left;">Name and address of employer</th>
      <td colspan="8" style="text-align: left;"><?= htmlspecialchars($employer_name . ' , ' . $employer_address) ?></td>
    </tr>
    <tr>
      <th colspan="3" style="text-align: left;">Nature of work being done by the establishment</th>
      <td colspan="8" style="text-align: left;"><?= $nature_of_business ?></td>
    </tr>
    <tr>
      <th colspan="3" style="text-align: left;">Month & Year</th>
      <td colspan="8" style="text-align: left;"><?= htmlspecialchars($month . ' - ' . $year) ?></td>
    </tr>
    <tr>
      <th colspan="3" style="text-align: left;">Place of work </th>
      <td colspan="8" style="text-align: left;"><?= htmlspecialchars( ($first_row['location_name'] ?? '')) ?></td>
    </tr>
    <tr>
      <th colspan="3" style="text-align: left;">Location Codes</th>
      <td colspan="8" style="text-align: left;"><?= htmlspecialchars($location_code) ?></td>
    </tr>

    <!-- Column Headers (in tbody) -->
    <tr>
      <th>Sl. No.</th>
      <th>Name of child</th>
      <th>Father's name</th>
      <th>Date of birth</th>
      <th>Permanent address</th>
      <th>Date of joining</th>
      <th>Name of work</th>
      <th>Daily hours</th>
      <th>Rest intervals</th>
      <th>Wage paid</th>
      <th>Remarks</th>
    </tr>

    <!-- NIL Row -->
    <tr>
      <td>1</td>
      <td>Nil</td>
      <td>Nil</td>
      <td>Nil</td>
      <td>Nil</td>
      <td>Nil</td>
      <td>Nil</td>
      <td>Nil</td>
      <td>Nil</td>
      <td>Nil</td>
      <td></td>
    </tr>
  </tbody>
</table>



</body>
</html>