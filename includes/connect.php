<?php



// connect.php
// เชื่อมต่อฐานข้อมูล

$host = 'localhost';
$dbname = 'thc_hospital';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn = new mysqli

// ดึงข้อมูล
$sql = "select * from settings";
$result = $conn->query($sql);   
if (!$result) {
    die("Query failed: " . $conn->error);
}
// ตั้งค่า character set เป็น utf8mb4
$sql = "SET NAMES 'utf8mb4'";





$sql = "SELECT * FROM settings LIMIT 1";
$result = $conn->query($sql);
$info = $result->fetch_assoc();
?>
