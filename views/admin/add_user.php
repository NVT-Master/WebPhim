<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php");
    exit();
}

$conn = getDbConnection();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Người Dùng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Body */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0f0f, #2d3436);
            color: #fff;
            /* chữ mặc định màu trắng */
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.9), rgba(45, 52, 54, 0.9));
            padding: 0.5rem 1rem;
            border-bottom: 2px solid #ffbb00;
        }

        .navbar .nav-link {
            color: #fff !important;
            transition: all 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: #ffbb00 !important;
            background: rgba(255, 187, 0, 0.1);
            border-radius: 5px;
            transform: translateY(-2px);
        }

        .navbar-brand {
            color: #ffbb00 !important;
            font-weight: 700;
        }

        /* Card Form */
        .card {
            background: rgba(45, 52, 54, 0.95);
            border-radius: 20px;
        }

        /* Tiêu đề form */
        .card h2 {
            color: #fff;
            /* chữ tiêu đề trắng */
            text-align: center;
            margin-bottom: 1.5rem;
        }

        /* Nhãn (label) */
        .form-label {
            color: #fff;
            /* chữ label trắng */
            font-weight: 500;
        }

        /* Input */
        .form-control {
            border-radius: 25px;
            border: 2px solid #ffbb00;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            box-shadow: 0 0 8px rgba(255, 187, 0, 0.5);
        }

        /* Select */
        .form-select {
            border-radius: 25px;
            border: 2px solid #ffbb00;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            box-shadow: 0 0 8px rgba(255, 187, 0, 0.5);
        }

        /* Button Thêm */
        .btn-submit {
            background: #ffbb00;
            color: #000;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #fff;
            color: #ffbb00;
            transform: scale(1.05);
        }

        /* Button Quay lại */
        .btn-back {
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: scale(1.05);
        }

        /* Alert tự ẩn */
        .alert-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            opacity: 0.95;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../../index.php"><i class="fas fa-film"></i> MovieBooking</a>
            <div class="d-flex ms-auto">
                <a href="manage_users.php" class="btn btn-secondary me-2">Quay lại</a>
            </div>
        </div>
        <div>
            <li class="nav-item">
                <a href="#" class="nav-link" id="themeToggle"><i class="fas fa-moon"></i></a>
            </li>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card p-4 mx-auto" style="max-width: 600px;">
            <h2 class="text-center mb-4">➕ Thêm Người Dùng Mới</h2>

            <!-- Thông báo thành công / lỗi -->
            <?php if (isset($_SESSION['add_user_success'])): ?>
                <div class="alert alert-success alert-custom" id="alertMessage">
                    <?= $_SESSION['add_user_success'] ?>
                </div>
                <?php unset($_SESSION['add_user_success']); ?>
            <?php elseif (isset($_SESSION['add_user_error'])): ?>
                <div class="alert alert-danger alert-custom" id="alertMessage">
                    <?= $_SESSION['add_user_error'] ?>
                </div>
                <?php unset($_SESSION['add_user_error']); ?>
            <?php endif; ?>

            <form action="../../handle/add_user_process.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên người dùng</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="text" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Vai trò</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="USER">USER</option>
                        <option value="ADMIN">ADMIN</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-submit btn-lg"><i class="fas fa-plus"></i> Thêm Người
                        Dùng</button>
                </div>
            </form>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    // Tự ẩn thông báo sau 1s
    const alertMessage = document.getElementById('alertMessage');
    if (alertMessage) {
        setTimeout(() => {
            alertMessage.style.display = 'none';
        }, 1000);
    }
</script>

</html>

<?php $conn->close(); ?>