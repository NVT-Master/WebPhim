<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);
    if ($stmt->execute() && $conn->affected_rows > 0) {
        $reset_link = "http://localhost/WebPhim/views/auth/reset_password.php?token=" . $token;
        $success = "Link reset đã được gửi đến $email. Vui lòng kiểm tra hộp thư.";
    } else {
        $error = "Email không tồn tại!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu - MovieBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>

<body>
    <script>
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto me-2">
                    <li class="nav-item"><a class="nav-link" href="../index.php"><i class="fas fa-home"></i> Trang
                            chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="../views/cinema/schedule.php"><i
                                class="fas fa-calendar-alt"></i> Lịch Chiếu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'ADMIN'): ?>
                            <li class="nav-item"><a class="nav-link" href="../views/admin/index.php"><i class="fas fa-cog"></i>
                                    Quản lý</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="../views/cinema/my_tickets.php"><i
                                    class="fas fa-ticket-alt"></i> Vé Của Tôi</a></li>
                        <li class="nav-item"><a class="nav-link" href="../views/cinema/profile.php"><i
                                    class="fas fa-user"></i> Hồ Sơ</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <form class="d-flex me-2" method="GET" action="../index.php">
                        <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm..."
                            value="<?= htmlspecialchars($search ?? '') ?>">
                        <button class="btn btn-search" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="auth-buttons">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="nav-link" href="../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i> Đăng
                                xuất</a>
                        <?php else: ?>
                            <div class="d-flex">
                                <a class="nav-link me-2" href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                                <a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <li class="nav-item">
                <a href="#" class="nav-link" id="themeToggle"><i class="fas fa-moon"></i></a>
            </li>
        </div>
    </nav>

    <!-- Content -->
    <div class="movie-grid">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">🔑 Quên Mật Khẩu</h2>
            <?php if (isset($error))
                echo "<p class='text-danger text-center'>$error</p>"; ?>
            <?php if (isset($success))
                echo "<p class='text-success text-center'>$success</p>"; ?>
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                    <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                </div>
                <button type="submit" class="btn btn-book w-100">Gửi Link Reset</button>
                <p class="mt-3 text-center"><a href="login.php" style="color: #ffbb00ff;">Quay lại Đăng nhập</a></p>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved. | <a href="../views/contact.php"
                    style="color: #ffbb00ff;">Liên hệ</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();

        // Theme toggle
        const body = document.body;
        const toggleBtn = document.getElementById("themeToggle");
        if (localStorage.getItem("theme") === "light") {
            body.classList.remove("dark-mode");
            body.classList.add("light-mode");
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            body.classList.add("dark-mode");
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }
        toggleBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (body.classList.contains("dark-mode")) {
                body.classList.remove("dark-mode");
                body.classList.add("light-mode");
                toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
                localStorage.setItem("theme", "light");
            } else {
                body.classList.remove("light-mode");
                body.classList.add("dark-mode");
                toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
                localStorage.setItem("theme", "dark");
            }
        });

        // Validation form
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>