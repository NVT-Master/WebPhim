<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

// Kiểm tra quyền ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}

// Kiểm tra id người dùng truyền lên
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['errors'] = ["ID người dùng không hợp lệ."];
    header("Location: ../views/admin/manage_users.php");
    exit();
}

$conn = getDbConnection();
$user_id = intval($_GET['id']);

// Ngăn admin xóa chính mình
if ($user_id === $_SESSION['user_id']) {
    $_SESSION['errors'] = ["Bạn không thể xóa chính mình."];
    header("Location: ../views/admin/manage_users.php");
    exit();
}

// Thực hiện xóa người dùng
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "🎉 Xóa người dùng thành công!";
} else {
    $_SESSION['errors'] = ["Xóa người dùng thất bại: " . $stmt->error];
}

$stmt->close();
$conn->close();

// Quay lại trang quản lý người dùng
header("Location: ../views/admin/manage_users.php");
exit();
?>