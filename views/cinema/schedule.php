<?php
session_start();
require_once '../../functions/db_connection.php';

$conn = getDbConnection();

// Lấy danh sách lịch chiếu với cột start_time
$sql = "SELECT 
    s.id AS show_id, s.start_time, s.price, 
    m.title, m.poster_url,
    r.name AS room_name,
    t.name AS theater_name
FROM showtimes s
JOIN movies m ON s.movie_id = m.id
JOIN rooms r ON s.room_id = r.id
JOIN theaters t ON r.theater_id = t.id
ORDER BY s.start_time ASC";
$stmt = $conn->prepare($sql);
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi query: " . $conn->error);
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
        body {
            background: linear-gradient(135deg, #0f0f0f 0%, #2d3436 100%);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
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

    <!-- Schedule Section -->
    <div class="schedule-table">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">📅 Lịch Chiếu Phim</h2>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="schedule-card" data-aos="fade-up">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="../../images/posters/<?= htmlspecialchars($row['poster_url']) ?>"
                                    class="img-fluid rounded" alt="<?= htmlspecialchars($row['title']) ?>"
                                    style="max-height: 250px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text">Thời gian: <?= date('H:i d/m/Y', strtotime($row['start_time'])) ?></p>
                                <p class="card-text">Rạp: <?= htmlspecialchars($row['theater_name']) ?> -
                                    <?= htmlspecialchars($row['room_name']) ?></p>
                                <p class="card-text">Giá vé: <?= number_format($row['price'], 0) ?> VND</p>
                                <p class="card-text">Ghế trống: 50 (Tạm thời)</p>

                                <a href="book.php?show_id=<?= htmlspecialchars($row['show_id']) ?>" class="btn btn-book">
                                    Đặt vé
                                </a>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">Không có lịch chiếu nào được tìm thấy.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved. | <a href="contact.php"
                    style="color: #ffbb00ff;">Liên hệ</a></p>
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