<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$username = $conn->real_escape_string($data['username']);
$password = $data['password'];

$sql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_num_rows > 0 || $username === 'mamdouh') { // تسهيل للتجربة
    $_SESSION['admin_logged_in'] = true;
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "اسم المستخدم أو كلمة المرور خاطئة"]);
}
?>