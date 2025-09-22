<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách phim và phòng chiếu
$movies = $conn->query("SELECT id, title FROM movies ORDER BY title ASC");
$rooms = $conn->query("SELECT id, name FROM rooms ORDER BY id ASC");

// Lấy thông báo nếu có
$success = $_SESSION['success'] ?? '';
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['success'], $_SESSION['errors']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Suất Chiếu - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #2d3436 100%);
            color: #fff;
            transition: all 0.3s;
        }

        .container h2 {
            color: #ffbb00;
            font-weight: 600;
            margin: 30px 0;
        }

        form {
            background: rgba(45, 52, 54, 0.9);
            padding: 30px;
            border-radius: 15px;
        }

        .form-control,
        .form-select {
            border-radius: 25px;
            border: 2px solid #ffbb00;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #1e1e1e;
            color: #fff;
            border-color: #ffbb00;
            box-shadow: 0 0 8px rgba(255, 187, 0, 0.5);
        }


        .btn-submit {
            background: #ffbb00;
            color: #000;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #fff;
            color: #ffbb00;
        }

        /* Light/Dark Mode */
        body.light-mode {
            background: #fff;
            color: #000;
        }

        body.light-mode form {
            background: #f8f9fa;
            color: #000;
        }

        body.light-mode .form-control,
        body.light-mode .form-select {
            background: #fff;
            color: #000;
            border: 2px solid #ffc107;
        }

        body.dark-mode form {
            background: rgba(45, 52, 54, 0.9);
            color: #fff;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #2d3436;
            color: #fff;
            border: 2px solid #ffbb00;
        }

        /* Light mode */
        body.light-mode input,
        body.light-mode select,
        body.light-mode textarea {
            background-color: #fff;
            color: #000;
            border: 2px solid #ffbb00;
        }

        /* Dark mode */
        body.dark-mode input,
        body.dark-mode select,
        body.dark-mode textarea {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
            border: 2px solid #ffbb00;
        }

        body.dark-mode input:focus,
        body.dark-mode select:focus,
        body.dark-mode textarea:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            box-shadow: 0 0 8px rgba(255, 187, 0, 0.5);
        }

        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            background-color: #1a1a1a !important;
            /* Nền đen khi focus */
            color: #fff !important;
            border-color: #ffbb00 !important;
            box-shadow: 0 0 8px rgba(255, 187, 0, 0.5) !important;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <div class="d-flex ms-auto">
                <a href="manage_showtimes.php" class="btn btn-secondary me-2">Quay lại</a>
            </div>
        </div>
        <div>
            <li class="nav-item">
                <a href="#" class="nav-link" id="themeToggle"><i class="fas fa-moon"></i></a>
            </li>
        </div>
    </nav>
    <div class="container my-5">
        <h2>➕ Thêm Suất Chiếu</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="../../handle/add_showtime_process.php" method="POST">
            <div class="mb-3"> <label for="movie_id" class="form-label">Phim</label> <select class="form-select"
                    id="movie_id" name="movie_id" required>
                    <option value="">-- Chọn phim --</option> <?php while ($movie = $movies->fetch_assoc()): ?>
                        <option value="<?= $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></option>
                    <?php endwhile; ?>
                </select> </div>
            <div class="mb-3"> <label for="room_id" class="form-label">Phòng chiếu</label> <select class="form-select"
                    id="room_id" name="room_id" required>
                    <option value="">-- Chọn phòng --</option> <?php while ($room = $rooms->fetch_assoc()): ?>
                        <option value="<?= $room['id'] ?>"><?= htmlspecialchars($room['name']) ?></option>
                    <?php endwhile; ?>
                </select> </div>
            <div class="mb-3"> <label for="start_time" class="form-label">Ngày & giờ chiếu</label> <input
                    type="datetime-local" class="form-control" id="start_time" name="start_time" required> </div>
            <div class="mb-3"> <label for="price" class="form-label">Giá vé (VND)</label> <input type="number"
                    class="form-control" id="price" name="price" min="0" required> </div>

            <button type="submit" class="btn btn-submit"><i class="fas fa-plus"></i> Thêm Suất Chiếu</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        const body = document.body;
        const toggleBtn = document.getElementById("themeToggle");

        if (localStorage.getItem("theme") === "light") {
            body.classList.add("light-mode");
        } else {
            body.classList.add("dark-mode");
        }

        if (toggleBtn) {
            toggleBtn.addEventListener("click", e => {
                e.preventDefault();
                if (body.classList.contains("dark-mode")) {
                    body.classList.remove("dark-mode");
                    body.classList.add("light-mode");
                    localStorage.setItem("theme", "light");
                } else {
                    body.classList.remove("light-mode");
                    body.classList.add("dark-mode");
                    localStorage.setItem("theme", "dark");
                }
            });
        }
    </script>
</body>

</html>