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
    die("Invalid movie ID.");
}

// Bilet iptal işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('The ticket has been successfully cancelled!'); window.location.href = 'my_tickets.php';</script>";
    } else {
        echo "<script>alert('Failed to cancel the ticket!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet İptali</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #121212;
            color: white;
            font-family: Arial, sans-serif;
        }

        form {
            width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #2c2c2c;
            border-radius: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #e74c3c;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            border: none;
        }

        button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Cancel</h2>

<form method="post" style="text-align:center; margin-top: 20px;">
    <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
    <button type="submit">Cancel</button>
</form>

</body>
</html>
