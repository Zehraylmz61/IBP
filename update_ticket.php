<?php
include 'config.php';
session_start();

// Kullanıcı oturum kontrolü
if (!isset($_SESSION["user_id"])) {
    die("You must log in.");
}

// Bilet ID'sini al
$ticket_id = isset($_GET['ticket_id']) ? $_GET['ticket_id'] : 0;
if ($ticket_id == 0) {
    die("Invalid movie ID");
}

// Bilet bilgilerini veritabanından çek
$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if (!$ticket) {
    die("Ticket not found.");
}

// Koltuk numaralarını al (toplam 40 koltuk olduğunu varsayıyoruz)
$total_seats = 40;
$booked_seats = [];
$stmt = $conn->prepare("SELECT seat_number FROM tickets WHERE film_id = ? AND session_time = ?");
$stmt->bind_param("is", $ticket['film_id'], $ticket['session_time']);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $booked_seats[] = $row['seat_number'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Update Ticket</title>
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

        button {
            background-color: #3498db;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        #price-display {
            text-align: center;
            font-size: 18px;
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

<div class="navbar">
  <a href="index.php" class="back-button">← Back to Home</a>
</div>

<h2 style="text-align:center;">Update Ticket</h2>


<div class="seating-chart">
    <?php
    for ($i = 1; $i <= $total_seats; $i++) {
        $class = in_array($i, $booked_seats) ? "seat booked" : "seat";
        echo "<div class='$class' data-seat='$i'>$i</div>";
    }
    ?>
</div>

<form method="post" style="text-align:center; margin-top: 20px;">
    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
    <input type="hidden" name="seat_number" id="selected-seat" value="<?php echo $ticket['seat_number']; ?>">
    <button type="submit" id="update-ticket">Update Ticket</button>
</form>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let selectedSeat = <?= $ticket['seat_number'] ?>; // Varsayılan seçili koltuk
    const seats = document.querySelectorAll('.seat');

    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            const seatId = seat.dataset.seat;

            // Eğer koltuk zaten alınmışsa, seçilmesine izin verilmez
            if (seat.classList.contains('booked')) {
                return;
            }

            // Koltuğa tıklanırsa, seçilir ya da seçilen koltuk temizlenir
            if (seatId == selectedSeat) {
                seat.classList.remove('selected');
                selectedSeat = null;
            } else {
                document.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
                seat.classList.add('selected');
                selectedSeat = seatId;
            }

            // Seçilen koltuğun ID'sini form alanına gönder
            document.getElementById('selected-seat').value = selectedSeat;
        });
    });
</script>

<?php
// Bilet güncellenmesi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seat_number = (int)$_POST['seat_number'];
    $ticket_id = (int)$_POST['ticket_id'];

    if ($seat_number > 0) {
        $update_stmt = $conn->prepare("UPDATE tickets SET seat_number = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $seat_number, $ticket_id);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            echo "<script>alert('The ticket has been successfully updated!'); window.location.href = 'my_tickets.php';</script>";
        } else {
            echo "<script>alert('The ticket could not be updated!');</script>";
        }
    }
}
?>

</body>
</html>
