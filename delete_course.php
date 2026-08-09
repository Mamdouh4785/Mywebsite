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
    // مسح صورة الشهادة من مجلد uploads إن وجدت
    $res = $conn->query("SELECT certificate_image FROM courses WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['certificate_image']) && file_exists($row['certificate_image'])) {
            @unlink($row['certificate_image']);
        }
    }

    $sql = "DELETE FROM courses WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "فشل الحذف"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "معرف غير صالح"]);
}
?>