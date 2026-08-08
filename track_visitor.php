<?php
header('Content-Type: application/json');
require 'db.php';

// زيادة العداد بمقدار 1
$sql = "UPDATE page_views SET views_count = views_count + 1 WHERE id = 1";
$conn->query($sql);

// جلب العدد الحالي
$result = $conn->query("SELECT views_count FROM page_views WHERE id = 1");
$row = $result->fetch_assoc();

echo json_encode(["status" => "success", "views" => $row['views_count']]);
?>