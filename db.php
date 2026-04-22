<?php
// db.php - simple MySQLi connection. Edit the parameters below.
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$DB_NAME = getenv('DB_NAME') ?: 'futa_advertising';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>