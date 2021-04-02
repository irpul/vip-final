<?php
@ob_start();
include("config.php");

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<title>ورود به ایرپولی</title>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="css/style.css">
<!--[if lte IE 8]>
<script type="text/javascript" src="js/html5.js"></script>
<![endif]-->

</head>
<?php

$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$url = explode('/pay.php', $url);

$useremail 		= $_POST['useremail'];
$username		= $_POST['username'];
$mobile			= $_POST['mobile'];
$password		= $_POST['password'];
$CallbackURL 	= $url[0].'/verify.php?username='.$username;
$cat 			= $_POST['cat'];
$active 		= '0';
$cat_p 			= mysql_fetch_array(mysql_query("SELECT * FROM cat where id='$cat'"));
$cat_time		= $cat_p['time'];
$product 		= $cat_p['title'];
$amount 		= $cat_p['price'];//Amount will be based on Rial  - Required
$user_p 		= mysql_num_rows(mysql_query("SELECT * FROM users where user='$username'"));
$time			= time();

if(is_numeric($cat_time)) 
{
	$endtime= $time + ($cat_time * 24 * 60 * 60) ;
}
if ($useremail && $username && $password && $cat && $mobile) {
	$sqli = mysql_query("INSERT INTO users (user,pass, mobile ,time,endtime,useremail,cat,active ) VALUES ('$username','$password','$mobile', '$time','$endtime','$useremail','$cat','0')");
}
else {
	echo "اطلاعات ارسالی کامل نیست ...";	
}	
	

$order_id 	= rand(1111111111,9999999999);
$redirect 	= urlencode($CallbackURL);
$description = "شارژ اکانت به مدت $cat_time روز";

$result 	= sendt($token,$amount,$order_id,$product,$username,$mobile,$useremail,$redirect,$description);

if( isset($result['http_code']) ){
	$data =  json_decode($result['data'],true);

	if( isset($data['code']) && $data['code'] === 1){
		header("Location: " . $data['url']);
		exit;
	}
	else{
		echo "Error Code: ".$data['code'] . ' ' . $data['status'];
	}
}else{
	echo 'پاسخی از سرویس دهنده دریافت نشد. لطفا دوباره تلاش نمائید';
}

//getirpulError($result);
exit;

function sendt($token,$amount,$order_id,$product,$username,$mobile,$email,$redirect , $description){
	$parameters = array(
		'method' 		=> 'payment',
		'order_id'		=> $order_id,
		'product'		=> $product,
		'payer_name'	=> $username ,
		'phone' 		=> '',
		'mobile' 		=> $mobile,
		'email' 		=> $email,
		'amount' 		=> $amount,
		'callback_url' 	=> $redirect,
		'address' 		=> '',
		'description' 	=> $description,
		'test_mode' 	=> false,
	);
	//print_r($parameters);exit;

	$result 	= post_data('https://irpul.ir/ws.php', $parameters, $token );

	return $result;
}

?>