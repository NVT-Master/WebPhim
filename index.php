<?php
session_start();
require_once __DIR__ . '/functions/db_connection.php';

$conn = getDbConnection();

// Xử lý tìm kiếm
$search = "";
if (isset($_GET['search']) && $_GET['search'] !== "") {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM movies WHERE title LIKE ?");
    $likeSearch = "%" . $search . "%";
    $stmt->bind_param("s", $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM movies");
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Vé Xem Phim - MovieBooking</title>
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

        /* Navbar */
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
            /* chữ nhập vào sẽ trắng */
            font-size: 0.9rem;
            height: 38px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            /* giữ trắng khi focus */
            box-shadow: 0 0 8px rgba(255, 111, 97, 0.4);
        }

        .form-control::placeholder {
            color: #ccc;
            /* placeholder xám sáng */
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

        /* Banner */
        .carousel {
            position: relative;
            overflow: hidden;
        }

        .carousel-item img {
            height: 650px;
            object-fit: cover;
            filter: brightness(50%);
        }

        .carousel-caption {
            top: 50%;
            transform: translateY(-50%);
            text-align: left;
            padding-left: 8%;
        }

        .carousel-caption h2 {
            font-size: 4rem;
            font-weight: 800;
            text-shadow: 3px 3px 15px rgba(0, 0, 0, 0.8);
            animation: fadeInDown 1s ease-out;
        }

        .carousel-caption p {
            font-size: 1.5rem;
            color: #ddd;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
            animation: fadeInUp 1s ease-out 0.5s backwards;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Movie Grid */
        .movie-grid {
            padding: 60px 20px;
            background: rgba(0, 0, 0, 0.1);
        }

        .movie-card {
            background: #2d3436;
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .movie-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 30px rgba(255, 254, 254, 1);
        }

        .movie-card img {
            height: 350px;
            object-fit: cover;
            border-bottom: 3px solid #ffbb00ff;
        }

        .movie-card .card-body {
            background: #222;
            padding: 1.8rem;
        }

        .movie-card .card-title {
            font-size: 1.5rem;
            color: #ffbb00ff;
            margin-bottom: 0.7rem;
        }

        .movie-card .card-text {
            font-size: 1rem;
            color: #bbb;
        }

        .btn-book {
            background: #ffbb00ff;
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-book:hover {
            background: #fff;
            transform: scale(1.1);
        }

        /* Footer */
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

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem 1rem;
            }

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

            .carousel-caption h2 {
                font-size: 2.5rem;
            }

            .carousel-caption p {
                font-size: 1.2rem;
            }

            .movie-card img {
                height: 250px;
            }

            .movie-card .card-title {
                font-size: 1.2rem;
            }

            .auth-buttons .nav-link {
                padding: 0.3rem 0.8rem;
                margin-left: 0.3rem;
            }
        }

        /* Mặc định dark */
        body.dark-mode {
            background: linear-gradient(135deg, #0f0f0f 0%, #2d3436 100%);
            color: #ffffffff;
        }

        /* Light mode */
        body.light-mode {
            background: #ffffffff;
            color: #000000ff;
        }

        body.light-mode .navbar {
            background: #ffffffff !important;
            border-bottom: 2px solid #ffbb00ff;
        }

        body.light-mode .nav-link {
            color: #000000ff !important;
        }

        body.light-mode .movie-card {
            background: #ffffffff;
            color: #212529;
        }

        body.light-mode .movie-card .card-body {
            background: #ffffffff;
        }

        body.light-mode footer {
            background: #ffffffff;
            border-top: 3px solid #ffbb00ff;
            color: #000000ff;
        }

        /* Làm chữ đậm và tối hơn khi ở light-mode */
        body.light-mode .movie-card .card-title {
            color: #111111;
            font-weight: 500;
        }

        body.light-mode .movie-card .card-text {
            color: #333333;
            font-weight: 400;
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
            <a class="navbar-brand" href="index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto me-2">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="views/cinema/schedule.php"><i
                                class="fas fa-calendar-alt"></i> Lịch Chiếu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'ADMIN'): ?>
                            <li class="nav-item"><a class="nav-link" href="views/admin/index.php"><i class="fas fa-cog"></i>Quản lý</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href=" views/cinema/my_tickets.php"><i
                                    class="fas fa-ticket-alt"></i> Vé Của Tôi</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <form class="d-flex me-2" method="GET" action="index.php">
                        <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm..."
                            value="<?= htmlspecialchars($search ?? '') ?>">
                        <button class="btn btn-search" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="auth-buttons">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="nav-link" href="handle/logout_process.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        <?php else: ?>
                            <div class="d-flex">
                                <a class="nav-link me-2" href="views/auth/login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                                <a class="nav-link" href="views/auth/register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <!-- nút chuyển đổi nền đen trắng -->
            <li class="nav-item">
                <a href="#" class="nav-link" id="themeToggle"><i class="fas fa-moon"></i></a>
            </li>
        </div>
    </nav>

    <!-- Banner -->
    <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/banner.jpg" class="d-block w-100" alt="Banner 1">
                <div class="carousel-caption">
                    <h2>Chào mừng đến MovieBooking</h2>
                    <p>Trải nghiệm điện ảnh đỉnh cao ngay hôm nay</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/banner1.png" class="d-block w-100" alt="Banner 2">
                <div class="carousel-caption">
                    <h2>Phim mới mỗi tuần</h2>
                    <p>Đặt vé nhanh chóng, giá ưu đãi</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Movie Grid -->
    <div class="movie-grid">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">📽️ Danh sách phim</h2>
            <div class="row row-cols-1 row-cols-md-4 g-4">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card movie-card">
                            <img src="images/posters/<?= htmlspecialchars($row['poster_url']) ?>" class="card-img-top"
                                alt="<?= htmlspecialchars($row['title']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text">Thể loại: <?= htmlspecialchars($row['genre']) ?></p>
                                <p class="card-text">Thời lượng: <?= htmlspecialchars($row['duration_min']) ?> phút</p>
                                <?php
                                $movieId = $row['id'];
                                $showtimeSql = "SELECT id FROM showtimes WHERE movie_id = ? ORDER BY start_time ASC LIMIT 1";
                                $stmtShow = $conn->prepare($showtimeSql);
                                $stmtShow->bind_param("i", $movieId);
                                $stmtShow->execute();
                                $showRes = $stmtShow->get_result();
                                $showtime = $showRes->fetch_assoc();
                                $stmtShow->close();
                                ?>

                                <a href="views/cinema/book.php?show_id=<?= $showtime ? $showtime['id'] : '' ?>"
                                    class="btn btn-book" <?= $showtime ? '' : 'onclick="alert(\'Chưa có lịch chiếu!\'); return false;"' ?>>
                                    Đặt vé
                                </a>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved. | <a href="views/contact.php"
                    style="color: #ffbb00ff;">Liên hệ</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();

        // Hiệu ứng khi scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // nền đen trắng
        const body = document.body;
        const toggleBtn = document.getElementById("themeToggle");

        // Kiểm tra trạng thái trong localStorage
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

<?php
$conn->close();
?>