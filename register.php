<?php
include 'config.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $email = $_POST["email"];
  $password = hash('sha256', $_POST["password"]); // SHA-256 ile şifreleniyor

  // E-posta daha önce kayıtlı mı kontrol et
  $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $checkStmt->bind_param("s", $email);
  $checkStmt->execute();
  $checkStmt->store_result();

  if ($checkStmt->num_rows > 0) {
    $error = "This email address is already registered.";
  } else {
    // Kayıt işlemi
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();

    header("Location: login.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: url('img/arkaplan.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .register-box {
      background: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 10px;
      color: white;
      width: 300px;
      box-shadow: 0 0 15px rgba(0,0,0,0.5);
    }

    .register-box h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .register-box input {
      width: 80%;
      padding: 10px;
      margin: 10px auto;
      border: none;
      border-radius: 5px;
      display:block;
    }

    .register-box button {
      width: 50%;
      padding: 10px;
      background-color: #27ae60;
      border: none;
      border-radius: 5px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      display: block;
      margin: 0 auto;
    }

    .register-box button:hover {
      background-color: #219150;
    }

    .error {
      color: #e74c3c;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="register-box">
    <h2>Register</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post" onsubmit="return validateRegisterForm()">
      <input id="username" name="username" placeholder="UserName">
      <input id="email" name="email" placeholder="Email" type="email">
      <input id="password" type="password" name="password" placeholder="Password">
      <button type="submit">Register</button>
    </form>
  </div>

  <script>
    function validateRegisterForm() {
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;

      if (username === "" || email === "" || password === "") {
        alert("All fields must be filled.");
        return false;
      }

      if (!email.includes("@") || !email.includes(".")) {
        alert("Please enter a valid email address.");
        return false;
      }

      if (password.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
      }

      return true;
    }
  </script>
</body>
</html>