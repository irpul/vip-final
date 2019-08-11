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
			'plugin' 		=> 'VIP_Final',
			'webgate_id' 	=> $MerchantID,
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
		);
		try {
			$client = new SoapClient('https://irpul.ir/webservice.php?wsdl' , array('soap_version'=>'SOAP_1_2','cache_wsdl'=>WSDL_CACHE_NONE ,'encoding'=>'UTF-8'));
			$result = $client->Payment($parameters);
		}catch (Exception $e) { echo 'Error'. $e->getMessage();  }

		if( $result['res_code'] === 1 ){
			header("Location: " . $result['url']);
		} else {
			echo 'ERR: ' . $result['res_code'] . ' ' . $result['status'];
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