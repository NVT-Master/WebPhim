<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

// Kiểm tra quyền ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $duration = intval($_POST['duration_min'] ?? 0);
    $rating = trim($_POST['rating'] ?? '');
    $trailer_url = trim($_POST['trailer_url'] ?? '');
    $release_date = $_POST['release_date'] ?? '';
    $poster = $_FILES['poster'] ?? null;

    $errors = [];

    // Validate form
    if (!$title)
        $errors[] = "Tên phim không được để trống";
    if ($duration <= 0)
        $errors[] = "Thời lượng phải lớn hơn 0";

    // Xử lý poster upload
    $newFileName = null;
    if ($poster && $poster['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($poster['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = "Chỉ cho phép ảnh JPG, PNG, WEBP";
        } else {
            $newFileName = uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../images/posters/';
            if (!move_uploaded_file($poster['tmp_name'], $uploadDir . $newFileName)) {
                $errors[] = "Upload poster thất bại";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration_min, rating, poster_url, trailer_url, release_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $title, $genre, $duration, $rating, $newFileName, $trailer_url, $release_date);

        if ($stmt->execute()) {
            // Thành công -> lưu session và chuyển về trang quản lý
            $_SESSION['success'] = "🎉 Thêm phim thành công!";
            header("Location: ../views/admin/manage_movies.php");
            exit();
        } else {
            $errors[] = "Thêm phim thất bại: " . $stmt->error;
        }
        $stmt->close();
    }

    // Nếu có lỗi -> lưu session và quay lại form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST; // Lưu dữ liệu cũ để điền lại form
        header("Location: ../views/admin/add_movie.php");
        exit();
    }
}

$conn->close();
?>