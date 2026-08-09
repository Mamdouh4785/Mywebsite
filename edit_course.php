<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "غير مصرح لك"]);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$title_ar = $_POST['title_ar'] ?? '';
$title_en = $_POST['title_en'] ?? '';
$provider_ar = $_POST['provider_ar'] ?? '';
$provider_en = $_POST['provider_en'] ?? '';

if ($id > 0 && !empty($title_ar) && !empty($title_en)) {
    $t_ar = $conn->real_escape_string($title_ar);
    $t_en = $conn->real_escape_string($title_en);
    $p_ar = $conn->real_escape_string($provider_ar);
    $p_en = $conn->real_escape_string($provider_en);

    $image_sql = "";
    if (isset($_FILES['certificate_image']) && $_FILES['certificate_image']['error'] === UPLOAD_ERR_OK) {
        $uploadFileDir = './uploads/';
        if(!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0755, true);
        
        $fileExtension = strtolower(pathinfo($_FILES['certificate_image']['name'], PATHINFO_EXTENSION));
        $newFileName = md5(time() . $_FILES['certificate_image']['name']) . '.' . $fileExtension;
        $dest_path = $uploadFileDir . $newFileName;
        
        if(move_uploaded_file($_FILES['certificate_image']['tmp_name'], $dest_path)) {
            $image_sql = ", certificate_image = '" . $conn->real_escape_string($dest_path) . "'";
        }
    }

    $sql = "UPDATE courses SET 
            title_ar = '$t_ar', 
            title_en = '$t_en', 
            provider_ar = '$p_ar', 
            provider_en = '$p_en' 
            $image_sql 
            WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "message" => "تم تحديث الشهادة بنجاح"]);
    } else {
        echo json_encode(["status" => "error", "message" => "فشل التحديث"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "بيانات غير مكتملة"]);
}
?>