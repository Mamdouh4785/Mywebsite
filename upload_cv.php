<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "جلسة غير صالحة، يرجى إعادة تسجيل الدخول"]);
    exit;
}

if (isset($_FILES['cv_file'])) {
    $fileError = $_FILES['cv_file']['error'];
    
    if ($fileError !== UPLOAD_ERR_OK) {
        $msg = "خطأ في رفع الملف (رمز الخطأ: $fileError)";
        if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
            $msg = "حجم الملف كبير جداً، يرجى رفع ملف أقل من 10 ميجابايت";
        }
        echo json_encode(["status" => "error", "message" => $msg]);
        exit;
    }

    $fileTmpPath = $_FILES['cv_file']['tmp_name'];
    $fileName = $_FILES['cv_file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExtension === 'pdf') {
        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        $newFileName = 'CV_Mamdouh_' . time() . '.pdf';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // نضمن تحديث السطر أو إدراجه فوراً إذا لم يكن موجوداً بقاعدة البيانات
            $check = $conn->query("SELECT id FROM settings WHERE id = 1");
            if ($check && $check->num_rows > 0) {
                $conn->query("UPDATE settings SET cv_file = '$dest_path' WHERE id = 1");
            } else {
                $conn->query("INSERT INTO settings (id, cv_file) VALUES (1, '$dest_path')");
            }

            echo json_encode(["status" => "success", "message" => "تم رفع وتحديث السيرة الذاتية بنجاح!", "file" => $dest_path]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "فشل حفظ الملف في مجلد uploads، تأكد من وجود المجلد"]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "يرجى اختيار ملف بصيغة PDF فقط"]);
        exit;
    }
} else {
    echo json_encode(["status" => "error", "message" => "لم يتم اختيار أي ملف لرفعه"]);
    exit;
}
?>