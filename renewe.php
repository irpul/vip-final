<?php 
@session_start();
@ob_start();
include("config.php");
if(check()!="user_1")
{
echo <<<HTML
<meta http-equiv="refresh" content="0; url= index.php">
HTML;
exit();
}
$username=$_SESSION["resller_username"];
$password = $_SESSION ["resller_password"];

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<title>سامانه مشترکین سایت - VIP Service</title>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="css/style.css">
<!--[if lte IE 8]>
<script type="text/javascript" src="js/html5.js"></script>
<![endif]-->

</head>
<body>
<header id="top">
	<div class="container_12 clearfix">
		<div id="logo" class="grid_12">
			<!-- replace with your website title or logo -->
			<a id="site-title" href="#">تمدید اشتراک در سامانه</a>
		</div>
	</div>
</header>
<div class="container_12 clearfix">
<div id="desc" class="grid_12">
<p><?php echo $username ;?> , عزیز  </p>
<p>از اینکه دوباره تصمیم به تمدید اشتراک خود در سامانه مشترکین ما گرفته اید متشکریم لطفا از طرح های زیر یکی را انتخاب نمایید .</p>
</div>

<!--  make account -->
<div class="box3 grid_12">
<?php
function post_data($url,$params,$token) {
	ini_set('default_socket_timeout', 15);

	$headers = array(
		"Authorization: token= {$token}",
		'Content-type: application/json'
	);

	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($handle, CURLOPT_TIMEOUT, 40);

	curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params) );
	curl_setopt($handle, CURLOPT_HTTPHEADER, $headers );

	$response = curl_exec($handle);
	//error_log('curl response1 : '. print_r($response,true));

	$msg='';
	$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));

	$status= true;

	if ($response === false) {
		$curl_errno = curl_errno($handle);
		$curl_error = curl_error($handle);
		$msg .= "Curl error $curl_errno: $curl_error";
		$status = false;
	}

	curl_close($handle);//dont move uppder than curl_errno

	if( $http_code == 200 ){
		$msg .= "Request was successfull";
	}
	else{
		$status = false;
		if ($http_code == 400) {
			$status = true;
		}
		elseif ($http_code == 401) {
			$msg .= "Invalid access token provided";
		}
		elseif ($http_code == 502) {
			$msg .= "Bad Gateway";
		}
		elseif ($http_code >= 500) {// do not wat to DDOS server if something goes wrong
			sleep(2);
		}
	}

	$res['http_code'] 	= $http_code;
	$res['status'] 		= $status;
	$res['msg'] 		= $msg;
	$res['data'] 		= $response;

	if(!$status){
		//error_log(print_r($res,true));
	}
	return $res;
}


	if($_POST['submitrenew'] and !$_POST['cat']) echo '<div class="error msg">'.'لطفا مدت زمان اشتراک خود را تعیین کنید'.'</div>';
	if($_POST['cat'] and $_POST['submitrenew']){
		$cat 			= $_POST['cat'];
		$username		= $_SESSION["resller_username"];	
		$cat_p 			= mysql_fetch_assoc(mysql_query("SELECT * FROM cat where id='$cat'"));
		$catid 			= $cat_p['id'];
		$amount 		= $cat_p['price']; //Amount will be based on Rial  - Required
		$product 		= $cat_p['title'];
		$time 			= $cat_p['time'];
		$description 	= "تمدید اکانت به مدت $time روز";  // Required
		
		$user_p =  mysql_fetch_assoc(mysql_query("SELECT * FROM users where user='$username'"));
		
		// this is for check sesion verify cat
		$_SESSION["catid"]	= $catid;
		// sending to the bank for renew
		$url			= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$url 			= explode('/renewe.php', $url);
		$callback_url 	= $url[0].'/renewverify.php?username='.$username;
		
		$email 			= $user_p['useremail'];
		$mobile 		= $user_p['mobile'];
		$order_id 		= rand(1111111111,9999999999);
		
		
		$parameters = array(
			'method' 		=> 'payment',
			'order_id'		=> $order_id,
			'product'		=> $product,
			'payer_name'	=> $username,
			'phone' 		=> '',
			'mobile' 		=> $mobile,
			'email' 		=> $email,
			'amount' 		=> $amount,
			'callback_url' 	=> $callback_url,
			'address' 		=> '',
			'description' 	=> $description,
			'test_mode' 	=> false,
		);

		$result 	= post_data('https://irpul.ir/ws.php', $parameters, $token );

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
	}
?>
</div>

<div class="box2 grid_12">
	<div class="regiter_title">
     دوره های موجود جهت تمدید اشتراک 
    </div>
  <div class="reg">
  <form action="" method="post">
  <dt><label for="newstitle" style="color:#06C;font-family: 'BKoodakBold'; font-size:18px">نوع اکانت</label></dt>
						<div style="margin:10px; background-color:#ebebeb; padding:10px">
                      
                        <dd>
<?php
$result = mysql_query("SELECT * FROM cat");
while($r=mysql_fetch_array($result))
{
$id=$r["id"];
$title=$r["title"];
echo <<<HTM
				<b><input type="radio" name="cat" value="$id">$title</b><hr>
				
HTM;
}				
?>
  </dd>
  </div>
  <button class="button red" type="submit" value="submitrenew" name="submitrenew" 
                      >تمدید اشتراک</button>
  
  </form>
  
  </div>
  </div>
  </div>
  </body>
  </html>