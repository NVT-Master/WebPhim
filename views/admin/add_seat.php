<?php
require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

$room_id = $_GET['room_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $row_label = $_POST['row_label'];
    $seat_number = $_POST['seat_number'];
    $seat_type = $_POST['seat_type'];

    $stmt = $conn->prepare("INSERT INTO seats (room_id, row_label, seat_number, seat_type) VALUES (?,?,?,?)");
    $stmt->bind_param("isis", $room_id, $row_label, $seat_number, $seat_type);
    $stmt->execute();
    header("Location: manage_seats.php?room_id=$room_id");
    exit();
}
?>

<form method="POST">
    <label>Hàng (A,B,C...)</label>
    <input type="text" name="row_label" required class="form-control">

    <label>Số ghế</label>
    <input type="number" name="seat_number" required class="form-control">

    <label>Loại ghế</label>
    <select name="seat_type" class="form-control">
        <option value="STANDARD">Standard</option>
        <option value="VIP">VIP</option>
        <option value="COUPLE">Couple</option>
    </select>

    <button type="submit" class="btn btn-success mt-3">Thêm ghế</button>
</form>