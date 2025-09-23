<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/auth/login.php");
    exit;
}

$usernameOrEmail = trim($_POST['usernameOrEmail'] ?? '');
$password = $_POST['password'] ?? '';

if (!$usernameOrEmail || !$password) {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    header("Location: ../views/auth/login.php");
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? OR name = ?");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$stmt->bind_result($id, $name, $email, $storedPassword, $role);

if ($stmt->fetch()) {
    if ($password === $storedPassword) {
        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = $role;
        // Đường dẫn tuyệt đối về trang chủ
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION['error'] = "Sai mật khẩu!";
    }
} else {
    $_SESSION['error'] = "Tên hoặc Email không tồn tại!";
}
header("Location: ../views/auth/login.php");
exit;
?>
<form method="POST" action="../../handle/login_process.php">