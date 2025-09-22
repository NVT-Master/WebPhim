<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $movie_id = (int) $_POST['movie_id'];
    $room_id = (int) $_POST['room_id'];
    $start_time = $_POST['start_time'];
    $price = (float) $_POST['price'];

    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE showtimes SET movie_id=?, room_id=?, start_time=?, price=? WHERE id=?");
    $stmt->bind_param("iisdi", $movie_id, $room_id, $start_time, $price, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: ../views/admin/manage_showtimes.php");
    exit;
}
?>