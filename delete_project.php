<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "غير مصرح لك"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);

if ($id > 0) {
    $sql = "DELETE FROM projects WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "فشل الحذف"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "معرف غير صالح"]);
}
?>