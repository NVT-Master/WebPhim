<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/auth/login.php");
    exit();
}

if (!isset($_POST['booking_id'])) {
    die("Dữ liệu không hợp lệ!");
}

$booking_id = (int) $_POST['booking_id'];

$conn = getDbConnection();

// Update trạng thái booking
$stmt = $conn->prepare("UPDATE bookings SET status = 'CONFIRMED' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
if ($stmt->execute()) {
    $_SESSION['success'] = "Thanh toán thành công!";
    header("Location: ../views/cinema/my_tickets.php");
} else {
    $_SESSION['error'] = "Thanh toán thất bại!";
    header("Location: ../views/cinema/payment.php?booking_id=" . $booking_id);
}
$stmt->close();
$conn->close();
?>