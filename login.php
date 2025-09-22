<?php
session_start();
include("config.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];

  // Şifre SHA-256 ile hash'leniyor
  $hashedPassword = hash('sha256', $password);

  // SHA hash ile veritabanında eşleşme kontrolü yapılıyor
  $stmt = $conn->prepare("SELECT id FROM users WHERE username=? AND password_hash=?");
  $stmt->bind_param("ss", $username, $hashedPassword);
  $stmt->execute();
  $stmt->bind_result($id);

  if ($stmt->fetch()) {
    $_SESSION["user_id"] = $id;
    header("Location: index.php");
    exit;
  } else {
    $error = "Invalid entry.";
  }
}
?>

<!DOCTYPE html>
<html lang="tr"> <!-- Sayfa dili Türkçe -->
<head>
  <meta charset="UTF-8">
  <title>Log In</title>
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

    .login-box {
      background: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 10px;
      color: white;
      width: 300px;
      box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    .login-box h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .login-box input {
      width: 80%;
      padding: 10px;
      margin: 10px auto;
      border: none;
      border-radius: 5px;
      display: block;
    }

    .login-box button {
      width: 50%;
      padding: 10px;
      background-color: #3498db;
      border: none;
      border-radius: 5px;
      color: white;
      font-weight: bold;
      cursor: pointer;
      display: block;
      margin: 0 auto;
    }

    .login-box button:hover {
      background-color: #2980b9;
    }

    .error {
      color: #e74c3c;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Log In</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post" onsubmit="return validateForm()">
      <input id="username" name="username" placeholder="UserName">
      <input id="password" type="password" name="password" placeholder="Password">
      <button type="submit">Log In</button>
    </form>
  </div>

  <script>
    function validateForm() {
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;

      if (username === "") {
        alert("Please fill out the username.");
        return false;
      }

      if (password === "") {
        alert("Please fill out the password.");
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