<?php
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

$mappingPath = __DIR__ . '/config/form_mapping1.php';
if (!file_exists($mappingPath)) {
    die("<div class='error'>Mapping file not found at: " . htmlspecialchars($mappingPath) . "</div>");
}

$stateFormMapping = include($mappingPath);
if (!is_array($stateFormMapping)) {
    die("<div class='error'>Invalid form mapping format in config/form_mapping1.php</div>");
}

$filters = $_SESSION['filter_criteria'];
$clientName = $filters['client_name'];
$principalEmployers = $filters['principal_employers'] ?? [];
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


    $pePlaceholders = implode(',', array_fill(0, count($principalEmployers), '?'));
    $locPlaceholders = implode(',', array_fill(0, count($locationCodes), '?'));
    $statePlaceholders = implode(',', array_fill(0, count($normalizedStates), '?'));

    $query = $pdo->prepare("
    SELECT principal_employer_name, state, location_code FROM (
        (SELECT principal_employer_name, state, location_code FROM combined_data 
         WHERE client_name = ? 
           AND principal_employer_name IN ($pePlaceholders) 
           AND location_code IN ($locPlaceholders)
           AND state IN ($statePlaceholders))
        UNION
        (SELECT principal_employer_name, state, location_code FROM n_f_holiday 
         WHERE client_name = ? 
           AND principal_employer_name IN ($pePlaceholders) 
           AND location_code IN ($locPlaceholders)
           AND state IN ($statePlaceholders))
    ) AS combined_results
    GROUP BY principal_employer_name, state, location_code
");

    $params = array_merge(
        [$clientName],
        $principalEmployers,
        $locationCodes,
        $normalizedStates, // Add states for first query
        [$clientName],
        $principalEmployers,
        $locationCodes,
        $normalizedStates  // Add states for second query
    );

    $query->execute($params);

    $groupedData = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $pe = $row['principal_employer_name'];
        $state = ucfirst(strtolower(trim($row['state'])));
        $loc = $row['location_code'];

        if (!isset($groupedData[$pe])) {
            $groupedData[$pe] = [];
        }

        if (!isset($groupedData[$pe][$state])) {
            $groupedData[$pe][$state] = [];
        }

        if (!in_array($loc, $groupedData[$pe][$state])) {
            $groupedData[$pe][$state][] = $loc;
        }
    }
} catch (PDOException $e) {
    die("<div class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>");
}

$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 1;
$principalEmployers = array_keys($groupedData);
$totalPrincipals = count($principalEmployers);
$totalPages = ceil($totalPrincipals / $perPage);
$currentPrincipalIndex = min($currentPage - 1, $totalPrincipals - 1);
$currentPrincipal = $principalEmployers[$currentPrincipalIndex] ?? null;
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
    <link rel="stylesheet" href="../styles/all_forms1.css">
</head>

<body>
    <!-- Custom Back Button -->
    <a href="../temp1.php" class="custom-back-btn">
        ⬅ Back
    </a>

    <div id="loading-indicator">Loading forms, please wait...</div>

    <div class="client-header">
        <h1><?= htmlspecialchars($clientName) ?></h1>
        <h3>Compliance Forms for <?= htmlspecialchars($monthYear) ?></h3>
    </div>

    <div id="download-controls">
        <button class="download-btn" id="downloadAllBtn">Download All Forms</button>
    </div>

    <?php if ($totalPrincipals > 0): ?>
        <div class="principal-count">
            Showing Principal Employer <?= $currentPage ?> of <?= $totalPages ?>
        </div>

        <nav aria-label="Principal employer pagination">
            <ul class="pagination">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>

        <div id="form-content">
            <?php if ($currentPrincipal && isset($groupedData[$currentPrincipal])): ?>
                <div class="principal-employer-block">
                    <div class="principal-header">
                        <?= htmlspecialchars($currentPrincipal) ?>
                    </div>

                    <?php foreach ($groupedData[$currentPrincipal] as $state => $locs): ?>
                        <?php if (!isset($stateFormMapping[$state])) continue; ?>

                        <div class="state-header-box">
                            <h2>
                                <?= htmlspecialchars($state) ?> Compliance Forms
                                <span class="badge bg-primary form-count-badge">
                                    <?php
                                    $formCount = 0;
                                    foreach ($locs as $loc) {
                                        $formCount += isset($stateFormMapping[$state]) ? count($stateFormMapping[$state]) : 0;
                                    }
                                    echo $formCount . ' Form' . ($formCount !== 1 ? 's' : '');
                                    ?>
                                </span>
                            </h2>
                        </div>

                        <?php foreach ($locs as $loc): ?>
                            <div class="location-section">
                                <h5>📍 Location Code: <strong class="text-primary"><?= htmlspecialchars($loc) ?></strong></h5>

                                <?php $_SESSION['current_principal'] = $currentPrincipal; ?>
                                <?php $_SESSION['current_state'] = $state; ?>
                                <?php $_SESSION['current_location'] = $loc; ?>

                                <?php foreach ($stateFormMapping[$state] as $formId => $formTemplate): ?>
                                    <div id="<?= strtolower(str_replace(' ', '-', $currentPrincipal)) . '-' . strtolower(str_replace(' ', '-', $state)) . '-' . $formId . '_' . $loc ?>" class="form-container">
                                        <?php if (file_exists($formTemplate)): ?>
                                            <?php include($formTemplate); ?>
                                        <?php else: ?>
                                            <div class="error">Form template not found: <?= htmlspecialchars($formTemplate) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <nav aria-label="Principal employer pagination">
            <ul class="pagination">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>

    <?php else: ?>
        <div class="alert alert-warning">No principal employers found for the selected criteria.</div>
    <?php endif; ?>

    <div id="all-forms-content" style="display: none;">
        <?php foreach ($groupedData as $pe => $statesData): ?>
            <div class="principal-employer-block">
                <div class="principal-header">
                    <?= htmlspecialchars($pe) ?>
                </div>

                <?php foreach ($statesData as $state => $locs): ?>
                    <?php if (!isset($stateFormMapping[$state])) continue; ?>

                    <div class="state-block">
                        <div class="state-header-box">
                            <h2><?= htmlspecialchars($state) ?> Compliance Forms</h2>
                        </div>

                        <?php foreach ($locs as $loc): ?>
                            <div class="location-section">
                                <h5>📍 Location Code: <strong class="text-primary"><?= htmlspecialchars($loc) ?></strong></h5>

                                <?php $_SESSION['current_principal'] = $pe; ?>
                                <?php $_SESSION['current_state'] = $state; ?>
                                <?php $_SESSION['current_location'] = $loc; ?>

                                <?php foreach ($stateFormMapping[$state] as $formId => $formTemplate): ?>
                                    <div id="<?= strtolower(str_replace(' ', '-', $pe)) . '-' . strtolower(str_replace(' ', '-', $state)) . '-' . $formId . '_' . $loc ?>"
                                        class="form-container"
                                        data-principal="<?= htmlspecialchars($pe, ENT_QUOTES) ?>"
                                        data-state="<?= htmlspecialchars($state, ENT_QUOTES) ?>"
                                        data-formid="<?= htmlspecialchars($formId, ENT_QUOTES) ?>"
                                        data-location="<?= htmlspecialchars($loc, ENT_QUOTES) ?>">
                                        <?php if (file_exists($formTemplate)): ?>
                                            <?php include($formTemplate); ?>
                                        <?php else: ?>
                                            <div class="error">Form template not found: <?= htmlspecialchars($formTemplate) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="js/download_handler1.js"></script>
    <script>
        document.querySelector('.custom-back-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href');

            // Fade-out transition
            document.body.style.transition = "opacity 0.5s ease";
            document.body.style.opacity = 0;

            setTimeout(() => {
                window.location.href = target;
            }, 500);
        });
    </script>
</body>

</html>