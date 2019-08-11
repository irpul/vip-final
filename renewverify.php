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
	function url_decrypt($string){
		$counter = 0;
		$data = str_replace(array('-','_','.'),array('+','/','='),$string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
		$data .= substr('====', $mod4);
		}
		$decrypted = base64_decode($data);
		
		$check = array('tran_id','order_id','amount','refcode','status');
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
			$tran_id 	= $ir_output['tran_id'];
			$order_id 	= $ir_output['order_id'];
			//$amount 	= $ir_output['amount'];
			$refcode	= $ir_output['refcode'];
			$status 	= $ir_output['status'];
			
			if($status == 'paid'){
				$api = $MerchantID;
				//$result = gett($api,$tran_id,$Amount);
				
				$parameters = array(
					'webgate_id'	=> $MerchantID,
					'tran_id' 		=> $tran_id,
					'amount'	 	=> $Amount,
				);
				try {
					$client = new SoapClient('https://irpul.ir/webservice.php?wsdl' , array('soap_version'=>'SOAP_1_2','cache_wsdl'=>WSDL_CACHE_NONE ,'encoding'=>'UTF-8'));
					$result = $client->PaymentVerification($parameters);
				}catch (Exception $e) { echo 'Error'. $e->getMessage();  }
					
				if($result == '1'){ ?>
					<div class="container_12 clearfix">
					<div id="desc" class="grid_12">
					 <div class="success msg">
					 <p>پرداخت شما با موفقیت انجام شد</p>
						<p>شماره تراکنش شما : <?php echo $tran_id ?></p>
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
					 <b>شماره تراکنش پرداخت شما : <span style='color:#900'>". $tran_id ."</span> میباشد </b>";
					//echo "$to, $subject, $message";
					mail($to, $subject, $message);
				}
				else{
					echo "<div class='error msg'>پرداخت با موفقیت انجام نشد کد خطا: $result</div>";
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