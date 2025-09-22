<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Xử lý update email nếu submit form
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['email'] ?? '');
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $updateStmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $updateStmt->bind_param("si", $new_email, $user_id);
        if ($updateStmt->execute()) {
            $success = "🎉 Cập nhật email thành công!";
            $user['email'] = $new_email;
        } else {
            $error = "❌ Cập nhật thất bại: " . $updateStmt->error;
        }
        $updateStmt->close();
    } else {
        $error = "❌ Email không hợp lệ.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ - MovieBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .profile-card {
            max-width: 500px;
            margin: auto;
            margin-top: 50px;
            background-color: rgba(45, 52, 54, 0.9);
            color: #fff;
            border-radius: 12px;
            padding: 20px;
        }

        .profile-card label {
            font-weight: 500;
        }

        .btn-update {
            background: #ffbb00;
            color: #000;
            border-radius: 25px;
        }

        .btn-update:hover {
            background: #fff;
            color: #ffbb00;
        }

        .alert {
            max-width: 500px;
            margin: 20px auto;
        }
    </style>
</head>

<body class="dark-mode">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto me-2">
                    <li class="nav-item"><a class="nav-link" href="../../index.php"><i class="fas fa-home"></i> Trang
                            chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="schedule.php"><i class="fas fa-calendar-alt"></i>
                            Lịch Chiếu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'ADMIN'): ?>
                            <li class="nav-item"><a class="nav-link" href="../admin/index.php"><i class="fas fa-cog"></i> Quản
                                    lý</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="my_tickets.php"><i class="fas fa-ticket-alt"></i> Vé
                                Của Tôi</a></li>
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
                            <a class="nav-link" href="../auth/login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                            <a class="nav-link" href="../auth/register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="movie-grid" data-aos="fade-up">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alertMessage">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertMessage">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="profile-card text-center">
            <h2>👤 Hồ Sơ Của Tôi</h2>
            <p><strong>Họ và tên:</strong> <?= htmlspecialchars($user['name'] ?? 'Chưa cập nhật') ?></p>

            <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'Chưa cập nhật') ?></p>
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-update" data-bs-toggle="modal" data-bs-target="#updateModal">
                Cập Nhật Thông Tin
            </button>
        </div>
    </div>

    <!-- Modal Update -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Cập Nhật Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <label for="email" class="form-label">Email mới</label>
                        <input type="email" class="form-control" name="email" id="email"
                            value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-update">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center mt-5 mb-3">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved. | <a href="../contact.php"
                    style="color: #ffbb00;">Liên hệ</a></p>
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
        toggleBtn.addEventListener("click", e => {
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

        // Auto hide alert
        const alertMessage = document.getElementById('alertMessage');
        if (alertMessage) {
            setTimeout(() => alertMessage.style.display = 'none', 3000);
        }
    </script>
</body>

</html>