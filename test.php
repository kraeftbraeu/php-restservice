<?php

$dbServername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "wl2";

$link = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);
if (!$link)
    die('keine Verbindung möglich: ' . mysql_error());
mysqli_set_charset($link,'utf8');

$result = mysqli_query($link, "select * from `user`");

$num=$result->num_rows;
echo "<b><center>Database Output</center></b><br><br>";
while ($row = $result->fetch_assoc())
    echo "<u>".$row['u_id']."</u>&nbsp;<b>".$row['u_name']."</b>&nbsp;<b>".$row['u_log']."</b>";
?>
