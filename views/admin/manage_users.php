<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

// Kiểm tra quyền ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php");
    exit();
}

$conn = getDbConnection();
$users = $conn->query("SELECT id, name, email, role FROM users ORDER BY id ASC");

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
    <title>Quản lý Người Dùng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background: linear-gradient(135deg, #0f0f0f 0%, #2d3436 100%);
            color: #fff;
            font-family: "Poppins", sans-serif;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
            padding: 20px;
        }

        footer {
            background-color: #222;
            color: #fff;
            padding: 20px 0;
            text-align: center;
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

        .btn-add-user {
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

        .btn-add-user:hover {
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

        /* Bảng quản lý người dùng */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .table-custom th,
        .table-custom td {
            padding: 0.75rem 1rem;
            text-align: left;
            transition: all 0.3s;
        }

        /* Header */
        .table-custom th {
            background-color: #ffbb00 !important;
            color: #000 !important;
        }

        /* Dòng dữ liệu */
        .table-custom td {
            background-color: rgba(45, 52, 54, 0.9) !important;
            color: #fff !important;
        }

        .table-custom tr:hover td {
            background-color: rgba(70, 70, 70, 0.9) !important;
        }

        /* Light mode */
        body.light-mode .table-custom th {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        body.light-mode .table-custom td {
            background-color: #fff !important;
            color: #000 !important;
            border-bottom: 1px solid #ccc !important;
        }

        /* Dark mode */
        body.dark-mode .table-custom th {
            background-color: #ffbb00 !important;
            color: #000 !important;
        }

        body.dark-mode .table-custom td {
            background-color: rgba(45, 52, 54, 0.9) !important;
            color: #fff !important;
            border-bottom: 1px solid #ffbb00 !important;
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
                    <li class="nav-item"><a class="nav-link" href="manage_rooms.php"><i class="fas fa-door-open"></i>
                            Quản lý Phòng</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users"></i> Người
                            Dùng</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="auth-buttons">
                        <a class="nav-link" href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i>
                            Đăng
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


    <!-- HTML... -->
    <div class="container my-5">
        <h2 class="text-center admin-heading">👥 Quản lý Người Dùng</h2>
        <!-- Thông báo lỗi hoặc thành công -->
        <?php if (isset($_SESSION['add_user_success'])): ?>
            <div class="alert alert-success alert-custom" id="alertMessage">
                <?= $_SESSION['add_user_success'] ?>
            </div>
            <?php unset($_SESSION['add_user_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['add_user_error'])): ?>
            <div class="alert alert-danger alert-custom" id="alertMessage">
                <?= $_SESSION['add_user_error'] ?>
            </div>
            <?php unset($_SESSION['add_user_error']); ?>
        <?php endif; ?>
        <!-- Thông báo -->
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
        <a href="add_user.php" class="btn btn-add-user"><i class="fas fa-plus"></i> Thêm Người Dùng</a>

        <!-- Bảng người dùng -->
        <div class="table-responsive">
            <table class="table table-borderless table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tài khoản</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i
                                        class="fas fa-pen"></i> Sửa</a>
                                <a href="../../handle/delete_user_process.php?id=<?= $row['id'] ?>"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này không?')"
                                    class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved.
                | <a href="../../views/contact.php" style="color: #ffbb00ff;">Liên hệ</a></p>
        </div>
    </footer>
    <script>
        // Tự ẩn thông báo sau 3 giây
        const alertMessage = document.getElementById('alertMessage');
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.style.display = 'none';
            }, 3000);
        }
    </script>
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