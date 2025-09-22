// Chọn ghế
document.addEventListener("DOMContentLoaded", () => {
  const seats = document.querySelectorAll(".seat");
  const selectedSeatsElement = document.getElementById("selected-seats");
  const seatsInput = document.getElementById("seats-input");
  let selectedSeats = [];

  seats.forEach((seat) => {
    seat.addEventListener("click", () => {
      if (seat.classList.contains("btn-success")) {
        seat.classList.remove("btn-success");
        selectedSeats = selectedSeats.filter((s) => s !== seat.dataset.seat);
      } else if (selectedSeats.length < 5) {
        // Giới hạn 5 ghế
        seat.classList.add("btn-success");
        selectedSeats.push(seat.dataset.seat);
      }
      selectedSeatsElement.textContent = selectedSeats.join(", ");
      seatsInput.value = selectedSeats.join(",");
    });
  });
});

// AJAX Search (tích hợp với index.php)
document
  .querySelector('form[role="search"]')
  ?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const searchTerm = e.target.querySelector('input[name="search"]').value;
    const response = await fetch(
      `../index.php?search=${encodeURIComponent(searchTerm)}`
    );
    const html = await response.text();
    document.body.innerHTML = html; // Cập nhật toàn bộ trang (hoặc dùng DOM để cập nhật cụ thể)
  });
