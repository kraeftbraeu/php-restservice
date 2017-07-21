<?php
include("ht/variablesDb.inc");
$link = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);
if (!$link)
    die('keine Verbindung möglich: ' . mysql_error());
mysqli_set_charset($link,'utf8');

$algo = "md5";
$GLOBALS["logDb"] = true;
?>