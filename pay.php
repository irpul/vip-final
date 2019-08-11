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
	
$api 		= $MerchantID;
$order_id 	= rand(1111111111,9999999999);
$redirect 	= urlencode($CallbackURL);
$description = "شارژ اکانت به مدت $cat_time روز";

$result 	= sendt($api,$amount,$order_id,$product,$username,$mobile,$useremail,$redirect,$description);


if( $result['res_code']===1 && is_numeric($result['res_code']) ){
	$go = $result['url'];
	header("Location: $go");
	return;
}
// error
getirpulError($result);
exit;

function sendt($api,$amount,$order_id,$product,$username,$mobile,$email,$redirect , $description){
	$parameters = array
	(
		'plugin'		=> 'VIP_Final',
		'webgate_id' 	=> $api,
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
	);
	//print_r($parameters);exit;
	try {
		$client = new SoapClient('https://irpul.ir/webservice.php?wsdl' , array('soap_version'=>'SOAP_1_2','cache_wsdl'=>WSDL_CACHE_NONE ,'encoding'=>'UTF-8'));
		$result = $client->Payment($parameters);
	}catch (Exception $e) { echo 'Error'. $e->getMessage();  }
	return $result;
}

function getirpulError( $result ){
	if($result['res_code']=='-1'){
		echo "<br> شناسه درگاه مشخص نشده است";
	}
	elseif($result['res_code']=='-2'){
		echo "<br> شناسه درگاه صحیح نمی باشد";
	}
	elseif($result['res_code']=='-3'){
		echo "<br> شما حساب کاربری خود را در ایرپول تایید نکرده اید";
	}
	elseif($result['res_code']=='-4'){
		echo "<br> مبلغ قابل پرداخت تعیین نشده است";
	}
	elseif($result['res_code']=='-5'){
		echo "<br> مبلغ قابل پرداخت صحیح نمی باشد";
	}
	elseif($result['res_code']=='-6'){
		echo "<br> شناسه تراکنش صحیح نمی باشد";
	}
	elseif($result['res_code']=='-7'){
		echo "<br> آدرس بازگشت مشخص نشده است";
	}
	elseif($result['res_code']=='-8'){
		echo "<br> آدرس بازگشت صحیح نمی باشد";
	}
	elseif($result['res_code']=='-9'){
		echo "<br> آدرس ایمیل وارد شده صحیح نمی باشد";
	}
	elseif($result['res_code']=='-10'){
		echo "<br> شماره تلفن وارد شده صحیح نمی باشد";
	}
	elseif($result['res_code']=='-12'){
		echo "<br> نام پلاگین (Plugin) مشخص نشده است";
	}
	elseif($result['res_code']=='-13'){
		echo "<br> نام پلاگین (Plugin) صحیح نیست";
	}
	else{
		echo "<br> پاسخی دریافت نشد لطفا مجدد تلاش کنید. کد خطا : "  . $result['res_code'] . ' ' . $result['status'];
	}		
	exit;
}	
?>