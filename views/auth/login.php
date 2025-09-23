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
                                WHERE email = ? OR name = ? LIMIT 1");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Nếu DB đang lưu mật khẩu thuần
            if ($password === $user['password']) {
                // Nếu bạn có hash thì đổi thành:
                // if (password_verify($password, $user['password'])) { ... }
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'ADMIN') {
                    header("Location: ../../views/admin/index.php");
                } else {
                    header("Location: ../../index.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "❌ Sai mật khẩu!";
            }
        } else {
            $_SESSION['error'] = "❌ Tên đăng nhập hoặc Email không tồn tại!";
        }

        $stmt->close();
        $conn->close();
    } else {
        $_SESSION['error'] = "⚠️ Vui lòng nhập đầy đủ thông tin!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Đặt vé xem phim</title>
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
            background: url('https://images.unsplash.com/photo-1489599096569-4d6b633f59f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') center/cover no-repeat;
            filter: blur(5px) brightness(60%);
            z-index: -1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 50px 40px;
            border-radius: 20px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .logo p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin: 5px 0 0 0;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating input {
            border: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            background: transparent;
            color: white;
            border-radius: 0;
            padding: 10px 0;
            font-size: 1rem;
        }

        .form-floating input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-floating input {
            border: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.15);
            color: #222;
            border-radius: 6px;
            padding: 12px 10px;
            font-size: 1rem;
        }

        .form-floating input::placeholder {
            color: rgba(0, 0, 0, 0.5);
        }

        .form-floating label {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 400;
        }

        .form-floating input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .btn-login {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
            background: rgba(255, 0, 0, 0.2);
            color: white;
            backdrop-filter: blur(5px);
            margin-bottom: 20px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #ff6b6b;
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .floating-elements::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: 20%;
            right: 20%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-elements::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: 30%;
            left: 10%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="floating-elements"></div>
        <div class="login-bg"></div>
        <div class="login-card">
            <div class="logo">
                <h1>🎬 Cinema</h1>
                <p>Trải nghiệm thế giới phim ảnh</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-floating">
                    <input type="text" class="form-control" id="usernameOrEmail" name="usernameOrEmail"
                        placeholder="Tên đăng nhập hoặc Email" required>
                    <label for="usernameOrEmail">Tên đăng nhập hoặc Email</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu"
                        required>
                    <label for="password">Mật khẩu</label>
                </div>

                <button type="submit" class="btn btn-login">Đăng nhập ngay</button>
            </form>

            <div class="register-link">
                <a href="register.php">Chưa có tài khoản? <span style="color: #ff6b6b;">Đăng ký</span></a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>