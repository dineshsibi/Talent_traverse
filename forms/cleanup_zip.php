<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$zipPath = $data['zip_path'] ?? '';

if ($zipPath && file_exists($zipPath) && strpos($zipPath, '_zips') !== false) {
    unlink($zipPath);
    echo json_encode(['success' => true, 'message' => 'ZIP file cleaned up']);
} else {
    echo json_encode(['success' => false, 'message' => 'No valid ZIP file to clean']);
}
?>