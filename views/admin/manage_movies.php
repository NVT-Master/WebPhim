<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

$conn = getDbConnection();
// Lấy danh sách phim
$result = $conn->query("SELECT * FROM movies ORDER BY id DESC");

// Hiển thị thông báo thành công hoặc lỗi
$success = $_SESSION['success'] ?? null;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['success'], $_SESSION['errors']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Phim - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #2d3436 100%);
            color: #fff;
        }

        .navbar {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.9), rgba(45, 52, 54, 0.9));
            padding: 0.5rem 1rem;
            border-bottom: 2px solid #ffbb00;
        }

        .navbar .nav-link {
            color: #fff !important;
        }

        .navbar .navbar-brand {
            color: #ffbb00 !important;
        }

        .navbar .auth-buttons a.nav-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.85rem !important;
            line-height: 1 !important;
            text-decoration: none;
        }

        .navbar #themeToggle {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.9rem !important;
            line-height: 1 !important;
        }

        .admin-heading {
            color: #ffbb00;
            font-weight: 600;
            margin: 30px 0;
        }

        .btn-add-movie {
            background: #ffbb00;
            color: #000;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            margin-bottom: 20px;
            display: inline-block;
        }

        .btn-add-movie:hover {
            background: #fff;
            color: #ffbb00;
            text-decoration: none;
            transform: scale(1.05);
        }

        .movie-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            background-color: rgba(45, 52, 54, 0.9);
        }

        .movie-card:hover {
            transform: scale(1.03);
        }

        .movie-card img {
            height: 300px;
            object-fit: cover;
        }

        .btn-edit,
        .btn-delete {
            border-radius: 8px;
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
            margin-right: 5px;
        }

        .btn-edit:hover {
            background: #ffdb4d;
            color: #000;
        }

        .btn-delete {
            background: #dc3545;
            color: #fff;
        }

        .btn-delete:hover {
            background: #e55;
            color: #fff;
        }

        /* Light/Dark Mode */
        body.light-mode {
            background: #fff;
            color: #000;
        }

        body.light-mode .movie-card {
            background-color: #f8f9fa;
            color: #000;
        }

        body.dark-mode .movie-card {
            background-color: rgba(45, 52, 54, 0.9);
            color: #fff;
        }
    </style>
</head>

<body>
    <!-- Navbar admin -->
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
                    <li class="nav-item"><a class="nav-link" href="manage_movies.php"><i class="fas fa-film"></i> Quản
                            lý Phim</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_showtimes.php"><i
                                class="fas fa-calendar-alt"></i> Quản Lịch Chiếu</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Người
                            Dùng</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="auth-buttons">
                        <a class="nav-link" href="../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i> Đăng
                            xuất</a>
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

    <div class="container my-5">
        <h2 class="text-center admin-heading">🎬 Quản lý Phim</h2>
        <!-- thông báo  -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alertMessage">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertMessage">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Nút thêm phim -->
        <a href="add_movie.php" class="btn-add-movie"><i class="fas fa-plus"></i> Thêm Phim Mới</a>

        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card movie-card">
                        <img src="../../images/posters/<?= htmlspecialchars($row['poster_url']) ?>" class="card-img-top"
                            alt="<?= htmlspecialchars($row['title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text">Thể loại: <?= htmlspecialchars($row['genre']) ?></p>
                            <p class="card-text">Thời lượng: <?= htmlspecialchars($row['duration_min']) ?> phút</p>
                            <a href="edit_movie.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-edit"><i
                                    class="fas fa-edit"></i> Sửa</a>
                            <a href="../../handle/delete_movie_process.php?id=<?= $row['id'] ?>"
                                class="btn btn-sm btn-delete"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa phim này không?');"><i
                                    class="fas fa-trash"></i> Xóa</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
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
        const alertMessage = document.getElementById('alertMessage');
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.style.display = 'none';
            }, 3000); // 3 giây
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>