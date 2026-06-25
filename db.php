<?php
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$dbpassword = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'outreach_monitor';

$conn = mysqli_connect($host, $username, $dbpassword, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>