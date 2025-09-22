<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$conn = getDbConnection();
$total_movies = $conn->query("SELECT COUNT(*) as count FROM movies")->fetch_assoc()['count'] ?? 0;
$total_showtimes = $conn->query("SELECT COUNT(*) as count FROM showtimes")->fetch_assoc()['count'] ?? 0;
$total_tickets = $conn->query("SELECT COUNT(*) as count FROM tickets")->fetch_assoc()['count'] ?? 0;
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý - MovieBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    
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
            <a class="navbar-brand" href="../../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto me-2">
                    <li class="nav-item"><a class="nav-link" href="../../index.php"><i class="fas fa-home"></i> Trangchủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_movies.php"><i class="fas fa-film"></i> Quảnlý Phim</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_showtimes.php"><i class="fas fa-calendar-alt"></i> Quản lý Lịch Chiếu</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Quảnlý Người Dùng</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="auth-buttons">
                        <a class="nav-link" href="../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
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
            <h2 class="text-center mb-5" data-aos="fade-up">📊 Bảng Điều Khiển Admin</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card movie-card">
                        <div class="card-body">
                            <h5 class="card-title">Tổng Phim</h5>
                            <p class="card-text display-4"><?= $total_movies ?></p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card movie-card">
                        <div class="card-body">
                            <h5 class="card-title">Tổng Lịch Chiếu</h5>
                            <p class="card-text display-4"><?= $total_showtimes ?></p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card movie-card">
                        <div class="card-body">
                            <h5 class="card-title">Tổng Vé Đã Đặt</h5>
                            <p class="card-text display-4"><?= $total_tickets ?></p>
                        </div>
                    </div>
                </div>
            </div>
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
    </script>
</body>

</html>