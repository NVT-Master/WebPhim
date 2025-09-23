<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if ($name && $email && $password && $confirmPassword) {
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "❌ Mật khẩu nhập lại không khớp!";
        } else {
            $conn = getDbConnection();

            // Kiểm tra email đã tồn tại chưa
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR name = ? LIMIT 1");
            $stmt->bind_param("ss", $email, $name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['error'] = "⚠️ Tài khoản đã tồn tại!";
            } else {
                // Thêm user mới
                $role = "USER"; // mặc định
                // nếu muốn hash thì dùng: $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $hashedPassword = $password; 

                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "🎉 Đăng ký thành công! Bạn có thể đăng nhập.";
                    header("Location: login.php");
                    exit();
                } else {
                    $_SESSION['error'] = "❌ Lỗi khi đăng ký, vui lòng thử lại!";
                }
                $stmt->close();
            }
            $conn->close();
        }
    } else {
        $_SESSION['error'] = "⚠️ Vui lòng nhập đầy đủ thông tin!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - Đặt vé xem phim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-wrapper {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1489599096569-4d6b633f59f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80') center/cover no-repeat;
            filter: blur(5px) brightness(60%);
            z-index: -1;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.8s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo { text-align: center; margin-bottom: 25px; }
        .logo h1 {
            color: white; font-size: 2.3rem; font-weight: 700; margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        .logo p { color: rgba(255,255,255,0.8); font-size: 1rem; margin: 5px 0 0 0; }
        .form-floating { margin-bottom: 18px; }
        .form-floating input {
            border: none; border-bottom: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.15); color: #222;
            border-radius: 6px; padding: 12px 10px; font-size: 1rem;
        }
        .form-floating label { color: rgba(255,255,255,0.7); font-weight: 400; }
        .btn-login {
            background: linear-gradient(135deg, #36d1dc, #5b86e5);
            border: none; border-radius: 50px; padding: 12px 30px;
            font-size: 1.1rem; font-weight: 600; color: white;
            width: 100%; transition: all 0.3s ease;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(91,134,229,0.4); }
        .alert {
            border-radius: 10px; border: none; background: rgba(255,0,0,0.2);
            color: white; backdrop-filter: blur(5px); margin-bottom: 15px;
        }
        .register-link { text-align: center; margin-top: 15px; }
        .register-link a { color: rgba(255,255,255,0.9); text-decoration: none; font-weight: 500; }
        .register-link a:hover { color: #36d1dc; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-bg"></div>
        <div class="login-card">
            <div class="logo">
                <h1>🎬 Cinema</h1>
                <p>Tạo tài khoản để trải nghiệm</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-floating">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Tên đăng nhập" required>
                    <label for="name">Tên đăng nhập</label>
                </div>
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email">Email</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                    <label for="password">Mật khẩu</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Nhập lại mật khẩu" required>
                    <label for="confirmPassword">Nhập lại mật khẩu</label>
                </div>

                <button type="submit" class="btn btn-login">Đăng ký</button>
            </form>

            <div class="register-link">
                <a href="login.php">Đã có tài khoản? <span style="color: #36d1dc;">Đăng nhập</span></a>
            </div>
        </div>
    </div>
</body>
</html>
