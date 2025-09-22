<?php
function getDbConnection() {
    $host = "localhost";
    $user = "root";
    $pass = "65540237";
    $dbname = "cinema_db";

    $conn = mysqli_connect($host, $user, $pass, $dbname);
    if (!$conn) {
        die("Kết nối thất bại: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}
