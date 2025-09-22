<?php
// C:\xampp\htdocs\WebPhim\functions\movie_functions.php
require_once __DIR__. '../functions/db_connection.php'; // Sử dụng file db_connection.php hiện có của bạn

/**
 * Lấy toàn bộ danh sách phim từ cơ sở dữ liệu
 * @return array Danh sách các phim dưới dạng mảng kết hợp
 */
function getAllMovies() {
    global $conn; // Sử dụng kết nối từ db_connection.php
    $result = mysqli_query($conn, "SELECT * FROM movies ORDER BY release_date DESC");
    $movies = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    return $movies;
}

/**
 * Lấy thông tin chi tiết của một phim dựa trên ID
 * @param int $movieId ID của phim
 * @return array Thông tin phim dưới dạng mảng kết hợp, hoặc null nếu không tìm thấy
 */
function getMovieById($movieId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();
    return $movie;
}

/**
 * Lấy danh sách phim theo thể loại
 * @param string $genre Thể loại phim
 * @return array Danh sách phim thuộc thể loại đó
 */
function getMoviesByGenre($genre) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM movies WHERE genre = ? ORDER BY release_date DESC");
    $stmt->bind_param("s", $genre);
    $stmt->execute();
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $movies;
}

/**
 * Thêm một phim mới vào cơ sở dữ liệu (dành cho admin)
 * @param string $title Tiêu đề phim
 * @param string $description Mô tả
 * @param int $durationMin Thời lượng (phút)
 * @param string $rating Xếp hạng
 * @param string $posterUrl URL áp phích
 * @param string $trailerUrl URL trailer
 * @param string $releaseDate Ngày phát hành
 * @param string $genre Thể loại
 * @return bool Thành công hoặc thất bại
 */
function addMovie($title, $description, $durationMin, $rating, $posterUrl, $trailerUrl, $releaseDate, $genre) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO movies (title, description, duration_min, rating, poster_url, trailer_url, release_date, genre, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssisssss", $title, $description, $durationMin, $rating, $posterUrl, $trailerUrl, $releaseDate, $genre);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}
?>