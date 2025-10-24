<?php
// --- Existing logic (unchanged)
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    die("<div class='error'>Session not active. Please enable sessions.</div>");
}

if (empty($_SESSION['filter_criteria'])) {
    die("<div class='error'>No filter criteria found. Please go back and generate the forms first.</div>");
}

$mappingPath = __DIR__ . '/config/form_mapping.php';
if (!file_exists($mappingPath)) {
    die("<div class='error'>Mapping file not found at: " . htmlspecialchars($mappingPath) . "</div>");
}

$stateFormMapping = include($mappingPath);
if (!is_array($stateFormMapping)) {
    die("<div class='error'>Invalid form mapping format in config/form_mapping.php</div>");
}

$filters = $_SESSION['filter_criteria'];
$clientName = $filters['client_name'];
$states = $filters['states'];
$locationCodes = $filters['location_codes'];
$month = $filters['month'];
$year = $filters['year'];
$monthYear = ($year && $month) ? "$year-$month" : "";

$normalizedStates = array_map(function ($state) {
    return ucfirst(strtolower(trim($state)));
}, $states);

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $placeholders = implode(',', array_fill(0, count($locationCodes), '?'));

    $query = $pdo->prepare("
        SELECT state, location_code FROM (
            (SELECT state, location_code FROM input WHERE client_name = ? AND location_code IN ($placeholders))
            UNION
            (SELECT state, location_code FROM 
            nfh WHERE client_name = ? AND location_code IN ($placeholders))
            UNION
            (SELECT state, location_code FROM clra WHERE client_name = ? AND location_code IN ($placeholders))
        ) AS combined_results
        GROUP BY state, location_code
    ");

    $params = array_merge(
        [$clientName],
        $locationCodes,
        [$clientName],
        $locationCodes,
        [$clientName],
        $locationCodes
    );
    $query->execute($params);

    $stateLocationMapping = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $state = ucfirst(strtolower(trim($row['state'])));
        if (!isset($stateLocationMapping[$state])) {
            $stateLocationMapping[$state] = [];
        }
        $stateLocationMapping[$state][] = $row['location_code'];
    }
} catch (PDOException $e) {
    die("<div class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

$stateList = implode(', ', array_map('htmlspecialchars', $normalizedStates));
$locationCodeList = implode(', ', array_map('htmlspecialchars', $locationCodes));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>All Compliance Forms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../styles/all_forms.css">
</head>
<body>
    <!-- Custom Back Button -->
    <a href="../temp.php" class="custom-back-btn">
        ⬅ Back
    </a>

    <div id="loading-indicator">
        <div class="floating-particles" id="particles-container"></div>
        <div class="loading-content">
            <div class="logo-container">
                <img src="../img/Talent.png" alt="Company Logo" class="company-logo">
            </div>
            <div class="loading-text" id="loading-message">Preparing Your Compliance Forms</div>
            <div class="loading-subtext" id="loading-submessage">Gathering form templates and data...</div>

            <div class="progress-container">
                <div class="progress-fill" id="progress-fill"></div>
            </div>

            <div class="progress-text" id="progress-text">0% Complete</div>
        </div>
    </div>

    <div id="content-area" class="content-area">
        <div id="download-controls">
            <button class="download-btn" id="downloadAllBtn">Download All Forms</button>
        </div>

        <div id="pagination-controls">
            <button class="download-btn" id="prevState">Previous</button>
            <button class="download-btn" id="nextState">Next</button>

            <label for="stateDropdown" style="margin-left: 15px;">Jump to State:</label>
            <select id="stateDropdown" class="form-select" style="width: auto; display: inline-block;">
                <?php foreach ($normalizedStates as $index => $state): ?>
                    <option value="<?= $index ?>"><?= htmlspecialchars($state) ?></option>
                <?php endforeach; ?>
            </select>

            <span id="stateIndicator">State 1 of <?= count($normalizedStates) ?></span>
        </div>

        <div id="form-content">
            <?php
            foreach ($normalizedStates as $state) {
                if (!isset($stateFormMapping[$state])) {
                    echo "<div class='error'>No form mapping found for state: $state</div>";
                    continue;
                }

                $stateLocationCodes = isset($stateLocationMapping[$state])
                    ? array_intersect($locationCodes, $stateLocationMapping[$state])
                    : [];

                if (empty($stateLocationCodes)) {
                    echo "<div class='error'>No valid location codes found for state: $state</div>";
                    continue;
                }

                echo "<div class='state-block'>";
                echo "<div class='state-header-box'>";
                echo "<h2>$state Compliance Forms</h2>";

                $totalFormsForState = 0;
                $formTemplatesForState = $stateFormMapping[$state] ?? [];
                foreach ($stateLocationCodes as $_) {
                    $totalFormsForState += count($formTemplatesForState);
                }

                echo "<div class='form-count'>Total Forms: $totalFormsForState</div>";
                echo "</div>";

                foreach ($stateLocationCodes as $locationCode) {
                    echo "<div class='location-section'>";
                    echo "<h5>📍 Location Code: <strong class='text-primary'>$locationCode</strong></h5>";

                    $_SESSION['current_location'] = $locationCode;

                    foreach ($stateFormMapping[$state] as $formId => $formTemplate) {
                        $uniqueFormId = strtolower($state) . '-' . $formId . '_' . $locationCode;
                        echo "<div id='$uniqueFormId' class='form-container'>";

                        if (file_exists($formTemplate)) {
                            include($formTemplate);
                        } else {
                            echo "<div class='error'>Form template not found: " . htmlspecialchars($formTemplate) . "</div>";
                        }

                        echo "</div>";
                    }

                    unset($_SESSION['current_location']);
                    echo "</div>"; // location-section
                }

                echo "</div>"; // state-block
            }
            ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Create floating particles
            const particlesContainer = $('#particles-container');
            const emojis = ['📄', '📝', '📑', '🔖', '📂', '📎', '📌', '✏️', '📏', '📅'];

            for (let i = 0; i < 20; i++) {
                const particle = $('<div class="particle">').text(emojis[Math.floor(Math.random() * emojis.length)]);
                particle.css({
                    left: Math.random() * 100 + '%',
                    animationDelay: Math.random() * 15 + 's',
                    animationDuration: (15 + Math.random() * 10) + 's'
                });
                particlesContainer.append(particle);
            }

            // Start processing with enhanced messages
            const messages = [
                "Preparing Your Compliance Forms",
                "Gathering Form Templates",
                "Loading Location Data",
                "Processing State Requirements",
                "Finalizing Documents"
            ];

            const submessages = [
                "Setting up your compliance forms...",
                "Collecting all necessary templates...",
                "Retrieving location-specific information...",
                "Applying state-specific regulations...",
                "Almost ready! Finalizing your documents..."
            ];

            let progress = 0;
            let messageIndex = 0;

            // Update messages initially
            $('#loading-message').text(messages[messageIndex]);
            $('#loading-submessage').text(submessages[messageIndex]);

            const progressInterval = setInterval(() => {
                progress += 2;

                // Update progress bar
                $('#progress-fill').css('width', progress + '%');
                $('#progress-text').text(progress + '% Complete');

                // Update messages at certain progress points
                if (progress >= 20 && messageIndex < 1) {
                    messageIndex = 1;
                    $('#loading-message').text(messages[messageIndex]);
                    $('#loading-submessage').text(submessages[messageIndex]);
                } else if (progress >= 40 && messageIndex < 2) {
                    messageIndex = 2;
                    $('#loading-message').text(messages[messageIndex]);
                    $('#loading-submessage').text(submessages[messageIndex]);
                } else if (progress >= 60 && messageIndex < 3) {
                    messageIndex = 3;
                    $('#loading-message').text(messages[messageIndex]);
                    $('#loading-submessage').text(submessages[messageIndex]);
                } else if (progress >= 80 && messageIndex < 4) {
                    messageIndex = 4;
                    $('#loading-message').text(messages[messageIndex]);
                    $('#loading-submessage').text(submessages[messageIndex]);
                }

                if (progress >= 100) {
                    clearInterval(progressInterval);
                    // Hide loading indicator and show content when done
                    setTimeout(function() {
                        $('#loading-indicator').fadeOut(500);
                        $('#content-area').fadeIn(800);
                        showState(currentIndex);
                    }, 500);
                }
            }, 70);

            const $states = $('.state-block');
            let currentIndex = 0;
            const totalStates = $states.length;

            function updateStateIndicator() {
                $('#stateIndicator').text(`State ${currentIndex + 1} of ${totalStates}`);
                $('#stateDropdown').val(currentIndex);
            }

            function showState(index) {
                $states.hide().eq(index).fadeIn(300);
                updateStateIndicator();
                $('html, body').animate({
                    scrollTop: 0
                }, 'smooth');
            }

            $('#prevState').on('click', function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    showState(currentIndex);
                }
            });

            $('#nextState').on('click', function() {
                if (currentIndex < totalStates - 1) {
                    currentIndex++;
                    showState(currentIndex);
                }
            });

            $('#stateDropdown').on('change', function() {
                currentIndex = parseInt($(this).val());
                showState(currentIndex);
            });
        });
    </script>

    <script>
        window.formStateData = {
            selectedStates: <?= json_encode($normalizedStates) ?>,
            clientName: "<?= addslashes($clientName) ?>",
            locationCodes: <?= json_encode($locationCodes) ?>,
            monthYear: "<?= $monthYear ?>",
        };
    </script>
    <script src="js/download_handler.js"></script>

</body>

</html>