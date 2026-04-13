<?php
$host = "localhost";
$user = "mundol1_linusmundo7";
$pass = "@Delasalle17";
$db = "mundol1_Montclair_State_Find";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error){
    die("Connection failed:". $conn->connect_error);
}
?>