<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT * FROM courses ORDER BY id DESC";
$result = $conn->query($sql);

$courses = [];
while($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);
?>