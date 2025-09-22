<?php
include 'config.php';
session_start();

// Oturum kontrolü
if (!isset($_SESSION["user_id"])) {
    die("You must log in.");
}

$film_id = isset($_GET["film_id"]) && is_numeric($_GET["film_id"]) ? intval($_GET["film_id"]) : 0;
$session_time = $_GET['session_time'] ?? '10:00:00';

if ($film_id === 0) {
    die("Invalid movie ID");
}

$film_stmt = $conn->prepare("SELECT * FROM films WHERE id = ?");
$film_stmt->bind_param("i", $film_id);
$film_stmt->execute();
$film_result = $film_stmt->get_result();
$film = $film_result->fetch_assoc();

if (!$film) {
    die("Movie not found.");
}

$stmt = $conn->prepare("SELECT seat_number FROM tickets WHERE film_id = ? AND session_time = ?");
$stmt->bind_param("is", $film_id, $session_time);
$stmt->execute();
$result = $stmt->get_result();

$booked_seats = [];
while ($row = $result->fetch_assoc()) {
    $booked_seats[] = $row['seat_number'];
}

$total_seats = 40;
$is_sold_out = count($booked_seats) >= $total_seats;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($film['title']) ?> - Seat Selection</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background-color: #121212;
      color: white;
      font-family: Arial, sans-serif;
    }
    .seating-chart {
      display: grid;
      grid-template-columns: repeat(8, 40px);
      gap: 10px;
      margin: 40px auto;
      width: max-content;
    }
    .seat {
      width: 40px;
      height: 40px;
      border-radius: 5px;
      background-color: #ccc;
      cursor: pointer;
      text-align: center;
      line-height: 40px;
      color: #000;
      font-weight: bold;
    }
    .seat.booked {
      background-color: #e74c3c;
      cursor: not-allowed;
      color: white;
    }
    .seat.selected {
      background-color: #2ecc71;
      color: white;
    }
    form {
      width: 400px;
      margin: 0 auto;
      padding: 20px;
      background-color: #2c2c2c;
      border-radius: 10px;
    }
    select, input, button {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      border: none;
    }
    #session-time-selector {
    padding: 8px 40px;
    border: 2px solid #00bcd4;
    border-radius: 6px;
    background-color: #2c2c2c;
    color: #ffffff;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
    width: 10%;
    }
    #session-time-selector:hover {
    background-color: #000000;
    color: #fff;
    }

    button {
      background-color: #3498db;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: #2980b9;
    }
    #price-display, #sold-out-message {
      text-align: center;
      font-size: 18px;
    }
    #sold-out-message {
      color: #e74c3c;
      margin-top: 20px;
    }
    .navbar {
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1000;
}

.back-button {
  display: inline-block;
  padding: 10px 16px;
  background-color: #3498db;
  color: white;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  transition: background-color .25s ease-in-out;
}

.back-button:hover {
  background-color:darkred;
  text-decoration: none;
}
  </style>
</head>
<body>

<h2 style="text-align:center;"><?= htmlspecialchars($film['title']) ?></h2>

<div style="text-align:center; margin-bottom: 20px;">
    <img src="<?= htmlspecialchars($film['poster']) ?>" alt="<?= htmlspecialchars($film['title']) ?>" style="max-width: 300px; border-radius: 10px;">
    <p style="font-size: 18px;"><?= htmlspecialchars($film['description']) ?></p>
</div>

<div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 20px;">
  <div>
    <label for="session-time-selector" style="color:white;">Session Time:</label><br>
    <select id="session-time-selector" style="width: 200px; padding: 10px; font-size: 16px;">
      <option value="10:00:00" <?= $session_time == '10:00:00' ? 'selected' : '' ?>>10:00</option>
      <option value="13:00:00" <?= $session_time == '13:00:00' ? 'selected' : '' ?>>13:00</option>
      <option value="16:00:00" <?= $session_time == '16:00:00' ? 'selected' : '' ?>>16:00</option>
      <option value="20:00:00" <?= $session_time == '20:00:00' ? 'selected' : '' ?>>20:00</option>
    </select>
  </div>

  <div>
    <label for="ticket-type-selector" style="color:white;">Ticket Type:</label><br>
    <select id="ticket-type-selector" style="width: 200px; padding: 10px; font-size: 16px;">
      <option value="Adult">Adult</option>
      <option value="Student">Student</option>
    </select>
  </div>
</div>



<div class="seating-chart" id="seating-chart">
  <?php
  for ($i = 1; $i <= $total_seats; $i++) {
      $class = in_array($i, $booked_seats) ? "seat booked" : "seat";
      echo "<div class='$class' data-seat='$i'>$i</div>";
  }
  ?>
</div>

<form id="ticket-form" style="<?= $is_sold_out ? 'display:none;' : '' ?>">
  <p id="price-display"></p>

  <input type="hidden" name="seats" id="selected-seats" required>
  <input type="hidden" name="film_id" value="<?= $film_id ?>">
  <input type="hidden" name="session_time" id="session-time-hidden" value="<?= $session_time ?>">

  <label>Payment Method:</label>
  <select name="payment_method" id="method" onchange="togglePaymentType()">
    <option value="Credit Card">Credit Card</option>
    <option value="Debit Card">Debit Card</option>
  </select>

  <label>Payment Type:</label>
  <select name="payment_type" id="paytype">
    <option value="One-time Payment">One-time Payment</option>
    <option value="Installments">Installments</option>
  </select>

  <button type="submit">Buy</button>
</form>
<div class="navbar">
  <a href="index.php" class="back-button">← Back to Home</a>
</div>

<?php if ($is_sold_out): ?>
  <div id="sold-out-message">All tickets have been sold out</div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  let selectedSeats = [];
  const seats = document.querySelectorAll('.seat');
  const seatInput = document.getElementById("selected-seats");
  const ticketTypeSelector = document.getElementById('ticket-type-selector');
  const priceDisplay = document.getElementById("price-display");

  seats.forEach(seat => {
    seat.addEventListener('click', () => {
      const seatId = seat.dataset.seat;
      const selectedType = ticketTypeSelector.value;

      // Eğer koltuk alınmışsa (booked sınıfı varsa) işlem yapma
      if (seat.classList.contains('booked')) {
        return; // Koltuğa tıklanamaz
      }

      // Seçilen koltuğa tıklanırsa ve zaten seçildiyse, çıkar
      if (selectedSeats.some(s => s.id === seatId)) {
        selectedSeats = selectedSeats.filter(s => s.id !== seatId);
        seat.classList.remove('selected');
      } else {
        selectedSeats.push({ id: seatId, type: selectedType });
        seat.classList.add('selected');
      }

      seatInput.value = JSON.stringify(selectedSeats);

      // Toplam fiyat hesaplama
      let total = 0;
      selectedSeats.forEach(s => {
        total += s.type === "Student" ? 100 : 200;
      });

      priceDisplay.innerText = "Total Price: " + total + " TL";
    });
  });

  // Ödeme metodu değiştiğinde ödeme türünü güncelle
  function togglePaymentType() {
    let method = document.getElementById("method").value;
    let paytype = document.getElementById("paytype");
    paytype.innerHTML = method === "Debit Card"
      ? "<option value='One-time Payment'>One-time Payment</option>"
      : "<option value='One-time Payment'>One-time Payment</option><option value='Installments'>Installmentst</option>";
  }

  // Seans değiştirildiğinde sayfa yenilensin
  document.getElementById("session-time-selector").addEventListener("change", function () {
    const time = this.value;
    window.location.href = "?film_id=<?= $film_id ?>&session_time=" + time;
  });

  // AJAX ile form gönderme
 $('#ticket-form').on('submit', function (e) {
  e.preventDefault();

  if (selectedSeats.length === 0) {
    alert("Please select at least one seat!");
    return;
  }

  $.ajax({
    url: 'buy_ticket_ajax.php',
    type: 'POST',
    data: {
      seats: JSON.stringify(selectedSeats),
      payment_method: $('#method').val(),
      payment_type: $('#paytype').val(),
      session_time: $('#session-time-selector').val(),
      film_id: <?= $film_id ?>
    },
    success: function (response) {
      alert(response);
      window.location.href = 'index.php';
    },
    error: function () {
      alert('An error has occurred!');
    }
  });
});
</script>

</body>
</html>