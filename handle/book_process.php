<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// Lấy dữ liệu từ form
$showtimeId = isset($_POST['show_id']) ? (int) $_POST['show_id'] : 0;
$seatsInput = isset($_POST['seats']) && is_array($_POST['seats']) ? $_POST['seats'] : [];
$promoCode = isset($_POST['promo_code']) ? trim($_POST['promo_code']) : '';

// Kiểm tra dữ liệu bắt buộc
if ($showtimeId <= 0 || empty($seatsInput)) {
    $_SESSION['error'] = "Vui lòng chọn suất chiếu và ghế trước khi đặt vé.";
    header("Location: ../views/cinema/book.php?show_id=" . urlencode($showtimeId));
    exit;
}

// Chuẩn hóa seat_id thành số nguyên và loại trùng
$seatIds = array_values(array_unique(array_map('intval', $seatsInput)));

$conn = getDbConnection();
$conn->set_charset('utf8mb4');
$conn->begin_transaction();

try {
    // 1) Kiểm tra suất chiếu
    $stmt = $conn->prepare("SELECT id, price, room_id, start_time FROM showtimes WHERE id = ?");
    $stmt->bind_param("i", $showtimeId);
    $stmt->execute();
    $showRes = $stmt->get_result();
    $show = $showRes->fetch_assoc();
    $stmt->close();

    if (!$show) {
        throw new Exception("Suất chiếu không tồn tại.");
    }

    // 2) Kiểm tra ghế hợp lệ và thuộc phòng
    $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
    $types = str_repeat('i', count($seatIds));

    $sqlSeats = "SELECT id, seat_type, room_id FROM seats WHERE id IN ($placeholders) FOR UPDATE";
    $stmt = $conn->prepare($sqlSeats);
    $stmt->bind_param($types, ...$seatIds);
    $stmt->execute();
    $seatRes = $stmt->get_result();

    $seats = [];
    while ($row = $seatRes->fetch_assoc()) {
        $seats[$row['id']] = $row;
    }
    $stmt->close();

    if (count($seats) !== count($seatIds)) {
        throw new Exception("Có ghế không hợp lệ.");
    }

    foreach ($seats as $seat) {
        if ((int) $seat['room_id'] !== (int) $show['room_id']) {
            throw new Exception("Ghế không thuộc phòng của suất chiếu.");
        }
    }

    // 3) Kiểm tra ghế đã được đặt chưa
    $sqlConflict = "SELECT bi.seat_id
                    FROM booking_items bi
                    JOIN bookings b ON b.id = bi.booking_id
                    WHERE b.showtime_id = ?
                      AND b.status IN ('PENDING','CONFIRMED')
                      AND bi.seat_id IN ($placeholders)
                    LIMIT 1";

    $stmt = $conn->prepare($sqlConflict);
    $typesConflict = "i" . $types;
    $stmt->bind_param($typesConflict, $showtimeId, ...$seatIds);
    $stmt->execute();
    $conflictRes = $stmt->get_result();
    $conflict = $conflictRes->fetch_assoc();
    $stmt->close();

    if ($conflict) {
        throw new Exception("Ghế đã được đặt. Vui lòng chọn ghế khác.");
    }

    // 4) Tính giá
    $basePrice = (float) $show['price'];
    $multipliers = [
        'STANDARD' => 1.00,
        'VIP' => 1.20,
        'COUPLE' => 1.80
    ];

    $items = [];
    $subtotal = 0.0;
    foreach ($seatIds as $sid) {
        $seat = $seats[$sid];
        $mul = $multipliers[$seat['seat_type']] ?? 1.0;
        $price = round($basePrice * $mul, 2);
        $items[] = ['seat_id' => $sid, 'price' => $price];
        $subtotal += $price;
    }

    // 5) Áp mã khuyến mãi
    $discountPct = 0.0;
    if ($promoCode !== '') {
        $stmt = $conn->prepare("SELECT discount_percentage, valid_from, valid_to FROM promotions WHERE code = ?");
        $stmt->bind_param("s", $promoCode);
        $stmt->execute();
        $promoRes = $stmt->get_result();
        $promo = $promoRes->fetch_assoc();
        $stmt->close();

        if ($promo) {
            $today = new DateTime('today');
            $from = new DateTime($promo['valid_from']);
            $to = new DateTime($promo['valid_to']);
            if ($today >= $from && $today <= $to) {
                $discountPct = (float) $promo['discount_percentage'];
            }
        }
    }

    $discountAmount = round($subtotal * ($discountPct / 100), 2);
    $totalAmount = max(0, round($subtotal - $discountAmount, 2));

    // 6) Tạo booking
    $holdMinutes = 10;
    $holdExpires = (new DateTime())->add(new DateInterval("PT{$holdMinutes}M"))->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, showtime_id, status, total_amount, hold_expires_at) VALUES (?, ?, 'PENDING', ?, ?)");
    $stmt->bind_param("iids", $userId, $showtimeId, $totalAmount, $holdExpires);
    if (!$stmt->execute()) {
        throw new Exception("Không thể tạo booking: " . $stmt->error);
    }
    $bookingId = $stmt->insert_id;
    $stmt->close();

    // 7) Chèn booking_items
    $stmt = $conn->prepare("INSERT INTO booking_items (booking_id, seat_id, price) VALUES (?, ?, ?)");
    foreach ($items as $it) {
        $stmt->bind_param("iid", $bookingId, $it['seat_id'], $it['price']);
        if (!$stmt->execute()) {
            throw new Exception("Không thể thêm ghế vào đơn: " . $stmt->error);
        }
    }
    $stmt->close();

    // 8) Tạo payment
    $stmt = $conn->prepare("INSERT INTO payments (booking_id, payment_method, amount, status) VALUES (?, 'CASH', ?, 'PENDING')");
    $stmt->bind_param("id", $bookingId, $totalAmount);
    $stmt->execute();
    $stmt->close();

    // Commit
    $conn->commit();

    // Chuyển hướng tới trang thanh toán
    $_SESSION['success'] = "Đặt vé thành công. Vui lòng thanh toán trong $holdMinutes phút.";
    header("Location: ../views/cinema/payment.php?booking_id=" . urlencode($bookingId));
    exit;



} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../views/cinema/book.php?show_id=" . urlencode($showtimeId));
    exit;
}
