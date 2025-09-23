<?php
session_start();
require_once __DIR__ . '/../../functions/db_connection.php';

// Bảo vệ trang: chỉ ADMIN
if (!isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách phòng
$rooms = $conn->query("SELECT * FROM rooms");

// Lấy tham số GET
$selected_room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : null;
$selected_showtime_id = isset($_GET['showtime_id']) ? intval($_GET['showtime_id']) : null;

$seats = [];
$room_name = "";
$showtimes = [];
$bookedSeatIds = [];

if ($selected_room_id) {
    // Ghế trong phòng
    $stmt = $conn->prepare("SELECT * FROM seats WHERE room_id = ? ORDER BY row_label, seat_number");
    $stmt->bind_param("i", $selected_room_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (!array_key_exists('status', $row) || $row['status'] === null) {
            $row['status'] = 'AVAILABLE';
        }
        if (!array_key_exists('seat_type', $row) || $row['seat_type'] === null) {
            $row['seat_type'] = 'STANDARD';
        }
        $seats[] = $row;
    }
    $stmt->close();

    // Tên phòng
    $stmt2 = $conn->prepare("SELECT name FROM rooms WHERE id = ?");
    $stmt2->bind_param("i", $selected_room_id);
    $stmt2->execute();
    $room_row = $stmt2->get_result()->fetch_assoc();
    $room_name = $room_row['name'] ?? "N/A";
    $stmt2->close();

    // Danh sách showtimes
    $stmt3 = $conn->prepare("SELECT id, start_time FROM showtimes WHERE room_id = ? ORDER BY start_time ASC");
    $stmt3->bind_param("i", $selected_room_id);
    $stmt3->execute();
    $res3 = $stmt3->get_result();
    while ($r = $res3->fetch_assoc()) {
        $showtimes[] = $r;
    }
    $stmt3->close();

    // Ghế đã đặt
    if ($selected_showtime_id) {
        $sqlBooked = "SELECT DISTINCT bi.seat_id
                      FROM booking_items bi
                      JOIN bookings b ON bi.booking_id = b.id
                      WHERE b.showtime_id = ? AND b.status IN ('PENDING','CONFIRMED')";
        $stmt4 = $conn->prepare($sqlBooked);
        $stmt4->bind_param("i", $selected_showtime_id);
        $stmt4->execute();
        $res4 = $stmt4->get_result();
        while ($r = $res4->fetch_assoc()) {
            $bookedSeatIds[] = (int) $r['seat_id'];
        }
        echo "<!-- Debug: Ghế đã đặt cho showtime $selected_showtime_id: " . implode(", ", $bookedSeatIds) . " -->\n";
        $stmt4->close();
    }

    // Debug dữ liệu seats
    echo "<!-- Debug: Số ghế trong phòng $selected_room_id: " . count($seats) . " -->\n";
    foreach ($seats as $s) {
        echo "<!-- Debug: Seat ID: " . $s['id'] . ", Status: " . $s['status'] . ", Type: " . $s['seat_type'] . " -->\n";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Phòng - MovieBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
            border-bottom: 2px solid #ffbb00;
        }

        .navbar .navbar-brand {
            color: #ffbb00 !important;
            font-weight: 600;
        }

        .admin-heading {
            color: #ffbb00;
            margin: 30px 0;
            text-align: center;
        }

        .seat-map {
            background: rgba(45, 52, 54, 0.9);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
        }

        .seat-row {
            display: flex;
            justify-content: center;
            margin: 8px 0;
            gap: 8px;
        }

        .seat {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .seat.AVAILABLE {
            background: #28a745;
            color: #fff;
            border: 1px solid #7a7a7aff;
        }

        .seat.AVAILABLE:hover {
            background: #ffbb00;
            color: #000;
            transform: scale(1.1);
        }

        /* Thêm quy tắc ưu tiên BOOKED trên loại ghế */
        .seat.BOOKED {
            background: #e74c3c !important;
            /* Màu đỏ ưu tiên */
            color: #fff !important;
            cursor: not-allowed !important;
        }

        .seat.VIP.BOOKED {
            background: #e74c3c !important;
            /* VIP booked: đỏ thay vì tím */
            color: #fff !important;
        }

        .seat.COUPLE.BOOKED {
            background: #e74c3c !important;
            /* Couple booked: đỏ thay vì xanh */
            color: #fff !important;
            width: 38px;
            /* Giữ kích thước chuẩn khi booked */
            border-radius: 6px;
        }

        /* Light mode tương tự */
        body.light-mode .seat.VIP.BOOKED,
        body.light-mode .seat.COUPLE.BOOKED {
            background: #ff6b6b !important;
            /* Đỏ nhạt cho light mode */
            color: #fff !important;
        }

        .seat.SELECTED {
            background: #ffbb00;
            color: #000;
            border: 2px solid #fff;
        }

        .seat.VIP {
            border-radius: 6px;
            background: #8e44ad;
            color: #fff;
        }

        .seat.VIP:hover {
            background: #ffbb00;
            color: #fff;
            transform: scale(1.1);
        }

        .seat.COUPLE {
            width: 80px;
            border-radius: 8px;
            background: #2980b9;
            color: #fff;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .room-actions {
            text-align: left;
            margin-top: 20px;
        }

        .room-actions a {
            background: #ffbb00;
            color: #000;
            padding: 10px 18px;
            border-radius: 25px;
            margin: 0 8px;
            font-weight: 600;
            text-decoration: none;
        }

        .room-actions a:hover {
            background: #fff;
            color: #ffbb00;
            transform: scale(1.05);
        }

        /* Mặc định: dark mode */
        body.dark-mode {
            background: linear-gradient(135deg, #0f0f0f, #2d3436);
            color: #fff;
        }

        body.dark-mode .seat-map {
            background: rgba(45, 52, 54, 0.9);
            color: #fff;
        }

        body.dark-mode .seat.AVAILABLE {
            background: #28a745;
            color: #fff;
        }

        body.dark-mode .seat.SELECTED {
            background: #ffbb00;
            color: #fff;
            border: 2px solid #fff;
        }

        /* Light mode */
        body.light-mode {
            background: #f9f9f9;
            color: #000;
        }

        body.light-mode .seat-map {
            background: #fff;
            border: 1px solid #ccc;
            color: #000;
        }

        body.light-mode .seat.AVAILABLE {
            background: #ddd;
            color: #000;
        }

        body.light-mode .seat.BOOKED {
            background: #ff6b6b;
            color: #fff;
        }

        body.light-mode .seat.SELECTED {
            background: #ffbb00;
            color: #000;
            border: 2px solid #000;
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
                    <a class="nav-link" href="../../handle/logout_process.php"><i class="fas fa-sign-out-alt"></i> Đăng
                        xuất</a>
                    <a href="#" class="nav-link" id="themeToggle"><i class="fas fa-moon"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container room-container">
        <h2 class="admin-heading">🎬 Quản lý Phòng & Ghế</h2>

        <div class="room-actions">
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addSeatModal">
                + Thêm ghế
            </button>
        </div><br><br>

        <!-- Form chọn phòng & suất chiếu -->
        <form method="GET" class="room-select d-flex gap-2">
            <select name="room_id" id="room_id" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">-- Chọn phòng --</option>
                <?php
                // Join rooms với theaters để lấy địa chỉ
                $stmt = $conn->prepare("SELECT r.id, r.name AS room_name, t.address 
                               FROM rooms r 
                               LEFT JOIN theaters t ON r.theater_id = t.id");
                $stmt->execute();
                $rooms = $stmt->get_result();
                while ($r = $rooms->fetch_assoc()):
                    ?>
                    <option value="<?= (int) $r['id'] ?>" <?= $selected_room_id == $r['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars("Phòng " . $r['room_name'] . ' - ' . ($r['address'] ?? 'Chưa có địa chỉ')) ?>
                    </option>
                <?php endwhile;
                $stmt->close(); ?>
            </select>

            <?php if (!empty($showtimes)): ?>
                <select name="showtime_id" id="showtime_id" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">-- Chọn suất chiếu --</option>
                    <?php foreach ($showtimes as $st): ?>
                        <option value="<?= (int) $st['id'] ?>" <?= $selected_showtime_id == $st['id'] ? 'selected' : '' ?>>
                            <?= date('H:i d/m/Y', strtotime($st['start_time'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </form>

        <?php if ($selected_room_id): ?>
            <div class="seat-map mt-3">
                <h5 class="mb-3">Sơ đồ ghế - <?= htmlspecialchars($room_name) ?></h5>

                <?php
                $rows = [];
                foreach ($seats as $s) {
                    $rows[$s['row_label']][] = $s;
                }
                ksort($rows);
                foreach ($rows as $row_label => $row_seats): ?>
                    <div class="seat-row">
                        <div class="seat-label-row"><?= htmlspecialchars($row_label) ?></div>
                        <?php
                        usort($row_seats, function ($a, $b) {
                            return intval($a['seat_number']) <=> intval($b['seat_number']);
                        });
                        foreach ($row_seats as $seat):
                            $seatStatus = 'AVAILABLE';
                            if ($selected_showtime_id && in_array((int) $seat['id'], $bookedSeatIds, true)) {
                                $seatStatus = 'BOOKED';
                            } else {
                                $sdb = strtoupper(trim($seat['status'] ?? 'AVAILABLE'));
                                $seatStatus = $sdb === '' ? 'AVAILABLE' : $sdb;
                            }
                            $seatType = strtoupper(trim($seat['seat_type'] ?? 'STANDARD'));
                            $classAttr = htmlspecialchars($seatType . ' ' . $seatStatus); // Thứ tự: type trước, status sau để CSS ưu tiên status
                            ?>
                            <div class="seat <?= $classAttr ?>" data-id="<?= (int) $seat['id'] ?>"
                                data-seat-number="<?= htmlspecialchars($seat['seat_number']) ?>"
                                data-seat-type="<?= htmlspecialchars($seat['seat_type']) ?>"
                                data-status="<?= htmlspecialchars($seat['status']) ?>">
                                <?= htmlspecialchars($seat['seat_number']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="legend">
                <div><span style="display:inline-block;width:18px;height:18px;background:#28a745"></span> Ghế trống</div>
                <div><span style="display:inline-block;width:18px;height:18px;background:#e74c3c"></span> Đã đặt</div>
                <div><span style="display:inline-block;width:18px;height:18px;background:#ffbb00"></span> Đang chọn</div>
                <div><span style="display:inline-block;width:18px;height:18px;background:#8e44ad"></span> VIP</div>
                <div><span style="display:inline-block;width:18px;height:18px;background:#2980b9"></span> Couple</div>
            </div>
        <?php endif; ?>
    </div>
    <!-- form thêm ghế -->
    <div class="modal fade" id="addSeatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm ghế mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addSeatForm">
                        <input type="hidden" name="room_id" value="<?= $selected_room_id ?>">
                        <div class="mb-3">
                            <label class="form-label">Hàng ghế</label>
                            <input type="text" name="row_label" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số ghế</label>
                            <input type="number" name="seat_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Loại ghế</label>
                            <select name="seat_type" class="form-select">
                                <option value="STANDARD">Thường</option>
                                <option value="VIP">VIP</option>
                                <option value="COUPLE">Couple</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Thêm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal sửa/xóa ghế (tạm thời vô hiệu hóa AJAX) -->
    <div class="modal fade" id="seatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa ghế</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <form id="seatForm" method="POST" action="manage_rooms_update.php"> <!-- Thay bằng form POST -->
                        <input type="hidden" name="seat_id" id="seat_id">

                        <div class="mb-3">
                            <label for="seat_type" class="form-label">Loại ghế</label>
                            <select name="seat_type" id="seat_type" class="form-select">
                                <option value="standard">Thường</option>
                                <option value="vip">VIP</option>
                                <option value="couple">Couple</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select name="status" id="status" class="form-select">
                                <option value="available">Trống</option>
                                <option value="booked">Đã đặt</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Lưu</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="deleteSeat" class="btn btn-danger">Xóa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Copyright © <?= date("Y") ?> MovieBooking. All rights reserved.
                | <a href="../views/contact.php" style="color: #ffbb00ff;">Liên hệ</a></p>
        </div>
    </footer>

    <script>
        let currentSeat = null;

        document.querySelectorAll(".seat").forEach(seat => {
            seat.addEventListener("click", function () {
                currentSeat = this;

                document.getElementById("seat_id").value = this.dataset.id;
                document.getElementById("seat_type").value = this.dataset.seatType.toLowerCase();
                document.getElementById("status").value = this.dataset.status.toLowerCase();

                // Cập nhật trạng thái nút "Xóa" dựa trên BOOKED
                const deleteBtn = document.getElementById("deleteSeat");
                if (this.dataset.status.toUpperCase() === 'BOOKED') {
                    deleteBtn.textContent = "Xóa (Ghế đã đặt)";
                } else {
                    deleteBtn.textContent = "Xóa";
                }

                const modal = new bootstrap.Modal(document.getElementById("seatModal"));
                modal.show();
            });
        });

        // Submit form sửa ghế
        document.getElementById("seatForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append("action", "update");

            fetch("manage_rooms_update.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Cập nhật thành công!");
                        if (currentSeat) {
                            currentSeat.dataset.seatType = formData.get("seat_type");
                            currentSeat.dataset.status = formData.get("status");
                            const newType = formData.get("seat_type").toUpperCase();
                            const newStatus = formData.get("status").toUpperCase();
                            currentSeat.className = `seat ${newType} ${newStatus}`;
                            if (newStatus === 'BOOKED') {
                                currentSeat.classList.add('BOOKED');
                            }
                        }
                        bootstrap.Modal.getInstance(document.getElementById("seatModal")).hide();
                    } else {
                        alert("Lỗi: " + (data.message ?? "không xác định"));
                    }
                })
                .catch(err => {
                    console.error("AJAX error:", err);
                    alert("Lỗi AJAX: " + err.message);
                });
        });

        // Xóa ghế
        document.getElementById("deleteSeat").addEventListener("click", function () {
            if (!currentSeat) return;

            if (currentSeat.dataset.status.toUpperCase() !== 'BOOKED') {
                alert("Chỉ có thể xóa trạng thái ghế đã đặt (màu đỏ)!");
                return;
            }

            if (!confirm("Bạn có chắc muốn hủy trạng thái đã đặt của ghế này không?")) return;

            const seatId = document.getElementById("seat_id").value;
            const formData = new FormData();
            formData.append("action", "clear_booking"); // đổi action
            formData.append("seat_id", seatId);

            fetch("manage_rooms_update.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Đã hủy trạng thái ghế!");
                        if (currentSeat) {
                            currentSeat.dataset.status = "available";
                            currentSeat.className = `seat ${currentSeat.dataset.seatType.toUpperCase()} AVAILABLE`;
                        }
                        bootstrap.Modal.getInstance(document.getElementById("seatModal")).hide();
                    } else {
                        alert("Lỗi: " + (data.message ?? "không xác định"));
                    }
                })
                .catch(err => {
                    console.error("AJAX error:", err);
                    alert("Lỗi AJAX: " + err.message);
                });
        });
        // thêm ghế (bản fix)
        document.getElementById("addSeatForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const roomId = formData.get("room_id");
            if (!roomId) { alert("Vui lòng chọn phòng trước khi thêm ghế."); return; }
            formData.append("action", "add");
            fetch("manage_rooms_update.php", { method: "POST", body: formData })
                .then(async (res) => { const text = await res.text(); try { return JSON.parse(text); } catch { throw new Error("Phản hồi không phải JSON từ server:\n" + text.slice(0, 800)); } })
                .then((data) => { if (data.success) { alert("Đã thêm ghế mới!"); location.reload(); } else { alert("Lỗi: " + (data.message ?? "không xác định")); } })
                .catch((err) => { console.error("Add seat error:", err); alert("Lỗi AJAX: " + err.message); });
        });

        // Khi mở modal Thêm ghế, đảm bảo hidden room_id luôn đúng với dropdown hiện tại
        const roomSelect = document.getElementById('room_id');
        const addSeatModalEl = document.getElementById('addSeatModal');
        addSeatModalEl?.addEventListener('show.bs.modal', () => {
            const hiddenRoomId = document.querySelector('#addSeatForm input[name="room_id"]');
            if (hiddenRoomId) hiddenRoomId.value = roomSelect?.value || '';
        });
        // Disable nút + Thêm ghế khi chưa chọn phòng
        const addSeatBtn = document.querySelector('[data-bs-target="#addSeatModal"]');
        function toggleAddSeatBtn() {
            const enabled = !!roomSelect?.value;
            if (addSeatBtn) {
                addSeatBtn.disabled = !enabled;
                addSeatBtn.title = enabled ? "" : "Hãy chọn phòng trước";
            }
        }
        toggleAddSeatBtn();
        roomSelect?.addEventListener('change', toggleAddSeatBtn);

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>