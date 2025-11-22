<?php
require_once("../connection/connection.php");

$sql="CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) not null ,
    password Text NOT NULL

);
"
$query = $connection->prepare($sql);
$query->execute();

echo "Table(s) Created!";



?>