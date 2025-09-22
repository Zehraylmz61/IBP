<?php
include 'config.php';
session_start();

// Kullanıcı oturumu kontrolü
if (!isset($_SESSION["user_id"])) {
    die("You must log in.");
}

// JSON verisini kontrol et
if (!isset($_POST["seats"]) || empty($_POST["seats"])) {
    die("No seats selected.");
}

$seats = json_decode($_POST["seats"], true);

if (!is_array($seats) || count($seats) === 0) {
    die("No valid seats selected.");
}

$pay = $_POST["payment_method"] ?? '';
$pay_type = $_POST["payment_type"] ?? '';
$film_id = isset($_POST["film_id"]) ? intval($_POST["film_id"]) : 0;
$session_time = $_POST["session_time"] ?? '';
$total_price = 0;

// Temel doğrulama
if ($film_id <= 0 || empty($session_time) || empty($pay) || empty($pay_type)) {
    die("Invalid input.");
}

foreach ($seats as $seat) {
    $seat_id = intval($seat['id']);
    $ticket_type = $seat['type'] ?? '';

    // Geçersiz koltuk id'si veya türü
    if ($seat_id <= 0 || !in_array($ticket_type, ['Student', 'Adult'])) {
        die("Invalid seat data.");
    }

    // Koltuk zaten alınmış mı kontrol et
    $check = $conn->prepare("SELECT id FROM tickets WHERE film_id = ? AND seat_number = ? AND session_time = ?");
    $check->bind_param("iis", $film_id, $seat_id, $session_time);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Seat number $seat_id is already taken.");
    }

    // Fiyat hesapla
    $price = ($ticket_type === 'Student') ? 100 : 200;
    $total_price += $price;

    // Veritabanına ekle
    $stmt = $conn->prepare("INSERT INTO tickets (user_id, film_id, seat_number, ticket_type, payment_method, payment_type, session_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $_SESSION["user_id"], $film_id, $seat_id, $ticket_type, $pay, $pay_type, $session_time);
    $stmt->execute();
}

echo "Tickets purchased successfully. Total Price: " . $total_price . " TL";
?>