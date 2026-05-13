<?php
$host = "localhost";
$db = "powerteam";
$user = "root";
$pass = "";

// $host = 'localhost';
// $db   = 'u815853083_power_team';
// $user = 'u815853083_power_team';
// $pass = 'R8?!q9@+]$^j';
// $charset = 'utf8mb4';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
