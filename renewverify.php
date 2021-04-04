<!DOCTYPE HTML>
<html lang="en">
<head>
<title>پنل کاربران</title>
<meta charset="utf-8">

<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/skins/red.css" title="gray">
<link rel="stylesheet" type="text/css" href="css/superfish.css">
<!--[if lte IE 8]>
<script type="text/javascript" src="js/html5.js"></script>
<script type="text/javascript" src="js/selectivizr.js"></script>
<script type="text/javascript" src="js/excanvas.min.js"></script>
<![endif]-->

</head>
<body>
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
$username 	= $_SESSION ["resller_username"];
$catid 		= $_SESSION["catid"];
$cat_p 		= mysql_fetch_array(mysql_query("SELECT * FROM `cat` where id=".$catid));
$cat_time	= $cat_p['time'];
$time		= time();
if(is_numeric($cat_time)){
	$endtime= $time + ($cat_time * 24 * 60 * 60); 
}

?>
<header id="top">
	<div class="container_12 clearfix">
		<div id="logo" class="grid_12">
			<!-- replace with your website title or logo -->
			<a id="site-title" href="#">تائید فرم پرداخت</a>
		</div>
	</div>
</header>

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


	function url_decrypt($string){
		$counter = 0;
		$data = str_replace(array('-','_','.'),array('+','/','='),$string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
		$data .= substr('====', $mod4);
		}
		$decrypted = base64_decode($data);
		
		$check = array('trans_id','order_id','amount','refcode','status');
		foreach($check as $str){
			str_replace($str,'',$decrypted,$count);
			if($count > 0){
				$counter++;
			}
		}
		if($counter === 5){
			return array('data'=>$decrypted , 'status'=>true);
		}else{
			return array('data'=>'' , 'status'=>false);
		}
	}
	
	$cat_p = mysql_fetch_array(mysql_query("SELECT * FROM `cat` where id=".$catid));
	$Amount = $cat_p['price']; //Amount will be based on Rial
	
	if( isset($_GET['irpul_token']) ){
		$irpul_token 	= $_GET['irpul_token'];
		$decrypted 		= url_decrypt( $irpul_token );
		if($decrypted['status']){
			parse_str($decrypted['data'], $ir_output);
			$trans_id 	= $ir_output['trans_id'];
			$order_id 	= $ir_output['order_id'];
			//$amount 	= $ir_output['amount'];
			$refcode	= $ir_output['refcode'];
			$status 	= $ir_output['status'];
			
			if($status == 'paid'){

				$parameters = array(
					'method' 	    => 'verify',
					'trans_id' 		=> $trans_id,
					'amount'	 	=> $Amount,
				);

				$result =  post_data('https://irpul.ir/ws.php', $parameters, $token );

				if( isset($result['http_code']) ){
					$data =  json_decode($result['data'],true);

					if( isset($data['code']) && $data['code'] === 1){
?>

						<div class="container_12 clearfix">
							<div id="desc" class="grid_12">
								<div class="success msg">
									<p>پرداخت شما با موفقیت انجام شد</p>
									<p>شماره تراکنش شما : <?php echo $trans_id ?></p>
									<p>لطفا در حفظ این شماره تراکنش دقت فرمایید</p>
								</div>
							</div>
						</div>
<?php
						$sql_del	= mysql_query("UPDATE `users` SET `active` = '1' , `time` ='". $time. "'  , `endtime`= '". $endtime ."' , `cat`='". $catid."' WHERE `id` ='".$id."';");
						$time		= jgetgmdate($time);
						$endtime	= jgetgmdate($endtime);

						$user_ver 	=  mysql_fetch_array(mysql_query("SELECT * FROM `users` where `user`='$username'"));
						$useremail 	= $user_ver['useremail'];
						$active 	= $user_ver['active'];
						$id			= $user_ver['id'];
						//save in htaccess
						$password 	= $user_ver['pass'];

						$to      	= $useremail;
						$subject 	= 'اکانت شما فعال شد';
						$message 	= "<b>از اینکه اشتراک ما را پذیرفته اید متشکریم </b><br/>
					<p> ما همواره در تلاشیم تا بتوانیم بهترین خدمات را برای شما فرآهم آوریم </p>
					<table border='0' width='100%'>
						<tr>
							<td width='132'><font face='Tahoma' size='2'>&nbsp;نام کاربری</font></td>
							<td>". $username . "</td>
						</tr>
						<tr>
							<td width='102'><font face='Tahoma' size='2'>&nbsp;تاریخ ایجاد حساب</font></td>
							<td>" .  $time['year'] .'/'.$time['mon'] .'/'.$time['mday'] . "</td>
						</tr>
						<tr>
							<td width='102'><font face='Tahoma' size='2'>&nbsp;تاریخ پایان اعتبار</font></td>
							<td>".$endtime['year'] .'/'.$endtime['mon'] .'/'.$endtime['mday'] . "</td>
						</tr>
						</table><br />
					 <p> هم اکنون شما میتوانید با رمز عبور: <b>" . $user_ver['pass'] ."</b> در سایت وارد شوید</p>
					 <b>شماره تراکنش پرداخت شما : <span style='color:#900'>". $trans_id ."</span> میباشد </b>";
						//echo "$to, $subject, $message";
						mail($to, $subject, $message);
					}
					else{
						echo "<div class='error msg'>'خطا در پرداخت. کد خطا: '" . $data['code'] . '<br/>' . $data['status'] . "</div>";
					}
				}else{
					echo "<div class='error msg'>پاسخی از سرویس دهنده دریافت نشد. لطفا دوباره تلاش نمائید</div>";
					echo '';
				}
			}else{
				echo "<div class='error msg'>فاکتور پرداخت نشده است</div>";
			}
		}
	}
?>
<div class="container_12 clearfix">
	<div id="desc" class="grid_12">
	وضعیت حساب شما :
	</div>
</div>

<div class="container_12 clearfix">
	<div id="desc" class="grid_12">
		<table border="0" width="100%">
			<tr>
				<td width="132"><font face="Tahoma" size="2">&nbsp;نام کاربری</font></td>
				<td><?php echo $username; ?></td>
			</tr>
			<tr>
				<td width="102"><font face="Tahoma" size="2">&nbsp;تاریخ شارژ حساب</font></td>
				<td><?php echo $time['year'] .'/'.$time['mon'] .'/'.$time['mday'];?></td>
			</tr>
			<tr>
				<td width="102"><font face="Tahoma" size="2">&nbsp;تاریخ پایان اعتبار</font></td>
				<td><?php echo $endtime['year'] .'/'.$endtime['mon'] .'/'.$endtime['mday'];?></td>
			</tr>
			<tr>
				<td width="102"><font face="Tahoma" size="2">&nbsp;وضعیت</font></td>
				<td><?php echo ($active==1)? "فعال" : "غیر فعال"; ?></td>
			</tr> 
		</table>
	</div>
</div>