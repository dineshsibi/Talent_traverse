<?php
// config.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "master_data";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo; // Make sure to return the connection
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>