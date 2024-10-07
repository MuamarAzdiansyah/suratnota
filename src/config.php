<?php
$servername = "db";
$username = "amar";
$password = "amar1403";
$dbname = "suratdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
