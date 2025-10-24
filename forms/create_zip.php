<?php
// Add at the very top of create_zip.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log all errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Function to send consistent JSON responses
function sendResponse($success, $message, $additionalData = [])
{
    $response = ['success' => $success, 'message' => $message] + $additionalData;
    echo json_encode($response);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    if (empty($input)) {
        sendResponse(false, 'No input data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(false, 'Invalid JSON: ' . json_last_error_msg());
    }

    if (!$data || !isset($data['files']) || !is_array($data['files'])) {
        sendResponse(false, 'Invalid input: files array required', ['received_data' => $data]);
    }

    $files = $data['files'];
    $clientName = $data['clientName'] ?? 'client';
    $folderMonthYear = $data['folderMonthYear'] ?? '';

    error_log("Create ZIP called with " . count($files) . " files");
    error_log("Client: $clientName, Month: $folderMonthYear");

    // Base directory for downloads (relative to create_zip.php location)
    $webDownloadBase = __DIR__ . '/../downloads/';
    $realBase = realpath($webDownloadBase);

    if ($realBase === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid downloads base path: ' . $webDownloadBase
        ]);
        exit;
    }

    error_log("Real base path: $realBase");

    // List what's actually in the downloads directory for debugging
    error_log("Contents of downloads directory:");
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realBase));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            error_log("Found: " . $file->getPathname());
        }
    }

    // Create zips directory if it doesn't exist
    $zipsDir = $realBase . DIRECTORY_SEPARATOR . '_zips' . DIRECTORY_SEPARATOR;
    if (!is_dir($zipsDir)) {
        if (!mkdir($zipsDir, 0755, true)) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create zips directory: ' . $zipsDir
            ]);
            exit;
        }
    }

    // Sanitize client name for filename
    $clientSafe = preg_replace('/[^\w\-]/', '_', trim($clientName));
    $time = (new DateTime())->format('Ymd_His');
    $zipFilename = "{$clientSafe}.zip";
    $zipPath = $zipsDir . $zipFilename;

    error_log("Creating ZIP file: $zipPath");

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot create ZIP file: ' . $zipPath
        ]);
        exit;
    }

    $added = 0;
    $failedFiles = [];

    foreach ($files as $relativePath) {
        // Normalize path - remove any leading/trailing slashes
        $relativePath = trim($relativePath);
        if ($relativePath === '') {
            $failedFiles[] = 'Empty path';
            continue;
        }

        // Remove any leading slashes or backslashes
        $relativePath = ltrim($relativePath, '/\\');

        // Build absolute path - FIXED: Use DIRECTORY_SEPARATOR
        $absolutePath = $realBase . DIRECTORY_SEPARATOR . $relativePath;

        // Try multiple path variations
        $pathVariations = [
            $absolutePath,
            $realBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath),
            $realBase . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativePath),
        ];

        $foundPath = null;
        foreach ($pathVariations as $variation) {
            if (file_exists($variation) && is_file($variation)) {
                $foundPath = $variation;
                break;
            }
        }

        if ($foundPath === null) {
            $failedFiles[] = "File not found: $relativePath (tried: " . implode(', ', $pathVariations) . ")";
            error_log("File not found: $relativePath");
            continue;
        }

        // Security check: ensure file is within the downloads directory
        if (strpos($foundPath, $realBase) !== 0) {
            $failedFiles[] = "Path traversal detected: $relativePath";
            error_log("Path traversal detected: $relativePath");
            continue;
        }

        // Check if file is readable
        if (!is_readable($foundPath)) {
            $failedFiles[] = "File not readable: $relativePath";
            error_log("File not readable: $relativePath");
            continue;
        }

        // Add file to ZIP with relative path as stored name
        if ($zip->addFile($foundPath, $relativePath)) {
            $added++;
            error_log("✓ Added to ZIP: $relativePath");
        } else {
            $failedFiles[] = "Failed to add to ZIP: $relativePath";
            error_log("Failed to add to ZIP: $relativePath");
        }
    }

    $zip->close();

    error_log("ZIP creation completed. Added: $added files, Failed: " . count($failedFiles));

    if ($added === 0) {
        // Remove empty zip file
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }
        echo json_encode([
            'success' => false,
            'message' => 'No valid PDF files found to add to ZIP',
            'failed_files' => $failedFiles,
            'base_path' => $realBase,
            'searched_files' => $files
        ]);
        exit;
    }

    // Build web-accessible URL for the ZIP file

    $zipUrl = '/downloads/_zips/' . $zipFilename; // Full path from root

    // Or if that doesn't work, try absolute URL:
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $zipUrl = $protocol . '://' . $host . '/downloads/_zips/' . $zipFilename;

    echo json_encode([
        'success' => true,
        'zip_filename' => $zipFilename,
        'zip_path' => $zipPath,
        'zip_url' => $zipUrl,
        'files_added' => $added,
        'message' => 'ZIP file created successfully with ' . $added . ' files'
    ]);
    exit;
} catch (Exception $e) {
    error_log("create_zip.php exception: " . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage());
}
