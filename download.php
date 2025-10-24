<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
  header("Location: login.php");
  exit;
}

if (isset($_POST['file'])) {
  $file = $_POST['file'];
  
  // Validate the file name to prevent directory traversal
  $allowedFiles = ['CLRA Format.xlsx', 'S&E Format.xlsx'];
  if (!in_array($file, $allowedFiles)) {
    die("Invalid file requested");
  }
  
  $filepath = 'formats/' . $file;
  
  if (file_exists($filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    flush(); // Flush system output buffer
    readfile($filepath);
    exit;
  } else {
    die("File not found");
  }
} else {
  die("No file specified");
}
?>