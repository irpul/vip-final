<?php 
include("tarikh.php");
include("fun.php");
error_reporting(0);
$usernamedb = "root"; 
$passworddb = ""; 
$serverdb = "localhost";
$db_conn = "vip";
$GLOBALS["localhost"]="1";
$MerchantID= "50024210";

date_default_timezone_set("Asia/Tehran");

$connect = mysql_connect($serverdb,$usernamedb,$passworddb) or die(mysql_error()); 
mysql_set_charset("utf8",$connect);
@mysql_select_db("$db_conn") or die(mysql_error()); 
?>
