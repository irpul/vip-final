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
include("config.php");
$username = $_GET['username'];
$user_ver =  mysql_fetch_array(mysql_query("SELECT * FROM `users` where `user`='$username'"));
$useremail = $user_ver['useremail'];
$active = $user_ver['active'];
$time=jgetgmdate($user_ver['time']);
$endtime=jgetgmdate($user_ver['endtime']);?>
<header id="top">
	<div class="container_12 clearfix">
		<div id="logo" class="grid_12">
			<!-- replace with your website title or logo -->
			<a id="site-title" href="#">تائید فرم پرداخت</a>
		</div>
	</div>
</header>

<div class="container_12 clearfix">
<div id="desc" class="grid_12">
وضعیت پرداخت شما :
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
		<td width="102"><font face="Tahoma" size="2">&nbsp;تاریخ ایجاد حساب</font></td>
		<td><?php echo $time['year'] .'/'.$time['mon'] .'/'.$time['mday'];?></td>
	</tr>
	<tr>
		<td width="102"><font face="Tahoma" size="2">&nbsp;تاریخ پایان اعتبار</font></td>
		<td><?php echo $endtime['year'] .'/'.$endtime['mon'] .'/'.$endtime['mday'];?></td>
	</tr>
	<!--<tr>
		<td width="102"><font face="Tahoma" size="2">&nbsp;وضعیت</font></td>
		<td><?php echo ($active==1)? "فعال" : "غیر فعال"; ?></td>
	</tr> !-->
</table>
</div>
</div>
<?php
	
$cat_p = mysql_fetch_array(mysql_query("SELECT * FROM `cat` where id=".$user_ver['cat']));
$Amount = $cat_p['price']; //Amount will be based on Rial


if( isset($_GET['irpul_token']) ){
	$irpul_token 	= $_GET['irpul_token'];
	$decrypted 		= url_decrypt( $irpul_token );
	if($decrypted['status']){
		parse_str($decrypted['data'], $ir_output);
		$tran_id 	= $ir_output['tran_id'];
		$order_id 	= $ir_output['order_id'];
		$amount 	= $ir_output['amount'];
		$refcode	= $ir_output['refcode'];
		$status 	= $ir_output['status'];
		
		if($status == 'paid')	
		{			
			$api = $MerchantID;
			$result = gett($api,$tran_id,$Amount);
				
			if($result == '1'){
		?>
					<div class="container_12 clearfix">
					<div id="desc" class="grid_12">
					 <div class="success msg">
					 <p>پرداخت شما با موفقیت انجام شد</p>
						<p>شماره تراکنش شما : <?php echo $tran_id; ?></p>
						<p>لطفا در نگهداری این شماره تراکنش دقت فرمایید</p>
					</div>
					</div>
					</div>
			
		<?php 
			$id= $user_ver['id'];
			//save in htaccess
			$password=$user_ver['pass'];
			$save_user=save_user($username,$password);
			if($save_user==1){
				$sql_del=mysql_query("UPDATE `users` SET `active` = '1' WHERE `id` ='".$id."';");
				
							$to      = $useremail;
							$subject = 'اکانت شما فعال شد';
							$message = "
								<b>از اینکه اشتراک ما را پذیرفته اید متشکریم </b><br/>
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
			 <b>شماره تراکنش پرداخت شما : <span style='color:#900'>". $result['RefID'] ."</span> میباشد </b>";
				mail($to, $subject, $message);
			};
			}
			else {
		?>
			<div class="error msg">
		<?php echo 'تراکنش پرداختی با شکست مواجه شد' ?>
			</div>
		<?php  } 

		}
	}
}

?>

</body>

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

function gett($api,$tran_id,$amount){
	$parameters = array
	(
		'webgate_id'	=> $api,
		'tran_id' 		=> $tran_id,
		'amount'	 	=> $amount,
	);
	try {
		$client = new SoapClient('https://irpul.ir/webservice.php?wsdl' , array('soap_version'=>'SOAP_1_2','cache_wsdl'=>WSDL_CACHE_NONE ,'encoding'=>'UTF-8'));
		$result = $client->PaymentVerification($parameters);
	}catch (Exception $e) { echo 'Error'. $e->getMessage();  }
	return $result;
}
?>