<?php
// C:\xampp\htdocs\WebPhim\functions\user_functions.php
require_once __DIR__ . '/db_connection.php';

/**
 * Đăng ký người dùng mới
 * @param string $username Tên đăng nhập
 * @param string $password Mật khẩu (plain text)
 * @param string $email Email
 * @return bool Thành công hoặc thất bại
 */
function registerUser($username, $password, $email) {
    $conn = getDbConnection();

    // Mã hoá mật khẩu (dùng bcrypt)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $username, $hashedPassword, $email);
    $result = $stmt->execute();

    $stmt->close();
    $conn->close();
    return $result;
}

/**
 * Đăng nhập người dùng
 * @param string $username Tên đăng nhập
 * @param string $password Mật khẩu
 * @return array|null Thông tin user nếu thành công, null nếu thất bại
 */
function loginUser($username, $password) {
    $conn = getDbConnection();

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    // Kiểm tra mật khẩu
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return null;
}

/**
 * Lấy thông tin user theo ID
 * @param int $userId
 * @return array|null
 */
function getUserById($userId) {
    $conn = getDbConnection();

    $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();
    return $user ?: null;
}

/**
 * Kiểm tra user có phải admin không
 * @param int $userId
 * @return bool
 */
function isAdmin($userId) {
    $conn = getDbConnection();

    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();

    $stmt->close();
    $conn->close();
    return ($role === 'admin');
}
?>
