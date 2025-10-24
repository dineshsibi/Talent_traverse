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
$currentState = 'Maharashtra'; // Hardcoded for this state template

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


    // Employer data with array handling
    $employer_name = safe($first_row['employer_name'] ?? '');
    $employer_address = safe($first_row['employer_address'] ?? '');
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
            text-align: center;
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
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="49" class="title">
            Form II <br>
            The Maharashtra Minimum Wages Rules, 1963 Rule 27(1) <br>
            Muster-roll-cum-wages Register																																															

            </th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: left;">Name and Address of the  Establishment / Shop </th>
            <td colspan="40" style="text-align: left;"><?= htmlspecialchars($client_name .' , '. $branch_address)?></td>
        </tr>
        <tr>
            <th colspan="9" style="text-align: left;">Name and address of the Employer</th>
            <td colspan="40" style="text-align: left;"><?= htmlspecialchars($employer_name .' , '. $employer_address)?></td>
        </tr>
        <tr>
            <th colspan="9" style="text-align: left;">Month & Year </th>
            <td colspan="40" style="text-align: left;"><?= htmlspecialchars($month .' - '. $year)?></td>
        </tr>
        <tr>
            <th colspan="16"></th>
            <td colspan="31" style="text-align: center;"><b>ATTENDANCE </b></td>
            <th rowspan="2"><b>Total days worked</b></th>
            <th rowspan="2"><b>Total hours worked during the Month</b></th>       
        </tr>
        
            
        
        <tr>
            <th>Sl No </th>
            <th>Employee Code </th>
            <th>Name of the Employee </th>
            <th>Age</th>
            <th>Sex</th>
            <th>Designation</th>
            <th>Date Of Entry</th>
            <th>Date of Birth</th>
            <th>Date Of Leaving</th>
            <th>PF No</th>
            <th>ESIC No</th>
            <th>UAN No</th>
            <th>Working Hours From</th>
            <th>Working Hours To</th>
            <th>Interval From</th>
            <th>Interval To</th>
            <th>Day 1</th>
            <th>Day 2</th>
            <th>Day 3</th>
            <th>Day 4</th>
            <th>Day 5</th>
            <th>Day 6</th>
            <th>Day 7</th>
            <th>Day 8</th>
            <th>Day 9</th>
            <th>Day 10</th>
            <th>Day 11</th>
            <th>Day 12</th>
            <th>Day 13</th>
            <th>Day 14</th>
            <th>Day 15</th>
            <th>Day 16</th>
            <th>Day 17</th>
            <th>Day 18</th>
            <th>Day 19</th>
            <th>Day 20</th>
            <th>Day 21</th>
            <th>Day 22</th>
            <th>Day 23</th>
            <th>Day 24</th>
            <th>Day 25</th>
            <th>Day 26</th>
            <th>Day 27</th>
            <th>Day 28</th>
            <th>Day 29</th>
            <th>Day 30</th>
            <th>Day 31</th>
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
            <th>32</th>
            <th>33</th>
            <th>34</th>
            <th>35</th>
            <th>36</th>
            <th>37</th>
            <th>38</th>
            <th>39</th>
            <th>40</th>
            <th>41</th>
            <th>42</th>
            <th>43</th>
            <th>44</th>
            <th>45</th>
            <th>46</th>
            <th>47</th>
            <th>48</th>
            <th>49</th>    
        </tr>
        <tbody>
            <?php if (!empty($stateData)): ?>
            <?php $i = 1; foreach ($stateData as $row): ?>
            <?php 
        // Calculate total hours worked
        $totalHours = 0;
        for ($day = 1; $day <= 31; $day++) {
            $dayField = 'day_' . $day;
            if (isset($row[$dayField])) {
                // Extract numeric value from the field (handles cases like "8" or "P8" or "8H")
                $dayValue = $row[$dayField];
                if (is_numeric($dayValue)) {
                    $totalHours += $dayValue;
                } else {
                    // Extract numbers from strings like "P8" or "8H"
                    preg_match('/(\d+)/', $dayValue, $matches);
                    if (!empty($matches)) {
                        $totalHours += (float)$matches[0];
                    }
                }
            }
        }
        ?>
        
        <?php
                $dob = $row['date_of_birth'] ?? '';
                $age = '';

                if (!empty($dob)) {
                    // Create DOB from your DB format (d-M-y, e.g., 20-Jan-04)
                    $dobDate = DateTime::createFromFormat('d-M-y', $dob);

                    if ($dobDate) {
                        // ✅ Get last day of the selected month & year
                        $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year); 
                        $referenceDate = DateTime::createFromFormat('Y-n-j', $year . '-' . $month . '-' . $lastDay);

                        if ($referenceDate) {
                            $age = $dobDate->diff($referenceDate)->y;
                        }
                    }
                }
        ?>
        
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['employee_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['employee_name'] ?? '') ?></td>
            <<td><?= htmlspecialchars($age) ?></td>
            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['designation'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_joining'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['date_of_leaving'] ?? '') ?></td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td colspan="4"><?= htmlspecialchars($row['shift_details'] ?? '') ?></td>
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
            <td><?= htmlspecialchars($row['total_worked_days'] ?? '') ?></td>
            <td><?= $totalHours ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="49" class="no-data">No contractor data available for Maharashtra</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>