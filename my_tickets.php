<?php
include 'config.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    die("You must log in.");
}

$user_id = $_SESSION["user_id"];

/* Film bilgileri de gelsin diye JOIN kullanalım */
$stmt = $conn->prepare("
    SELECT t.id AS ticket_id,
           t.seat_number,
           t.session_time,
           f.title,
           f.poster
    FROM tickets t
    JOIN films  f ON f.id = t.film_id
    WHERE t.user_id = ?
    ORDER BY t.session_time ASC
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>My Tickets</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#121212;
    --card:#1e1e1e;
    --accent:#3498db;
    --accent-hover:#2980b9;
    --danger:#e74c3c;
    --danger-hover:#c0392b;
    --text:#f5f5f5;
    --muted:#9b9b9b;
    --radius:12px;
    --shadow:0 4px 12px rgba(0,0,0,.35);
    --transition:.25s ease-in-out;
}

*{box-sizing:border-box;margin:0;padding:0}
body{
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',Arial,Helvetica,sans-serif;
    min-height:100vh;
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:60px 20px;
}
h1{
    font-weight:600;
    margin-bottom:30px;
    text-align:center;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:24px;
    width:100%;
    max-width:1100px;
}

.card {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;           /* Görsel ve içerikleri ortala */
    transition: transform var(--transition);
    width: 100%;
    max-width: 300px;              /* Kartların taşmaması için sınırla */
    margin: auto;
}

.card:hover{transform:translateY(-6px)}

.poster {
    width: auto;                /* genişliği içeriğe göre ayarla */
    height: 380px;              /* sabit yükseklik */
    max-width: 100%;            /* taşmayı engelle */
    object-fit: cover;
    object-position: center;
    align-self: center;         /* ortalamak için */
    padding: 20px; 
}


.content{padding:20px;flex:1}

.title{
    font-size:20px;
    font-weight:600;
    margin-bottom:8px;
}

.info{
    font-size:14px;
    color:var(--muted);
    margin-bottom:18px;
    line-height:1.4;
}

.actions{
    display:flex;
    gap:10px;
    margin-top:auto;
    width: 100%;
    justify-content: center; 
}

button,a.action{
    flex:1;
    text-align:center;
    padding:10px 15px;
    border:none;
    border-radius:var(--radius);
    font-weight:600;
    cursor:pointer;
    transition:background var(--transition),transform var(--transition);
    text-decoration:none;
    color:#fff;
     max-width: 150px;
     
}
button:hover,a.action:hover{transform:translateY(-2px)}

.update{background:var(--accent)}
.update:hover{background:var(--accent-hover)}

.cancel{background:var(--danger)}
.cancel:hover{background:var(--danger-hover)}

.navbar {
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1000;
}

.back-button {
  display: inline-block;
  padding: 10px 16px;
  background-color: var(--accent);
  color: white;
  border-radius: var(--radius);
  text-decoration: none;
  font-weight: 600;
  transition: background-color var(--transition);
}

.back-button:hover {
  background-color:darkred;
}

</style>
</head>
<body>
    <div class="navbar">
  <a href="index.php" class="back-button">← Back to Home</a>
</div>


<h1>My Tickets</h1>

<?php if($result->num_rows===0): ?>
    <p>You don't have any tickets yet.</p>
<?php else: ?>
<div class="grid">
<?php while($row=$result->fetch_assoc()): ?>
    <div class="card">
        <img class="poster" src="<?= htmlspecialchars($row['poster']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
        <div class="content">
            <div class="title"><?= htmlspecialchars($row['title']) ?></div>
            <div class="info">
                Session: <?= substr($row['session_time'],0,5) ?><br>
                Seat No: <?= $row['seat_number'] ?>
            </div>

            <div class="actions">
                <a class="action update" href="update_ticket.php?ticket_id=<?= $row['ticket_id'] ?>">Update</a>
                <form style="flex:1" method="post" action="cancel_ticket.php?ticket_id=<?= $row['ticket_id'] ?>" onsubmit="return confirm('Are you sure you want to cancel the ticket?');">
                    <button class="cancel" type="submit">Cancel</button>
                </form>
            </div>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php endif; ?>

</body>
</html>