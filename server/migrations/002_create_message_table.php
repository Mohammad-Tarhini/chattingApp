<?php
require_once("../connection/connection.php");

$sql="CREATE TABLE message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receiver_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('sent','delivered','seen') DEFAULT 'sent',
    delivered_at DATETIME NULL,
    send_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at datetime ,

    FOREIGN KEY (receiver_id) REFERENCES users(id),
    FOREIGN KEY (sender_id) REFERENCES users(id)
);
"
$query = $connection->prepare($sql);
$query->execute();

echo "Table(s) Created!";



?>