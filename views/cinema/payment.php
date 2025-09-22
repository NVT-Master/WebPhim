<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;

$conn = getDbConnection();
$conn->set_charset('utf8mb4');

// Lấy thông tin booking, suất chiếu, phim và ghế
$stmt = $conn->prepare("
    SELECT b.id AS booking_id, b.total_amount, s.start_time, m.title, GROUP_CONCAT(se.row_label, se.seat_number SEPARATOR ', ') AS seats
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN booking_items bi ON bi.booking_id = b.id
    JOIN seats se ON se.id = bi.seat_id
    WHERE b.id = ? AND b.user_id = ?
    GROUP BY b.id
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$booking) {
    $_SESSION['error'] = "Không tìm thấy booking.";
    header("Location: ../views/cinema/my_tickets.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - MovieBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
</head>

<body>
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

    <!-- Nội dung thanh toán -->
    <div class="movie-grid" data-aos="fade-up">
        <div class="container">
            <h2 class="text-center mb-5">💳 Thanh Toán</h2>
            <div class="card movie-card shadow-sm">
                <div class="card-body">
                    <p class="card-text"><strong>Phim:</strong> <?= htmlspecialchars($booking['title']) ?></p>
                    <p class="card-text"><strong>Thời gian:</strong>
                        <?= date('H:i d/m/Y', strtotime($booking['start_time'])) ?></p>
                    <p class="card-text"><strong>Ghế đã chọn:</strong> <?= htmlspecialchars($booking['seats']) ?></p>
                    <p class="card-text"><strong>Tổng tiền:</strong>
                        <?= number_format($booking['total_amount'], 0, ',', '.') ?> VND</p>
                    <form method="POST" action="handle/payment_process.php">
                        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                        <button type="submit" class="btn btn-warning w-100 mt-3">Xác Nhận Thanh Toán</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer giống trang chủ -->
    <footer class="bg-dark text-light py-3 mt-5">
        <div class="container text-center">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved. | <a href="../contact.php"
                    style="color: #ffbb00;">Liên hệ</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="../js/script.js"></script>
    <script>AOS.init();</script>
</body>

</html>