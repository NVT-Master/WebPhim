<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php");
    exit;
}

$conn = getDbConnection();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header("Location: manage_showtimes.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM showtimes WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$showtime = $stmt->get_result()->fetch_assoc();
$stmt->close();

$movies = $conn->query("SELECT id, title FROM movies ORDER BY title ASC");
$rooms = $conn->query("SELECT id, name FROM rooms ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Xuất Chiếu - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <div class="d-flex ms-auto">
                <a href="manage_showtimes.php" class="btn btn-secondary me-2">Quay lại</a>
                <a href="#" id="themeToggle" class="btn btn-light"><i class="fas fa-moon"></i></a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4">✏️ Sửa Xuất Chiếu</h2>
        <form action="../../handle/edit_showtime_process.php" method="POST">
            <input type="hidden" name="id" value="<?= $showtime['id'] ?>">

            <div class="mb-3">
                <label for="movie_id" class="form-label">Phim</label>
                <select name="movie_id" id="movie_id" class="form-select" required>
                    <?php while ($movie = $movies->fetch_assoc()): ?>
                        <option value="<?= $movie['id'] ?>" <?= $movie['id'] == $showtime['movie_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($movie['title']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="room_id" class="form-label">Phòng</label>
                <select name="room_id" id="room_id" class="form-select" required>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <option value="<?= $room['id'] ?>" <?= $room['id'] == $showtime['room_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($room['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Thời gian</label>
                <input type="datetime-local" name="start_time" id="start_time" class="form-control"
                    value="<?= date('Y-m-d\TH:i', strtotime($showtime['start_time'])) ?>" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Giá vé (VND)</label>
                <input type="number" name="price" id="price" class="form-control" value="<?= $showtime['price'] ?>"
                    min="0" required>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const body = document.body;
        const toggleBtn = document.getElementById("themeToggle");
        if (localStorage.getItem("theme") === "light") { body.classList.add("light-mode"); toggleBtn.innerHTML = '<i class="fas fa-sun"></i>'; } else { body.classList.add("dark-mode"); toggleBtn.innerHTML = '<i class="fas fa-moon"></i>'; }
        toggleBtn.addEventListener("click", (e) => { e.preventDefault(); if (body.classList.contains("dark-mode")) { body.classList.remove("dark-mode"); body.classList.add("light-mode"); toggleBtn.innerHTML = '<i class="fas fa-sun"></i>'; localStorage.setItem("theme", "light"); } else { body.classList.remove("light-mode"); body.classList.add("dark-mode"); toggleBtn.innerHTML = '<i class="fas fa-moon"></i>'; localStorage.setItem("theme", "dark"); } });
    </script>
</body>

</html>
<?php $conn->close(); ?>