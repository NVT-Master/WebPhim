<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}

$conn = getDbConnection();

if (isset($_GET['id'])) {
    $movieId = intval($_GET['id']);
    // Lấy poster để xóa file ảnh
    $stmt = $conn->prepare("SELECT poster_url FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();

    if ($movie) {
        // Xóa phim
        $stmtDelete = $conn->prepare("DELETE FROM movies WHERE id = ?");
        $stmtDelete->bind_param("i", $movieId);

        if ($stmtDelete->execute()) {
            // Xóa poster trên server nếu có
            if ($movie['poster_url'] && file_exists(__DIR__ . '/../images/posters/' . $movie['poster_url'])) {
                unlink(__DIR__ . '/../images/posters/' . $movie['poster_url']);
            }
            $_SESSION['success'] = "🎉 Xóa phim thành công!";
        } else {
            $_SESSION['errors'] = ["Xóa phim thất bại: " . $stmtDelete->error];
        }

        $stmtDelete->close();
    } else {
        $_SESSION['errors'] = ["Phim không tồn tại!"];
    }
} else {
    $_SESSION['errors'] = ["ID phim không hợp lệ!"];
}

$conn->close();
header("Location: ../views/admin/manage_movies.php");
exit();
