<?php
$servername = "localhost"; 
$username = "smartbot_3N";
$password = "rPSQqnk98nj@dl#2";
$dbname = "smartbot_3N";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed"]));
}
$conn->set_charset("utf8mb4");
?>