<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "film_site";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}
?>