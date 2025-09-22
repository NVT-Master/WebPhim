<?php
// C:\xampp\htdocs\WebPhim\functions\booking_functions.php
require_once __DIR__ . '/db_connection.php'; // Sử dụng db_connection.php hiện có

/**
 * Lấy danh sách đặt vé của người dùng
 * @param int $userId ID của người dùng
 * @return array Danh sách các đặt vé đã xác nhận
 */
function getUserBookings($userId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND status = 'confirmed' ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $bookings;
}

/**
 * Lấy thông tin chi tiết của một phim dựa trên ID
 * @param int $movieId ID của phim
 * @return array Thông tin phim, hoặc null nếu không tìm thấy
 */
function getMovieById($movieId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $movie;
}

/**
 * Lấy thông tin lịch chiếu dựa trên ID
 * @param int $showtimeId ID của lịch chiếu
 * @return array Thông tin lịch chiếu, hoặc null nếu không tìm thấy
 */
function getShowtimeById($showtimeId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM showtimes WHERE id = ?");
    $stmt->bind_param("i", $showtimeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $showtime = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $showtime;
}

/**
 * Lấy danh sách ghế đã đặt cho một vé
 * @param int $bookingId ID của đặt vé
 * @return array Danh sách ghế
 */
function getBookingSeats($bookingId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT s.row_label, s.seat_number, s.seat_type, bi.price 
                          FROM booking_items bi 
                          JOIN seats s ON bi.seat_id = s.id 
                          WHERE bi.booking_id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $seats = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    return $seats;
}

/**
 * Tạo một đặt vé mới
 * @param int $userId ID người dùng
 * @param int $showtimeId ID lịch chiếu
 * @param array $seatIds Mảng ID ghế
 * @return int|bool ID của đặt vé nếu thành công, false nếu thất bại
 */
function createBooking($userId, $showtimeId, $seatIds) {
    $conn = getDbConnection();
    $conn->begin_transaction();
    try {
        $showtime = getShowtimeById($showtimeId);
        $totalAmount = count($seatIds) * $showtime['price'];
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, showtime_id, status, total_amount, hold_expires_at, created_at) VALUES (?, ?, 'PENDING', ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW())");
        $stmt->bind_param("iid", $userId, $showtimeId, $totalAmount);
        $stmt->execute();
        $bookingId = $conn->insert_id;

        foreach ($seatIds as $seatId) {
            $stmt = $conn->prepare("INSERT INTO booking_items (booking_id, seat_id, price) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $bookingId, $seatId, $showtime['price']);
            $stmt->execute();
        }
        $conn->commit();
        $stmt->close();
        $conn->close();
        return $bookingId;
    } catch (Exception $e) {
        $conn->rollback();
        $stmt->close();
        $conn->close();
        return false;
    }
}

/**
 * Hủy một đặt vé
 * @param int $bookingId ID của đặt vé
 * @return bool Thành công hoặc thất bại
 */
function cancelBooking($bookingId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE bookings SET status = 'CANCELLED' WHERE id = ? AND status = 'PENDING'");
    $stmt->bind_param("i", $bookingId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}
?>