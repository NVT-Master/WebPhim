<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

// Chỉ admin mới xóa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php");
    exit;
}

// Lấy ID xuất chiếu cần xóa
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: ../views/admin/manage_showtimes.php");
    exit;
}

$conn = getDbConnection();

// Xóa xuất chiếu
$stmt = $conn->prepare("DELETE FROM showtimes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$conn->close();

// Quay về trang quản lý
header("Location: ../views/admin/manage_showtimes.php");
exit;
?>