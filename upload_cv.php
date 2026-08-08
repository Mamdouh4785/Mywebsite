<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "غير مصرح لك"]);
    exit;
}

if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['cv_file']['tmp_name'];
    $fileName = $_FILES['cv_file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExtension === 'pdf') {
        $uploadFileDir = './uploads/';
        if(!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0755, true);
        
        $newFileName = 'CV_Mamdouh_' . time() . '.pdf';
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            $conn->query("UPDATE settings SET cv_file = '$dest_path' WHERE id = 1");
            echo json_encode(["status" => "success", "message" => "تم تحديث السيرة الذاتية بنجاح!", "file" => $dest_path]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "يرجى رفع ملف بصيغة PDF فقط"]);
        exit;
    }
}
echo json_encode(["status" => "error", "message" => "فشل رفع الملف"]);
?>