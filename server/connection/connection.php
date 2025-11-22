<?php

$connection=new mysqli("localhost","root","","chattingapp_db");

if($connection ->connect_error){
    die("connection error" . $connection-> connect_error);
}

?>