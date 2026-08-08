<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "غير مصرح لك"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['title_ar']) && !empty($data['title_en'])) {
    $t_ar = $conn->real_escape_string($data['title_ar']);
    $t_en = $conn->real_escape_string($data['title_en']);
    $d_ar = $conn->real_escape_string($data['description_ar']);
    $d_en = $conn->real_escape_string($data['description_en']);
    $link = $conn->real_escape_string($data['link']);

    $sql = "INSERT INTO projects (title_ar, title_en, description_ar, description_en, link) 
            VALUES ('$t_ar', '$t_en', '$d_ar', '$d_en', '$link')";
    
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "فشل الحفظ"]);
    }
}
?>