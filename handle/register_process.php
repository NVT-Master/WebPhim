<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/auth/register.php");
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm'] ?? '';

if (!$name || !$email || !$password || !$confirm) {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    header("Location: ../views/auth/register.php");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
    header("Location: ../views/auth/register.php");
    exit;
}

$conn = getDbConnection();

// Kiểm tra trùng email hoặc tên
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR name = ?");
$stmt->bind_param("ss", $email, $name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "Tên hoặc email đã được sử dụng!";
    $stmt->close();
    $conn->close();
    header("Location: ../views/auth/register.php");
    exit;
}
$stmt->close();

// Không mã hóa mật khẩu
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'USER')");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
    $stmt->close();
    $conn->close();
    header("Location: ../views/auth/login.php");
    exit;
} else {
    $_SESSION['error'] = "Đăng ký thất bại. Vui lòng thử lại!";
    $stmt->close();
    $conn->close();
    header("Location: ../views/auth/register.php");
    exit;
}