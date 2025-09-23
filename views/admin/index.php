<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$conn = getDbConnection();

// Đếm tổng số phim
$total_movies = $conn->query("SELECT COUNT(*) AS count FROM movies")->fetch_assoc()['count'] ?? 0;

// Đếm tổng số lịch chiếu
$total_showtimes = $conn->query("SELECT COUNT(*) AS count FROM showtimes")->fetch_assoc()['count'] ?? 0;

// Đếm tổng số vé đã đặt
$total_tickets = $conn->query("SELECT COUNT(*) AS count FROM bookings WHERE status = 'CONFIRMED'")
    ->fetch_assoc()['count'] ?? 0;


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
    <link href="../../css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f 0%, #2d3436 100%);
            color: #fff;
            font-family: "Poppins", sans-serif;
        }

        .movie-card {
            background: #1e272e;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            color: #fff;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .movie-card:hover {
            transform: translateY(-5px);
        }

        .movie-card h5 {
            margin-bottom: 15px;
            font-weight: 600;
            color: #ffbb00;
        }

        .movie-card p {
            font-size: 2.5rem;
            margin: 0;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand text-warning" href="../../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link text-white" href="../../index.php"><i
                                class="fas fa-home"></i> Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="manage_movies.php"><i
                                class="fas fa-film"></i> Quản lý Phim</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="manage_showtimes.php"><i
                                class="fas fa-calendar-alt"></i> Quản Lịch Chiếu</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="manage_rooms.php"><i
                                class="fas fa-door-open"></i> Quản lý Phòng</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="manage_users.php"><i
                                class="fas fa-users"></i> Người Dùng</a></li>
                </ul>
                <a class="btn btn-warning ms-3" href="../../handle/logout_process.php"><i
                        class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </div>
    </nav>

    <!-- Nội dung -->
    <div class="container my-5">
        <h2 class="text-center mb-5" data-aos="fade-up">📊 Bảng Điều Khiển Admin</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="movie-card">
                    <h5><i class="fas fa-film"></i> Tổng Phim</h5>
                    <p><?= $total_movies ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="movie-card">
                    <h5><i class="fas fa-calendar-alt"></i> Tổng Lịch Chiếu</h5>
                    <p><?= $total_showtimes ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="movie-card">
                    <h5><i class="fas fa-ticket-alt"></i> Tổng Vé Đã Đặt</h5>
                    <p><?= $total_tickets ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-3 bg-dark">
        <p class="m-0">Copyright © <?= date("Y") ?> MovieBooking. All rights reserved.
            | <a href="../views/contact.php" class="text-warning">Liên hệ</a>
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init();</script>
</body>

</html>