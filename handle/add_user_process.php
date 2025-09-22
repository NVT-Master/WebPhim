<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // Kiểm tra người dùng tồn tại theo email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['add_user_error'] = "Người dùng với email này đã tồn tại!";
        header("Location: ../views/admin/add_user.php");
        exit();
    }
    $stmt->close();

    // Thêm người dùng mới
    $stmt = $conn->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $role);
    if ($stmt->execute()) {
        $_SESSION['add_user_success'] = "Thêm người dùng thành công!";
        header("Location: ../views/admin/manage_users.php");
        exit();
    } else {
        $_SESSION['add_user_error'] = "Thêm người dùng thất bại!";
        header("Location: ../views/admin/add_user.php");
        exit();
    }
    $stmt->close();
}

$conn->close();
?>