<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "mamdouh_portfolio";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات"]));
}
?>