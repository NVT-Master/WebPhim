<?php
session_start();

// Debug (xóa sau khi xong)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Xóa output buffer trước khi set header
ob_start();
header('Content-Type: application/json');
ob_end_clean();

if (!isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền']);
    exit();
}

$conn = getDbConnection();

$response = ['success' => false, 'message' => 'Hành động không hợp lệ'];

$action = $_POST['action'] ?? '';
file_put_contents('debug.log', "Action received: $action\n", FILE_APPEND); // Log action

try {
    if ($action === 'update') {
        $seat_id = intval($_POST['seat_id'] ?? 0);
        $seat_type = strtolower($_POST['seat_type'] ?? 'standard');
        $status = strtolower($_POST['status'] ?? 'available');

        if ($seat_id <= 0) {
            $response['message'] = 'Thiếu ID ghế';
        } else {
            $stmt = $conn->prepare("UPDATE seats SET seat_type = ?, status = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssi", $seat_type, $status, $seat_id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Cập nhật ghế thành công';
                } else {
                    $response['message'] = 'Lỗi khi cập nhật: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Lỗi chuẩn bị câu lệnh: ' . $conn->error;
            }
        }
    } elseif ($action === 'delete') {
        $seat_id = intval($_POST['seat_id'] ?? 0);
        if ($seat_id <= 0) {
            $response['message'] = 'Thiếu ID ghế';
        } else {
            $stmt = $conn->prepare("DELETE FROM seats WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $seat_id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Xóa ghế thành công';
                } else {
                    $response['message'] = 'Lỗi khi xóa: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Lỗi chuẩn bị câu lệnh: ' . $conn->error;
            }
        }
    }

    echo json_encode($response);
} catch (Exception $e) {
    $response['message'] = 'Lỗi không xác định: ' . $e->getMessage();
    echo json_encode($response);
}

$conn->close();
?>