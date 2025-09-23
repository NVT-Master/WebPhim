<?php
header('Content-Type: application/json; charset=UTF-8');

// Kiểm tra và yêu cầu file db_connection.php với đường dẫn điều chỉnh
$db_connection_path = __DIR__ . '/../../functions/db_connection.php';
if (!file_exists($db_connection_path)) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy file db_connection.php']);
    exit;
}
require_once $db_connection_path;

$conn = getDbConnection();
$response = ['success' => false, 'message' => ''];

// Debug dữ liệu nhận được
error_log("Received POST: " . print_r($_POST, true)); // Ghi log vào error log

try {
    $action = $_POST['action'] ?? '';
    $seat_id = $_POST['seat_id'] ?? null;
    $seat_type = $_POST['seat_type'] ?? 'standard';
    $status = $_POST['status'] ?? 'available';

    // Debug giá trị
    error_log("Action: $action, Seat ID: $seat_id, Seat Type: $seat_type, Status: $status");

    // FIX: Validate action (KHÔNG yêu cầu seat_id cho add / clear_booking)
    if (!in_array($action, ['add', 'update', 'delete', 'clear_booking'])) {
        $response['message'] = 'Action không hợp lệ: ' . $action;
        echo json_encode($response);
        exit;
    }

    /* =========================
       CLEAR BOOKING (hủy trạng thái đã đặt)
       ========================= */
    if ($action === "clear_booking") {
        // FIX: Ràng buộc seat_id
        $seat_id = intval($_POST['seat_id'] ?? 0);
        if (!$seat_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu seat_id']);
            exit;
        }

        // Xóa booking_items của ghế này
        $stmt = $conn->prepare("DELETE bi FROM booking_items bi
                                JOIN bookings b ON bi.booking_id = b.id
                                WHERE bi.seat_id = ? AND b.status IN ('PENDING','CONFIRMED')");
        $stmt->bind_param("i", $seat_id);
        $stmt->execute();
        $stmt->close();

        // Cập nhật trạng thái ghế thành AVAILABLE
        $stmt2 = $conn->prepare("UPDATE seats SET status = 'AVAILABLE' WHERE id = ?");
        $stmt2->bind_param("i", $seat_id);
        $stmt2->execute();
        $stmt2->close();

        echo json_encode(["success" => true]);
        exit;
    }

    /* =========================
       ADD (thêm ghế)
       ========================= */
    if ($action === "add") {
        $room_id = intval($_POST['room_id'] ?? 0);
        $row_label = strtoupper(trim($_POST['row_label'] ?? ''));
        $seat_number = intval($_POST['seat_number'] ?? 0);
        $seat_type = strtoupper(trim($_POST['seat_type'] ?? 'STANDARD'));

        // FIX: Kiểm tra thiếu dữ liệu
        if (!$room_id || $row_label === '' || !$seat_number) {
            echo json_encode(["success" => false, "message" => "Thiếu room_id / row_label / seat_number"]);
            exit;
        }

        // FIX: Kiểm tra trùng ghế trong cùng phòng
        $chk = $conn->prepare("SELECT 1 FROM seats WHERE room_id=? AND row_label=? AND seat_number=?");
        $chk->bind_param("isi", $room_id, $row_label, $seat_number);
        $chk->execute();
        if ($chk->get_result()->fetch_row()) {
            echo json_encode(["success" => false, "message" => "Ghế đã tồn tại trong phòng này"]);
            $chk->close();
            exit;
        }
        $chk->close();

        // Thêm ghế
        $stmt = $conn->prepare("INSERT INTO seats (room_id, row_label, seat_number, seat_type, status)
                                VALUES (?, ?, ?, ?, 'AVAILABLE')");
        $stmt->bind_param("isis", $room_id, $row_label, $seat_number, $seat_type);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    /* =========================
       UPDATE (chỉnh sửa ghế)
       ========================= */
    if ($action === 'update') {
        // FIX: Ràng buộc seat_id + chuẩn hóa dữ liệu
        $seat_id = intval($_POST['seat_id'] ?? 0);
        if (!$seat_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu seat_id']);
            exit;
        }
        $seat_type = strtoupper(trim($_POST['seat_type'] ?? 'STANDARD'));
        $status = strtoupper(trim($_POST['status'] ?? 'AVAILABLE'));

        $stmt = $conn->prepare("UPDATE seats SET seat_type = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $seat_type, $status, $seat_id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Cập nhật ghế thành công';
        } else {
            $response['message'] = 'Cập nhật ghế thất bại: ' . $conn->error;
        }
        $stmt->close();
    }
    /* =========================
       DELETE (xóa ghế)
       ========================= */ elseif ($action === 'delete') {
        // FIX: Ràng buộc seat_id
        $seat_id = intval($_POST['seat_id'] ?? 0);
        if (!$seat_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu seat_id']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM seats WHERE id = ?");
        $stmt->bind_param("i", $seat_id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Xóa ghế thành công';
        } else {
            $response['message'] = 'Xóa ghế thất bại: ' . $conn->error;
        }
        $stmt->close();
    }

} catch (Exception $e) {
    $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
} finally {
    // Lưu ý: với add & clear_booking mình đã echo và exit ở trên.
    // Với update/delete sẽ đi qua đây để trả JSON.
    echo json_encode($response);
    $conn->close();
}
