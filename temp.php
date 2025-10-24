<?php
session_start();

// Include config which returns a PDO connection
$pdo = include("includes/config.php");

$clientName = "";
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
    $selectedStates = $_GET['state'] ?? [];
    $selectedLocations = $_GET['locationCode'] ?? [];

    $response = [];

    if ($clientName) {
      // Get states for client
      $query = $pdo->prepare("SELECT DISTINCT state FROM input WHERE client_name = ? ORDER BY state ASC");
      $query->execute([$clientName]);
      $response['states'] = $query->fetchAll(PDO::FETCH_COLUMN);

      if (!empty($selectedStates)) {
        // Get locations for client and states
        $statePlaceholders = implode(',', array_fill(0, count($selectedStates), '?'));
        $query = $pdo->prepare("SELECT DISTINCT location_code FROM input WHERE client_name = ? AND state IN ($statePlaceholders) ORDER BY location_code ASC");
        $query->execute(array_merge([$clientName], $selectedStates));
        $response['locations'] = $query->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($selectedLocations)) {
          // Get month-year for client, states and locations
          $locationPlaceholders = implode(',', array_fill(0, count($selectedLocations), '?'));
          $query = $pdo->prepare("
                        SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) AS monthYear 
                        FROM input 
                        WHERE client_name = ? AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                        UNION
                        SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) 
                        FROM nfh 
                        WHERE client_name = ? AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                        UNION
                        SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) 
                        FROM clra 
                        WHERE client_name = ? AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                        ORDER BY monthYear DESC
                    ");
          $query->execute(array_merge(
            [$clientName],
            $selectedStates,
            $selectedLocations,
            [$clientName],
            $selectedStates,
            $selectedLocations,
            [$clientName],
            $selectedStates,
            $selectedLocations
          ));
          $response['monthYears'] = $query->fetchAll(PDO::FETCH_COLUMN);
        }
      }
    }

    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  }

  // Initial page load - get client list
  $clientList = $pdo->query("SELECT DISTINCT client_name FROM input ORDER BY client_name ASC")->fetchAll(PDO::FETCH_COLUMN);

  // If we have a client selected, get its states
  if (isset($_POST['clientName'])) {
    $clientName = $_POST['clientName'];
    $query = $pdo->prepare("SELECT DISTINCT state FROM input WHERE client_name = ? ORDER BY state ASC");
    $query->execute([$clientName]);
    $stateList = $query->fetchAll(PDO::FETCH_COLUMN);

    // If we have states selected, get their locations
    if (isset($_POST['state'])) {
      $states = $_POST['state'];
      $statePlaceholders = implode(',', array_fill(0, count($states), '?'));
      $query = $pdo->prepare("SELECT DISTINCT location_code FROM input WHERE client_name = ? AND state IN ($statePlaceholders) ORDER BY location_code ASC");
      $query->execute(array_merge([$clientName], $states));
      $locationList = $query->fetchAll(PDO::FETCH_COLUMN);

      // If we have locations selected, get their month-years
      if (isset($_POST['locationCode'])) {
        $locationCodes = $_POST['locationCode'];
        $locationPlaceholders = implode(',', array_fill(0, count($locationCodes), '?'));
        $query = $pdo->prepare("
                    SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) AS monthYear 
                    FROM input 
                    WHERE client_name = ? AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                    UNION
                    SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) 
                    FROM nfh 
                    WHERE client_name = ? AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                    UNION
                    SELECT DISTINCT CONCAT(year, '-', LPAD(month, 2, '0')) 
                    FROM clra 
                    WHERE client_name = ? AND state IN ($statePlaceholders) AND location_code IN ($locationPlaceholders)
                    ORDER BY monthYear DESC
                ");
        $query->execute(array_merge(
          [$clientName],
          $states,
          $locationCodes,
          [$clientName],
          $states,
          $locationCodes,
          [$clientName],
          $states,
          $locationCodes
        ));
        $monthYearList = $query->fetchAll(PDO::FETCH_COLUMN);
      }
    }
  }
} catch (PDOException $e) {
  echo "<p style='color:red'>Connection error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_GET['ajax'])) {
  $clientName = trim($_POST['clientName'] ?? '');
  $states = $_POST['state'] ?? [];
  $locationCodes = $_POST['locationCode'] ?? [];
  $monthYear = $_POST['monthYear'] ?? '';

  if ($monthYear) {
    list($year, $month) = explode('-', $monthYear);
  }

  if ($clientName && count($states) > 0 && count($locationCodes) > 0) {
    try {
      // Instead of storing all data in session, just store the filter criteria
      $_SESSION['filter_criteria'] = [
        'client_name' => $clientName,
        'states' => $states,
        'location_codes' => $locationCodes,
        'month' => $month,
        'year' => $year
      ];

      header("Location: forms/all_forms.php");
      exit();
    } catch (PDOException $e) {
      echo "<p style='color:red'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
  } else {
    echo "<p style='color:red'>Please fill all fields and select at least one state and location code.</p>";
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Talent Traverse - Search Data</title>
  <script type="text/javascript">
    function preventBack() {
      window.history.forward()
    };
    setTimeout("preventBack()", 0);
    window.onunload = function() {
      null;
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/temp.css">
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
       <a href="upload.php?category=<?= htmlspecialchars($_GET['category'] ?? 'establishment') ?>">Back</a>
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
      <select id="clientName" name="clientName" required onchange="updateStates()">
        <option value="">-- Select Client --</option>
        <?php foreach ($clientList as $client): ?>
          <option value="<?= htmlspecialchars($client); ?>" <?= ($client === $clientName) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($client); ?>
          </option>
        <?php endforeach; ?>
      </select>

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

  <footer>
    <p>&copy; <?= date('Y') ?> Talent Traverse. All rights reserved.</p>
  </footer>

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
    }

    function updateStates() {
      const clientName = document.getElementById('clientName').value;
      if (!clientName) return;

      fetchData(clientName, [], []).then(data => {
        const container = document.getElementById('stateCheckboxes');
        container.innerHTML = '';
        data.states.forEach(state => {
          const label = document.createElement('label');
          label.innerHTML = `<input type="checkbox" name="state[]" value="${state}" onchange="updateLocations()"/> ${state}<br>`;
          container.appendChild(label);
        });

        // Clear dependent fields
        document.getElementById('locationCheckboxes').innerHTML = '';
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
      });
    }

    function updateLocations() {
      const clientName = document.getElementById('clientName').value;
      const states = Array.from(document.querySelectorAll('#stateCheckboxes input[type="checkbox"]:checked'))
        .map(cb => cb.value);
      if (!clientName || states.length === 0) {
        document.getElementById('locationCheckboxes').innerHTML = '';
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
        return;
      }

      fetchData(clientName, states, []).then(data => {
        const container = document.getElementById('locationCheckboxes');
        container.innerHTML = '';
        data.locations.forEach(location => {
          const label = document.createElement('label');
          label.innerHTML = `<input type="checkbox" name="locationCode[]" value="${location}" onchange="updateMonthYear()"/> ${location}<br>`;
          container.appendChild(label);
        });

        // Clear month-year field
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
      });
    }

    function updateMonthYear() {
      const clientName = document.getElementById('clientName').value;
      const states = Array.from(document.querySelectorAll('#stateCheckboxes input[type="checkbox"]:checked'))
        .map(cb => cb.value);
      const locations = Array.from(document.querySelectorAll('#locationCheckboxes input[type="checkbox"]:checked'))
        .map(cb => cb.value);
      if (!clientName || states.length === 0 || locations.length === 0) {
        document.getElementById('monthYear').innerHTML = '<option value="">-- Select Month & Year --</option>';
        return;
      }

      fetchData(clientName, states, locations).then(data => {
        const select = document.getElementById('monthYear');
        select.innerHTML = '<option value="">-- Select Month & Year --</option>';
        data.monthYears.forEach(my => {
          const option = document.createElement('option');
          option.value = my;
          option.textContent = new Date(my + '-01').toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric'
          });
          select.appendChild(option);
        });
      });
    }

    function fetchData(clientName, states, locations) {
      const params = new URLSearchParams();
      params.append('ajax', '1');
      params.append('clientName', clientName);
      states.forEach(state => params.append('state[]', state));
      locations.forEach(location => params.append('locationCode[]', location));

      return fetch(`?${params.toString()}`)
        .then(response => response.json());
    }

    // Initialize form if we have post data
    <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
      document.addEventListener('DOMContentLoaded', function() {
        updateStates();
        // Simulate clicks to restore selections
        <?php if (!empty($states)): ?>
          setTimeout(() => {
            <?php foreach ($states as $state): ?>
              document.querySelectorAll('#stateCheckboxes input[type="checkbox"][value="<?= $state ?>"]').forEach(cb => cb.checked = true);
            <?php endforeach; ?>
            updateLocations();
          }, 100);
        <?php endif; ?>

        <?php if (!empty($locationCodes)): ?>
          setTimeout(() => {
            <?php foreach ($locationCodes as $loc): ?>
              document.querySelectorAll('#locationCheckboxes input[type="checkbox"][value="<?= $loc ?>"]').forEach(cb => cb.checked = true);
            <?php endforeach; ?>
            updateMonthYear();
          }, 200);
        <?php endif; ?>
      });
    <?php endif; ?>
  </script>

</body>

</html>