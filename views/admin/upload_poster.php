<?php
// file này chỉ để thêm mấy cái poster lỗi hình thôi nhưng mà k nhớ id thì k làm gì được :>>>>
session_start();
require_once '../../functions/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../auth/login.php");
    exit;
}

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieId = $_POST['movie_id'];
    $posterUrl = '';

    // Xử lý upload ảnh
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['poster'];
        $fileName = basename($file['name']); // Lấy tên file gốc
        $fileTmp = $file['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Lấy phần mở rộng
        $allowedExt = ['jpg', 'jpeg', 'png']; // Các định dạng cho phép

        // Tạo tên file duy nhất để tránh trùng lặp
        $newFileName = uniqid() . '_' . $movieId . '.' . $fileExt;
        $uploadPath = __DIR__ . '/../../images/posters/' . $newFileName;

        // Kiểm tra định dạng và upload
        if (in_array($fileExt, $allowedExt)) {
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $posterUrl = $newFileName; // Lưu tên file mới vào database
                $stmt = $conn->prepare("UPDATE movies SET poster_url = ? WHERE id = ?");
                $stmt->bind_param("si", $posterUrl, $movieId);
                $stmt->execute();
                $stmt->close();
                echo "<script>alert('Tải poster thành công!'); window.location.href='upload_poster.php';</script>";
            } else {
                echo "<script>alert('Lỗi khi tải ảnh lên server!');</script>";
            }
        } else {
            echo "<script>alert('Chỉ chấp nhận file JPG, JPEG, PNG!');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tải Poster Lên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; }
        .container { max-width: 600px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tải Poster cho Phim</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ID Phim</label>
                <input type="number" name="movie_id" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Chọn File Poster (JPG, JPEG, PNG)</label>
                <input type="file" name="poster" class="form-control" accept=".jpg,.jpeg,.png" required>
            </div>
            <button type="submit" class="btn btn-primary">Tải Lên</button>
        </form>
    </div>
</body>
</html>