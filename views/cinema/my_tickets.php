<?php
session_start();
require_once '../../functions/db_connection.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDbConnection();

// Nếu người dùng nhấn huỷ vé
if (isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);

    // Xoá trong booking_items trước (liên kết khoá ngoại)
    $stmt = $conn->prepare("DELETE FROM booking_items WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();

    // Xoá trong bookings
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();

    // Redirect để tránh submit lại form
    header("Location: my_tickets.php");
    exit;
}

// Lấy danh sách vé của user
$sql = "SELECT b.id AS booking_id, b.created_at, b.status,
               s.start_time, s.price,
               m.title, m.poster_url,
               r.name AS room_name, t.name AS theater_name,
               GROUP_CONCAT(CONCAT(se.row_label, se.seat_number) ORDER BY se.row_label, se.seat_number SEPARATOR ', ') AS seats
        FROM bookings b
        JOIN booking_items bi ON b.id = bi.booking_id
        JOIN seats se ON bi.seat_id = se.id
        JOIN showtimes s ON b.showtime_id = s.id
        JOIN movies m ON s.movie_id = m.id
        JOIN rooms r ON s.room_id = r.id
        JOIN theaters t ON r.theater_id = t.id
        WHERE b.user_id = ?
        GROUP BY b.id, s.id
        ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Vé của tôi - MovieBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background: linear-gradient(135deg, #0f0f0f 0%, #2d3436 100%);
            color: #fff;
            font-family: "Poppins", sans-serif;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
            padding: 20px;
        }

        footer {
            background-color: #222;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }

        .ticket-card {
            background: #2d3436;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            /* đổi thành card dọc */
            height: auto;
        }

        .ticket-card img {
            width: 100%;
            height: 180px;
            /* ảnh vừa khung */
            object-fit: cover;
            /* cắt ảnh để vừa */
        }

        .ticket-info {
            padding: 15px;
        }


        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 187, 0, 0.3);
        }

        .ticket-info h5 {
            color: #ffbb00;
        }

        .status {
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 10px;
        }

        .status.CONFIRMED {
            background: #28a745;
            color: #fff;
        }

        .status.PENDING {
            background: #ffc107;
            color: #000;
        }

        .status.CANCELLED {
            background: #dc3545;
            color: #fff;
        }

        .cancel-btn {
            background: #dc3545;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
        }

        .cancel-btn:hover {
            background: #b52a37;
        }
    </style>
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
                    <div class="auth-buttons">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="nav-link" href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>
                                Đăng xuất</a>
                        <?php else: ?>
                            <a class="nav-link" href="../auth/login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                            <a class="nav-link" href="../auth/register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4 text-center">🎟️ Vé của tôi</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="ticket-card">
                            <img src="../../images/posters/<?= htmlspecialchars($row['poster_url']) ?>"
                                alt="<?= htmlspecialchars($row['title']) ?>">
                            <div class="ticket-info">
                                <div>
                                    <h5><?= htmlspecialchars($row['title']) ?></h5>
                                    <p>⏰ <?= date('H:i d/m/Y', strtotime($row['start_time'])) ?></p>
                                    <p>🎬 <?= htmlspecialchars($row['theater_name']) ?> -
                                        <?= htmlspecialchars($row['room_name']) ?></p>
                                    <p>💺 Ghế: <?= htmlspecialchars($row['seats']) ?></p>
                                    <p>💵 Giá vé: <?= number_format($row['price'], 0) ?> VND</p>
                                    <p>📅 Đặt ngày: <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></p>
                                    <span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span>
                                </div>
                                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn huỷ vé này không?');">
                                    <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                                    <button type="submit" name="cancel_booking" class="cancel-btn mt-2">
                                        <i class="fas fa-times"></i> Huỷ vé
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Bạn chưa đặt vé nào.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved.
                | <a href="../../views/contact.php" style="color: #ffbb00ff;">Liên hệ</a></p>
        </div>
    </footer>
</body>

</html>

<?php $conn->close(); ?>