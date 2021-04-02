<?php
include("tarikh.php");
include("fun.php");

error_reporting(1);

$usernamedb = "root"; 
$passworddb = ""; 
$serverdb = "localhost";
$db_conn = "vip";
$GLOBALS["localhost"]="1";

$token= "8f90d8b359c6bd1a11973c7b3c2803bb";

date_default_timezone_set("Asia/Tehran");

$connect = mysql_connect($serverdb,$usernamedb,$passworddb) or die(mysql_error()); 
mysql_set_charset("utf8",$connect);
@mysql_select_db("$db_conn") or die(mysql_error()); 
?>
