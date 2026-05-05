<?php
$host = 'localhost';
$db   = 'powerteam';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// $db   = 'u815853083_power_team';
// $user = 'u815853083_power_team';
// $pass = 'R8?!q9@+]$^j';
//  $charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enables exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>