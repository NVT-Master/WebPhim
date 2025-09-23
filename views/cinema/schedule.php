<?php
session_start();
require_once '../../functions/db_connection.php';

$conn = getDbConnection();

// Lấy danh sách lịch chiếu + thông tin phim
$sql = "SELECT 
    s.id AS show_id, s.start_time, s.price, s.room_id,
    m.id AS movie_id, m.title, m.poster_url,
    r.name AS room_name,
    t.name AS theater_name
FROM showtimes s
JOIN movies m ON s.movie_id = m.id
JOIN rooms r ON s.room_id = r.id
JOIN theaters t ON r.theater_id = t.id
ORDER BY m.id, s.start_time ASC";

$result = $conn->query($sql);

// Gom nhóm theo movie_id
$movies = [];
while ($row = $result->fetch_assoc()) {
    $movie_id = $row['movie_id'];
    if (!isset($movies[$movie_id])) {
        $movies[$movie_id] = [
            'title' => $row['title'],
            'poster_url' => $row['poster_url'],
            'showtimes' => []
        ];
    }

    // Tính số ghế
    $show_id = $row['show_id'];

    // Tổng ghế
    $stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM seats WHERE room_id = ?");
    $stmt_total->bind_param("i", $row['room_id']);
    $stmt_total->execute();
    $total_seats = $stmt_total->get_result()->fetch_assoc()['total'];

    // Ghế đã đặt
    $stmt_booked = $conn->prepare("SELECT COUNT(DISTINCT bi.seat_id) as booked 
        FROM booking_items bi 
        JOIN bookings b ON bi.booking_id = b.id 
        WHERE b.showtime_id = ? AND b.status IN ('PENDING', 'CONFIRMED')");
    $stmt_booked->bind_param("i", $show_id);
    $stmt_booked->execute();
    $booked_seats = $stmt_booked->get_result()->fetch_assoc()['booked'];

    $available_seats = $total_seats - $booked_seats;

    $movies[$movie_id]['showtimes'][] = [
        'show_id' => $show_id,
        'start_time' => $row['start_time'],
        'price' => $row['price'],
        'theater_name' => $row['theater_name'],
        'room_name' => $row['room_name'],
        'available_seats' => $available_seats > 0 ? $available_seats : 0
    ];
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Chiếu - MovieBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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

        .navbar {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.9), rgba(45, 52, 54, 0.9));
            padding: 0.5rem 1rem;
            border-bottom: 2px solid #ffbb00ff;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(255, 111, 97, 0.3);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(0, 0, 0, 0.95);
            padding: 0.3rem 1rem;
        }

        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffbb00ff !important;
            text-shadow: 1px 1px 6px rgba(255, 111, 97, 0.4);
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 0.5rem;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            padding: 0.4rem 0.8rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-radius: 5px;
        }

        .nav-link i {
            margin-right: 0.3rem;
        }

        .nav-link:hover {
            color: #ffbb00ff !important;
            background: rgba(255, 187, 0, 0.1);
            transform: translateY(-2px);
        }

        .navbar-toggler {
            border: none;
            padding: 0.2rem 0.4rem;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 187, 0, 1)' stroke-width='2' stroke-linecap='round' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .form-control {
            border-radius: 20px;
            border: 2px solid #ffbb00ff;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 0.9rem;
            height: 38px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 8px rgba(255, 111, 97, 0.4);
        }

        .form-control::placeholder {
            color: #ccc;
        }

        .btn-search {
            background: #ffbb00ff;
            border: none;
            border-radius: 20px;
            padding: 0.4rem 1.2rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-left: 0.5rem;
        }

        .btn-search:hover {
            background: #e65b50;
            transform: scale(1.05);
        }

        .auth-buttons .nav-link {
            padding: 0.4rem 1rem;
            margin-left: 0.5rem;
            background: rgba(255, 187, 0, 0.1);
        }

        .auth-buttons .nav-link:hover {
            background: rgba(255, 187, 0, 0.2);
        }

        .schedule-table {
            padding: 60px 20px;
        }

        .schedule-card {
            background: #2d3436;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(255, 187, 0, 0.3);
        }

        .schedule-card .card-title {
            color: #ffbb00ff;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .schedule-card .card-text {
            color: #bbb;
            font-size: 1rem;
        }

        .btn-book {
            background: #ffbb00ff;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-book:hover {
            background: #00ff0dff;
            transform: scale(1.1);
        }

        footer {
            background: #1a1a1a;
            padding: 2.5rem 0;
            border-top: 3px solid #ffbb00ff;
            margin-top: 60px;
        }

        footer p {
            margin: 0;
            color: #999;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }

            .nav-link {
                font-size: 0.9rem;
                padding: 0.3rem 0.6rem;
            }

            .form-control {
                height: 34px;
                font-size: 0.85rem;
            }

            .btn-search {
                padding: 0.3rem 1rem;
                font-size: 0.85rem;
            }

            .schedule-card .card-title {
                font-size: 1.2rem;
            }

            .schedule-card .card-text {
                font-size: 0.9rem;
            }
        }
    </style>
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
                            <a class="nav-link" href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>
                                Đăng
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

    <!-- Schedule Section -->
    <div class="schedule-table">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">📅 Lịch Chiếu Phim</h2>
            <?php if (!empty($movies)): ?>
                <?php foreach ($movies as $movie): ?>
                    <div class="schedule-card" data-aos="fade-up">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="../../images/posters/<?= htmlspecialchars($movie['poster_url']) ?>"
                                    class="img-fluid rounded" alt="<?= htmlspecialchars($movie['title']) ?>"
                                    style="max-height: 250px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                                <div class="mt-3">
                                    <?php foreach ($movie['showtimes'] as $show): ?>
                                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                            <span>
                                                🕒 <?= date('H:i d/m/Y', strtotime($show['start_time'])) ?> |
                                                🎬 <?= htmlspecialchars($show['theater_name']) ?> -
                                                <?= htmlspecialchars($show['room_name']) ?>
                                            </span>
                                            <span>
                                                💰 <?= number_format($show['price'], 0) ?> VND |
                                                🎟 Ghế trống: <?= $show['available_seats'] ?>
                                            </span>
                                            <a href="book.php?show_id=<?= $show['show_id'] ?>" class="btn btn-sm btn-book">
                                                Đặt vé
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có lịch chiếu nào được tìm thấy.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved.
                | <a href="../../views/contact.php" style="color: #ffbb00ff;">Liên hệ</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();

        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>