<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['usernameOrEmail'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail && $password) {
        $conn = getDbConnection();

        // Lấy user theo tên hoặc email
        $stmt = $conn->prepare("SELECT id, name, email, password, role 
                                FROM users 
                                WHERE email = ? OR name = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $stmt->bind_result($id, $name, $email, $storedPassword, $role);

        if ($stmt->fetch()) {
            // 👉 So sánh mật khẩu nhập với mật khẩu trong DB
            if ($password === $storedPassword) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role;

                if ($role === 'ADMIN') {
                    header("Location: ../views/admin/index.php");
                } else {
                    header("Location: ../views/cinema/index.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Sai mật khẩu!";
            }
        } else {
            $_SESSION['error'] = "Tên hoặc Email không tồn tại!";
        }

        $stmt->close();
        $conn->close();
    } else {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Đặt vé xem phim</title>
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
    <img src="../../images/backgrounds/anhnen.jpg" class="bg-full" alt="Background">

    <div class="center-form">
        <div class="form-box">
            <h2 class="text-center mb-4 text-primary">Đăng nhập</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="../../handle/login_process.php">
                <div class="mb-3">
                    <label>Tên hoặc Email</label>
                    <input type="text" name="usernameOrEmail" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>

            <div class="text-center mt-3">
                <a href="register.php">Chưa có tài khoản? Đăng ký</a>
            </div>
        </div>
    </div>
</body>

</html>