<?php
header('Content-Type: application/json');
require 'db.php';

$result = $conn->query("SELECT cv_file FROM settings WHERE id = 1");
$data = $result->fetch_assoc();
echo json_encode($data);
?>