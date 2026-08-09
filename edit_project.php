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

if ($id > 0 && !empty($data['title_ar']) && !empty($data['title_en'])) {
    $t_ar = $conn->real_escape_string($data['title_ar']);
    $t_en = $conn->real_escape_string($data['title_en']);
    $d_ar = $conn->real_escape_string($data['description_ar']);
    $d_en = $conn->real_escape_string($data['description_en']);
    $link = $conn->real_escape_string($data['link']);

    $sql = "UPDATE projects SET 
            title_ar = '$t_ar', 
            title_en = '$t_en', 
            description_ar = '$d_ar', 
            description_en = '$d_en', 
            link = '$link' 
            WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "message" => "تم تحديث المشروع بنجاح"]);
    } else {
        echo json_encode(["status" => "error", "message" => "فشل التحديث"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "بيانات غير مكتملة"]);
}
?>