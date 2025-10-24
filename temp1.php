<?php
session_start();

// Include config which returns a PDO connection
$pdo = include("includes/config.php");

$clientName = "";
$principalEmployers = [];
$states = [];
$locationCodes = [];
$monthYear = "";
$month = $year = null;

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle AJAX requests for dependent dropdowns
    if (isset($_GET['ajax'])) {
        $clientName = $_GET['clientName'] ?? '';
        $selectedPrincipalEmployers = $_GET['principalEmployer'] ?? [];
        $selectedStates = $_GET['state'] ?? [];
        $selectedLocations = $_GET['locationCode'] ?? [];
        
        $response = [];
        
        if ($clientName) {
            // Get principal employers for client from both tables
            $query = $pdo->prepare("
                SELECT DISTINCT principal_employer_name FROM combined_data WHERE client_name = ? 
                UNION
                SELECT DISTINCT principal_employer_name FROM n_f_holiday WHERE client_name = ?
                ORDER BY principal_employer_name ASC
            ");
            $query->execute([$clientName, $clientName]);
            $response['principalEmployers'] = $query->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($selectedPrincipalEmployers)) {
                // Get states for client and principal employers from both tables
                $pePlaceholders = implode(',', array_fill(0, count($selectedPrincipalEmployers), '?'));
                $query = $pdo->prepare("
                    SELECT DISTINCT state FROM combined_data 
                    WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders)
                    UNION
                    SELECT DISTINCT state FROM n_f_holiday 
                    WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders)
                    ORDER by state ASC
                ");
                $query->execute(array_merge([$clientName], $selectedPrincipalEmployers, [$clientName], $selectedPrincipalEmployers));
                $response['states'] = $query->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($selectedStates)) {
                    // Get locations for client, principal employers and states from combined_data
                    $statePlaceholders = implode(',', array_fill(0, count($selectedStates), '?'));
                    $query = $pdo->prepare("
                        SELECT DISTINCT location_code 
                        FROM combined_data 
                        WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders) AND state IN ($statePlaceholders) 
                        ORDER BY location_code ASC
                    ");
                    $query->execute(array_merge([$clientName], $selectedPrincipalEmployers, $selectedStates));
                    $response['locations'] = $query->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (!empty($selectedLocations)) {
                        // Get month-year for client, principal employers, states and locations from both tables
                        $locationPlaceholders = implode(',', array_fill(0, count($selectedLocations), '?'));
                        
                        // Query for combined_data (has location_code)
                        $query1 = $pdo->prepare("
                            SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) AS monthYear 
                            FROM combined_data 
                            WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders) 
                            AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                        ");
                        
                        // Query for n_f_holiday (no location_code)
                        $query2 = $pdo->prepare("
                            SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) AS monthYear
                            FROM n_f_holiday 
                            WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders) 
                            AND state IN ($statePlaceholders)
                        ");
                        
                        // Execute both queries
                        $query1->execute(array_merge([$clientName], $selectedPrincipalEmployers, $selectedStates, $selectedLocations));
                        $monthYears1 = $query1->fetchAll(PDO::FETCH_COLUMN);
                        
                        $query2->execute(array_merge([$clientName], $selectedPrincipalEmployers, $selectedStates));
                        $monthYears2 = $query2->fetchAll(PDO::FETCH_COLUMN);
                        
                        // Combine and sort results
                        $combinedMonthYears = array_unique(array_merge($monthYears1, $monthYears2));
                        rsort($combinedMonthYears); // Sort descending (newest first)
                        
                        $response['monthYears'] = $combinedMonthYears;
                    }
                }
            }
        }
        
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Initial page load - get client list from both tables
    $clientList = $pdo->query("
        SELECT DISTINCT client_name FROM combined_data 
        UNION 
        SELECT DISTINCT client_name FROM n_f_holiday 
        ORDER BY client_name ASC
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    // If we have a client selected, get its principal employers
    if (isset($_POST['clientName'])) {
        $clientName = $_POST['clientName'];
        $query = $pdo->prepare("
            SELECT DISTINCT principal_employer_name FROM combined_data WHERE client_name = ? 
            UNION
            SELECT DISTINCT principal_employer_name FROM n_f_holiday WHERE client_name = ?
            ORDER BY principal_employer_name ASC
        ");
        $query->execute([$clientName, $clientName]);
        $principalEmployerList = $query->fetchAll(PDO::FETCH_COLUMN);
        
        // If we have principal employers selected, get their states
        if (isset($_POST['principalEmployer'])) {
            $principalEmployers = $_POST['principalEmployer'];
            $pePlaceholders = implode(',', array_fill(0, count($principalEmployers), '?'));
            $query = $pdo->prepare("
                SELECT DISTINCT state FROM combined_data 
                WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders)
                UNION
                SELECT DISTINCT state FROM n_f_holiday 
                WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders)
                ORDER BY state ASC
            ");
            $query->execute(array_merge([$clientName], $principalEmployers, [$clientName], $principalEmployers));
            $stateList = $query->fetchAll(PDO::FETCH_COLUMN);
            
            // If we have states selected, get their locations (only from combined_data)
            if (isset($_POST['state'])) {
                $states = $_POST['state'];
                $statePlaceholders = implode(',', array_fill(0, count($states), '?'));
                $query = $pdo->prepare("
                    SELECT DISTINCT location_code 
                    FROM combined_data 
                    WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders) AND state IN ($statePlaceholders) 
                    ORDER BY location_code ASC
                ");
                $query->execute(array_merge([$clientName], $principalEmployers, $states));
                $locationList = $query->fetchAll(PDO::FETCH_COLUMN);
                
                // If we have locations selected, get their month-years from both tables
                if (isset($_POST['locationCode'])) {
                    $locationCodes = $_POST['locationCode'];
                    $locationPlaceholders = implode(',', array_fill(0, count($locationCodes), '?'));
                    
                    // Get month-years from combined_data (with locations)
                    $query1 = $pdo->prepare("
                        SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) AS monthYear 
                        FROM combined_data 
                        WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders) 
                        AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                    ");
                    
                    // Get month-years from n_f_holiday (without locations)
                    $query2 = $pdo->prepare("
                        SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) AS monthYear
                        FROM n_f_holiday 
                        WHERE client_name = ? AND principal_employer_name IN ($pePlaceholders) 
                        AND state IN ($statePlaceholders)
                    ");
                    
                    // Execute both queries
                    $query1->execute(array_merge([$clientName], $principalEmployers, $states, $locationCodes));
                    $monthYears1 = $query1->fetchAll(PDO::FETCH_COLUMN);
                    
                    $query2->execute(array_merge([$clientName], $principalEmployers, $states));
                    $monthYears2 = $query2->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Combine and sort results
                    $monthYearList = array_unique(array_merge($monthYears1, $monthYears2));
                    rsort($monthYearList); // Sort descending (newest first)
                }
            }
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_GET['ajax'])) {
    $clientName = trim($_POST['clientName'] ?? '');
    $principalEmployers = $_POST['principalEmployer'] ?? [];
    $states = $_POST['state'] ?? [];
    $locationCodes = $_POST['locationCode'] ?? [];
    $monthYear = $_POST['monthYear'] ?? '';

    if ($monthYear) {
        list($year, $month) = explode('-', $monthYear);
    }

    if ($clientName && count($principalEmployers) > 0 && count($states) > 0 && count($locationCodes) > 0) {
        try {
            // Instead of storing all data in session, just store the filter criteria
            $_SESSION['filter_criteria'] = [
                'client_name' => $clientName,
                'principal_employers' => $principalEmployers,
                'states' => $states,
                'location_codes' => $locationCodes,
                'month' => $month,
                'year' => $year
            ];

            header("Location: forms/all_forms1.php");
            exit();

        } catch (PDOException $e) {
            echo "<p style='color:red'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:red'>Please fill all fields and select at least one principal employer, state and location code.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Talent Traverse - Search Data</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="styles/temp1.css">
</head>
<body>
<!-- Animated Background -->
<div class="animated-bg">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
</div>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">
        <img src="./img/Talent.png" alt="Logo">
        <div class="logo-text">
        <span class="main-title">Talent Traverse HR TECH PVT LTD</span>
        <span class="tagline" style="text-align:right;">Beyond Boundaries</span>
      </div>
    </div>
    <div class="nav-links">
       <a href="upload.php?category=<?= htmlspecialchars($_GET['category'] ?? 'contract') ?>">Back</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<!-- Floating Elements -->
<div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
</div>

<!-- Filter Form -->
<div class="filter-container">
    <h2>Search Data</h2>
    <form method="post" action="">
        <label for="clientName">Client Name:</label>
        <select id="clientName" name="clientName" required onchange="updatePrincipalEmployers()">
            <option value="">-- Select Client --</option>
            <?php foreach ($clientList as $client): ?>
                <option value="<?= htmlspecialchars($client); ?>" <?= ($client === $clientName) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($client); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Principal Employers:</label>
        <input type="checkbox" id="selectAllPE" onchange="toggleAllCheckboxes('peCheckboxes', this.checked); updateStates()" /> 
        <label for="selectAllPE" style="font-weight:normal; display:inline;">Select All</label>
        <input type="text" id="peSearch" onkeyup="filterCheckboxes('peSearch', 'peCheckboxes')" placeholder="Type to filter principal employers..." />
        <div id="peCheckboxes" class="checkbox-container">
            <?php foreach ($principalEmployerList ?? [] as $pe): ?>
                <label><input type="checkbox" name="principalEmployer[]" value="<?= htmlspecialchars($pe); ?>" 
                            <?= in_array($pe, $principalEmployers) ? 'checked' : ''; ?> onchange="updateStates()" /> 
                        <?= htmlspecialchars($pe); ?></label><br>
            <?php endforeach; ?>
        </div>

        <label>States:</label>
        <input type="checkbox" id="selectAllStates" onchange="toggleAllCheckboxes('stateCheckboxes', this.checked); updateLocations()" /> 
        <label for="selectAllStates" style="font-weight:normal; display:inline;">Select All</label>
        <input type="text" id="stateSearch" onkeyup="filterCheckboxes('stateSearch', 'stateCheckboxes')" placeholder="Type to filter states..." />
        <div id="stateCheckboxes" class="checkbox-container">
            <?php foreach ($stateList ?? [] as $s): ?>
                <label><input type="checkbox" name="state[]" value="<?= htmlspecialchars($s); ?>" 
                            <?= in_array($s, $states) ? 'checked' : ''; ?> onchange="updateLocations()" /> 
                        <?= htmlspecialchars($s); ?></label><br>
            <?php endforeach; ?>
        </div>

        <label>Location Codes:</label>
        <input type="checkbox" id="selectAllLocations" onchange="toggleAllCheckboxes('locationCheckboxes', this.checked); updateMonthYear()" /> 
        <label for="selectAllLocations" style="font-weight:normal; display:inline;">Select All</label>
        <input type="text" id="locationSearch" onkeyup="filterCheckboxes('locationSearch', 'locationCheckboxes')" placeholder="Type to filter location codes..." />
        <div id="locationCheckboxes" class="checkbox-container">
            <?php foreach ($locationList ?? [] as $loc): ?>
                <label><input type="checkbox" name="locationCode[]" value="<?= htmlspecialchars($loc); ?>" 
                            <?= in_array($loc, $locationCodes) ? 'checked' : ''; ?> onchange="updateMonthYear()" /> 
                        <?= htmlspecialchars($loc); ?></label><br>
            <?php endforeach; ?>
        </div>

        <label for="monthYear">Select Month & Year:</label>
        <select id="monthYear" name="monthYear" required>
            <option value="">-- Select Month & Year --</option>
            <?php foreach ($monthYearList ?? [] as $my): ?>
                <option value="<?= htmlspecialchars($my); ?>" <?= ($monthYear === $my) ? 'selected' : ''; ?>>
                    <?= date("F Y", strtotime($my . "-01")); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button class="btn-primary" type="submit">Generate</button>
    </form>
</div>

<script>
function filterCheckboxes(inputId, containerId) {
    const input = document.getElementById(inputId).value.toLowerCase();
    const container = document.getElementById(containerId);
    const labels = container.getElementsByTagName('label');

    for (let i = 0; i < labels.length; i++) {
        const text = labels[i].textContent.toLowerCase();
        labels[i].style.display = text.includes(input) ? '' : 'none';
    }
}

function toggleAllCheckboxes(containerId, checked) {
    const checkboxes = document.querySelectorAll(`#${containerId} input[type="checkbox"]`);
    checkboxes.forEach(cb => cb.checked = checked);
    // Trigger change event for the first checkbox to update dependent dropdowns
    if (checkboxes.length > 0) {
        checkboxes[0].dispatchEvent(new Event('change'));
    }
}

function updatePrincipalEmployers() {
    const clientName = document.getElementById('clientName').value;
    if (!clientName) {
        document.getElementById('peCheckboxes').innerHTML = '';
        document.getElementById('stateCheckboxes').innerHTML = '';
        document.getElementById('locationCheckboxes').innerHTML = '';
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
        return;
    }
    
    fetchData(clientName, [], [], []).then(data => {
        const container = document.getElementById('peCheckboxes');
        container.innerHTML = '';
        if (data.principalEmployers && data.principalEmployers.length > 0) {
            data.principalEmployers.forEach(pe => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" name="principalEmployer[]" value="${pe}" onchange="updateStates()"/> ${pe}<br>`;
                container.appendChild(label);
            });
        }
        
        // Clear dependent fields
        document.getElementById('stateCheckboxes').innerHTML = '';
        document.getElementById('locationCheckboxes').innerHTML = '';
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
    }).catch(error => {
        console.error('Error updating principal employers:', error);
    });
}

function updateStates() {
    const clientName = document.getElementById('clientName').value;
    const principalEmployers = Array.from(document.querySelectorAll('#peCheckboxes input[type="checkbox"]:checked'))
                     .map(cb => cb.value);
    
    if (!clientName || principalEmployers.length === 0) {
        document.getElementById('stateCheckboxes').innerHTML = '';
        document.getElementById('locationCheckboxes').innerHTML = '';
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
        return;
    }
    
    fetchData(clientName, principalEmployers, [], []).then(data => {
        const container = document.getElementById('stateCheckboxes');
        container.innerHTML = '';
        if (data.states && data.states.length > 0) {
            data.states.forEach(state => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" name="state[]" value="${state}" onchange="updateLocations()"/> ${state}<br>`;
                container.appendChild(label);
            });
        }
        
        // Clear dependent fields
        document.getElementById('locationCheckboxes').innerHTML = '';
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
    }).catch(error => {
        console.error('Error updating states:', error);
    });
}

function updateLocations() {
    const clientName = document.getElementById('clientName').value;
    const principalEmployers = Array.from(document.querySelectorAll('#peCheckboxes input[type="checkbox"]:checked'))
                     .map(cb => cb.value);
    const states = Array.from(document.querySelectorAll('#stateCheckboxes input[type="checkbox"]:checked'))
                     .map(cb => cb.value);
    
    if (!clientName || principalEmployers.length === 0 || states.length === 0) {
        document.getElementById('locationCheckboxes').innerHTML = '';
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
        return;
    }
    
    fetchData(clientName, principalEmployers, states, []).then(data => {
        const container = document.getElementById('locationCheckboxes');
        container.innerHTML = '';
        if (data.locations && data.locations.length > 0) {
            data.locations.forEach(location => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" name="locationCode[]" value="${location}" onchange="updateMonthYear()"/> ${location}<br>`;
                container.appendChild(label);
            });
        }
        
        // Clear month-year field
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
    }).catch(error => {
        console.error('Error updating locations:', error);
    });
}

function updateMonthYear() {
    const clientName = document.getElementById('clientName').value;
    const principalEmployers = Array.from(document.querySelectorAll('#peCheckboxes input[type="checkbox"]:checked'))
                     .map(cb => cb.value);
    const states = Array.from(document.querySelectorAll('#stateCheckboxes input[type="checkbox"]:checked'))
                     .map(cb => cb.value);
    const locations = Array.from(document.querySelectorAll('#locationCheckboxes input[type="checkbox"]:checked'))
                         .map(cb => cb.value);
    
    if (!clientName || principalEmployers.length === 0 || states.length === 0 || locations.length === 0) {
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
        return;
    }
    
    fetchData(clientName, principalEmployers, states, locations).then(data => {
        const select = document.getElementById('monthYear');
        select.innerHTML = '<option value="">-- Select Month & Year --</option>';
        
        if (data.monthYears && data.monthYears.length > 0) {
            data.monthYears.forEach(my => {
                try {
                    const [year, month] = my.split('-');
                    const date = new Date(year, month-1);
                    const formattedDate = date.toLocaleDateString('en-US', { 
                        month: 'long', 
                        year: 'numeric' 
                    });
                    
                    const option = document.createElement('option');
                    option.value = my;
                    option.textContent = formattedDate;
                    select.appendChild(option);
                } catch (e) {
                    console.error('Error formatting date:', my, e);
                }
            });
        }
    }).catch(error => {
        console.error('Error updating month-year:', error);
    });
}

function fetchData(clientName, principalEmployers, states, locations) {
    const params = new URLSearchParams();
    params.append('ajax', '1');
    params.append('clientName', clientName);
    principalEmployers.forEach(pe => params.append('principalEmployer[]', pe));
    states.forEach(state => params.append('state[]', state));
    locations.forEach(location => params.append('locationCode[]', location));
    
    return fetch(`?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error in fetchData:', error);
            throw error;
        });
}

// Initialize form if we have post data
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    // Trigger initial updates
    updatePrincipalEmployers();
    
    <?php if (!empty($principalEmployers)): ?>
    // Restore principal employer selections after a short delay
    setTimeout(() => {
        <?php foreach ($principalEmployers as $pe): ?>
        const peCheckbox = document.querySelector(`#peCheckboxes input[type="checkbox"][value="<?= $pe ?>"]`);
        if (peCheckbox) peCheckbox.checked = true;
        <?php endforeach; ?>
        // Trigger states update
        updateStates();
    }, 100);
    <?php endif; ?>
    
    <?php if (!empty($states)): ?>
    // Restore state selections after principal employers are loaded
    setTimeout(() => {
        <?php foreach ($states as $state): ?>
        const stateCheckbox = document.querySelector(`#stateCheckboxes input[type="checkbox"][value="<?= $state ?>"]`);
        if (stateCheckbox) stateCheckbox.checked = true;
        <?php endforeach; ?>
        // Trigger locations update
        updateLocations();
    }, 300);
    <?php endif; ?>
    
    <?php if (!empty($locationCodes)): ?>
    // Restore location selections after states are loaded
    setTimeout(() => {
        <?php foreach ($locationCodes as $loc): ?>
        const locCheckbox = document.querySelector(`#locationCheckboxes input[type="checkbox"][value="<?= $loc ?>"]`);
        if (locCheckbox) locCheckbox.checked = true;
        <?php endforeach; ?>
        // Trigger month-year update
        updateMonthYear();
    }, 500);
    <?php endif; ?>
    <?php endif; ?>
});
</script>

</body>
</html>