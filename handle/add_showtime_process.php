<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php");
    exit();
}

$conn = getDbConnection();

$movie_id = $_POST['movie_id'];
$room_id = $_POST['room_id'];
$start_time = $_POST['start_time'];
$price = $_POST['price'];

// Thêm vào CSDL
$stmt = $conn->prepare("INSERT INTO showtimes (movie_id, room_id, start_time, price) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisd", $movie_id, $room_id, $start_time, $price);
if ($stmt->execute()) {
    $_SESSION['success'] = "Thêm suất chiếu thành công!";
    header("Location: ../views/admin/manage_showtimes.php");
} else {
    $_SESSION['errors'] = ["Thêm suất chiếu thất bại: " . $stmt->error];
    header("Location: ../admin/add_showtime.php");
}
$stmt->close();
$conn->close();
exit();
