<?php
function getDbConnection()
{
    $host = "localhost";
    $user = "root";
    $pass = "65540237";
    $dbname = "cinema_db";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}
?>