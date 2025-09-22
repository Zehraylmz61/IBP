<?php 
include 'config.php';
session_start();

// Kategori ve arama kontrolü
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Kategorileri al
$category_result = $conn->query("SELECT DISTINCT category FROM films");

// Film sorgusu
if ($selected_category && $selected_category != 'all' && $search_term != '') {
    $stmt = $conn->prepare("SELECT * FROM films WHERE category = ? AND title LIKE ?");
    $like = "%$search_term%";
    $stmt->bind_param("ss", $selected_category, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($selected_category && $selected_category != 'all') {
    $stmt = $conn->prepare("SELECT * FROM films WHERE category = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($search_term != '') {
    $stmt = $conn->prepare("SELECT * FROM films WHERE title LIKE ?");
    $like = "%$search_term%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM films");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<link rel="icon" type="image/jpeg" href="img/icon.jpeg">




  <meta charset="UTF-8">
  <title>Movie Trailers</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #121212;
      color: white;
    }

    .navbar {
      position: fixed;
      top: 10px;
      left: 10px;
      z-index: 100;
    }

    .navbar a {
      display: inline-block;
      padding: 10px 15px;
      background-color:rgb(136, 136, 136);
      color: white;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      margin-right: 10px;
    }

    .navbar a:hover {
      background-color:rgb(84, 161, 105);
    }

    .film-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 100px;
      gap: 20px;
    }

    .film {
      background-color: #2c2c2c;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      width: 200px;
      height: 450px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.5);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: center;
      transition: transform 0.3s;
    }
    .film:hover {
      transform: scale(1.05);
    }

    .film img {
      width: 100%;
      height: 320px;
      border-radius: 5px;
      object-fit: cover;
      margin-bottom: 8px;
    }

    .film-title {
      font-weight: bold;
      font-size: 16px;
      margin: 0;
    }

    .ticket-link {
      display: inline-block;
      padding: 14px 28px;
      background-color: #3498db;
      color: white;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      font-size: 15px;
      width: auto;
      text-align: center;
      transition: background-color 0.3s;
    }

    .ticket-link:hover {
      background-color: #2980b9;
    }

    .biletlerim-button {
      background-color: #f39c12;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      text-align: center;
      display: inline-block;
      margin-top: 20px;
    }

    .biletlerim-button:hover {
      background-color: #e67e22;
    }

    .filter-forms {
      text-align: center;
      margin-top: 20px;
    }

    .filter-forms input,
    .filter-forms select,
    .filter-forms button {
      padding: 8px;
      border-radius: 5px;
      margin: 5px;
      background-color: #333;
      color: white;
      border: none;
    }

    .filter-forms button {
      background-color: #3498db;
      font-weight: bold;
      cursor: pointer;
    }

    .filter-forms button:hover {
      background-color: #2980b9;
    }
      /* Modal */
  #modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    justify-content: center;
    align-items: center;
    z-index: 999;
  }

  #modal iframe {
    width: 80%;
    height: 80%;
    border-radius: 10px;
  }

  #modal button.close {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    background-color: #e74c3c;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
  }
  </style>
</head>
<body>

<div class="navbar">
  <?php if (isset($_SESSION["user_id"])): ?>
    <a href="logout.php">Log Out</a>
    <a href="my_tickets.php" class="biletlerim-button">My Tickets</a>
  <?php else: ?>
    <a href="login.php">Log In</a>
    <a href="register.php">Register</a>
  <?php endif; ?>
</div>

<h1 style="text-align:center; margin-top: 80px;">Now Showing</h1>

<!-- Kategori ve Arama Formu -->
<div class="filter-forms">
  <form method="get">
    <select name="category" onchange="this.form.submit()">
      <option value="all">All Categories</option>
      <?php while ($cat = $category_result->fetch_assoc()): ?>
        <option value="<?php echo $cat['category']; ?>" <?php if ($cat['category'] == $selected_category) echo 'selected'; ?>>
          <?php echo htmlspecialchars($cat['category']); ?>
        </option>
      <?php endwhile; ?>
    </select>

    <input type="text" name="search" placeholder="Search movies..." value="<?php echo htmlspecialchars($search_term); ?>">
    <button type="submit">Search</button>
  </form>
</div>

<!-- Filmler -->
<div class="film-container">
  <?php while ($film = $result->fetch_assoc()): ?>
    <div class="film" data-trailer="https://www.youtube.com/embed/<?php echo $film['trailer']; ?>">
      <img src="<?php echo $film['poster']; ?>" alt="<?php echo htmlspecialchars($film['title']); ?>">
      <div class="film-title"><?php echo htmlspecialchars($film['title']); ?></div>
      
      <?php if (isset($_SESSION["user_id"])): ?>
        <a href="buy_ticket.php?film_id=<?php echo $film['id']; ?>" class="ticket-link">Buy</a>
      <?php else: ?>
        <p><a href="login.php" class="ticket-link">Log in to purchase tickets</a></p>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>


<!-- Modal -->
<div id="modal" style="display:none;">
  <iframe id="trailer" frameborder="0" allowfullscreen></iframe>
  <button class="close">Close</button>
</div>

<script src="script.js"></script>


</body>
</html>
