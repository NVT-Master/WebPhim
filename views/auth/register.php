<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name && $email && $password) {
        if ($password !== $confirm) {
            $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
        } else {
            $conn = getDbConnection();

            // kiểm tra trùng email
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR name = ?");
            $check->bind_param("ss", $email, $name);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $_SESSION['error'] = "Email hoặc Tên đã tồn tại!";
            } else {
                // Lưu mật khẩu dạng thường (không hash)
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'USER')");
                $stmt->bind_param("sss", $name, $email, $password);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Đăng ký thành công! Mời bạn đăng nhập.";
                    header("Location: login.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Lỗi khi đăng ký: " . $stmt->error;
                }

                $stmt->close();
            }

            $check->close();
            $conn->close();
        }
    } else {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký - Đặt vé xem phim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-full {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .center-form {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-box {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Background -->
    <img src="../../images/backgrounds/anhnen.jpg" class="bg-full" alt="Background">

    <div class="center-form">
        <div class="form-box">
            <h2 class="text-center mb-4 text-primary">Đăng ký tài khoản</h2>

            <!-- Thông báo -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="../../handle/register_process.php">
                <div class="mb-3">
                    <label>Họ tên</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Xác nhận mật khẩu</label>
                    <input type="password" name="confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php">Đã có tài khoản? Đăng nhập</a>
            </div>
        </div>
    </div>
</body>

</html>