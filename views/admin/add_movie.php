<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$conn = getDbConnection();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Phim - Admin</title>
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

        .navbar .nav-link,
        .navbar .btn {
            color: #fff;
        }

        .card {
            background-color: rgba(45, 52, 54, 0.9);
            color: #fff;
        }

        .form-control {
            background-color: #2d3436;
            color: #fff;
            border: 1px solid #555;
        }

        .form-control:focus {
            background-color: #2d3436;
            color: #fff;
            border-color: #ffbb00;
            box-shadow: none;
        }

        .btn-warning {
            background-color: #ffbb00;
            color: #000;
            border: none;
        }

        .btn-warning:hover {
            background-color: #fff;
            color: #ffbb00;
        }

        body.light-mode {
            background: #f8f9fa;
            color: #000;
        }

        body.light-mode .card {
            background-color: #fff;
            color: #000;
        }

        body.light-mode .form-control {
            background-color: #fff;
            color: #000;
            border: 1px solid #ccc;
        }

        body.light-mode .form-control:focus {
            border-color: #ffbb00;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <div class="d-flex ms-auto">
                <a href="manage_movies.php" class="btn btn-secondary me-2">Quay lại</a>
            </div>
        </div>
        <div>
            <li class="nav-item">
                <a href="#" class="nav-link" id="themeToggle"><i class="fas fa-moon"></i></a>
            </li>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card p-4 shadow" style="max-width: 800px; margin: auto;">
            <h2 class="text-center mb-4">🎬 Thêm Phim Mới</h2>
            <form action="../../handle/add_movie_process.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="genre" class="form-label">Thể loại</label>
                    <input type="text" name="genre" id="genre" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="duration_min" class="form-label">Thời lượng (phút)</label>
                    <input type="number" name="duration_min" id="duration_min" class="form-control" min="1">
                </div>
                <div class="mb-3">
                    <label for="rating" class="form-label">Đánh giá</label>
                    <input type="text" name="rating" id="rating" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="poster" class="form-label">Hình ảnh Poster</label>
                    <input type="file" name="poster" id="poster" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="trailer_url" class="form-label">URL Trailer</label>
                    <input type="url" name="trailer_url" id="trailer_url" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="release_date" class="form-label">Ngày phát hành</label>
                    <input type="date" name="release_date" id="release_date" class="form-control">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-plus"></i> Thêm Phim</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const body = document.body;
        const toggleBtn = document.getElementById("themeToggle");

        function setTheme(theme) {
            if (theme === 'light') {
                body.classList.add('light-mode');
                body.classList.remove('dark-mode');
                toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
                toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
            }
        }

        // Load theme
        if (localStorage.getItem('theme') === 'light') {
            setTheme('light');
        } else {
            setTheme('dark');
        }

        // Toggle theme
        toggleBtn.addEventListener('click', e => {
            e.preventDefault();
            if (body.classList.contains('dark-mode')) {
                setTheme('light');
                localStorage.setItem('theme', 'light');
            } else {
                setTheme('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>