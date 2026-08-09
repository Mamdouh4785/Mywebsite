<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "غير مصرح لك"]);
    exit;
}

// جلب مسار ملف CV الحالي لمسحه من المجلد
$res = $conn->query("SELECT cv_file FROM settings WHERE id = 1");
if ($res && $row = $res->fetch_assoc()) {
    $current_file = $row['cv_file'];
    if (!empty($current_file) && file_exists($current_file) && strpos($current_file, 'uploads/') !== false) {
        @unlink($current_file); // مسح الملف الفيزيائي
    }
}

// تفريغ خانة السيرة الذاتية في قاعدة البيانات
$conn->query("UPDATE settings SET cv_file = NULL WHERE id = 1");

echo json_encode(["status" => "success", "message" => "تم حذف السيرة الذاتية بنجاح"]);
?>