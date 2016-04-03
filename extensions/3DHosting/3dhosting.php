<html>
<head>
<title>3D PAY Hosting</title>
<meta http-equiv="Content-Language" content="tr">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
  
</head>
<body>

<?php
	$clientId = "160000002"; // Merchant ID
	$amount = "9950"; // Total amount ( shopping total, checkout total )
	$oid = "2"; // Order Number, may be produced by some sort of code and set here, if it doesn't exist gateway produces it and returns
	$okUrl = "http://new.inter-line.ru/3DHostingOdeme.php"; // return page ( hosted at merchant's server ) when process finished successfully, process means 3D authentication and payment after 3D auth
	$failUrl = "http://new.inter-line.ru/3DHostingOdeme.php"; // return page ( hosted at merchant's server ) when process finished UNsuccessfully, process means 3D authentication and payment after 3D auth
	$rnd = microtime(); // Used to generate some random value
	$islemtipi="Auth"; // Transacation Type 
	$storekey = "123456"; //  Merchant's store key, it must be produced using merchant reporting interface and set here.
	$taksit = ""; //  Installment (  how many installments will be for this sale )
	$hashstr = $clientId . $oid . $amount . $okUrl . $failUrl . $islemtipi . $taksit . $rnd . $storekey; // hash string
	$hash = base64_encode(pack('H*',sha1($hashstr))); // hash value
?>

<center>
<form method="post" action="https://testsanalpos.est.com.tr/fim/est3dgate">
<input type="hidden" name="clientid" value="<?php echo $clientId?>">
<input type="hidden" name="amount" value="<?php echo $amount?>">
<input type="hidden" name="oid" value="<?php echo $oid?>">	
<input type="hidden" name="okUrl" value="<?php echo $okUrl?>" >
<input type="hidden" name="failUrl" value="<?php echo $failUrl?>" >
<input type="hidden" name="islemtipi" value="<?php echo $islemtipi?>" >
<input type="hidden" name="taksit" value="<?php echo $taksit?>">
<input type="hidden" name="rnd" value="<?php echo $rnd?>" >
<input type="hidden" name="hash" value="<?php echo $hash?>" >
<input type="hidden" name="storetype" value="3d_pay_hosting" >
<input type="hidden" name="refreshtime" value="0" >
<input type="hidden" name="lang" value="ru">
<input type="hidden" name="currency" value="643" />
<input type="hidden" name="encoding" value="utf-8" />

<input type="hidden" name="tel" value="012345678">
<input type="hidden" name="Email" value="test@test.com">
<input type="hidden" name="firmaadi" value="Billing Company"> <!-- Название компании-получателя счета -->
<input type="hidden" name="Faturafirma" value="John Smith"> <!-- Имя/фамилия получателя счета -->
<input type="hidden" name="Fadres" value="Address line 1"> <!-- Адрес -->
<input type="hidden" name="Fadres2" value="Address line 2"> <!-- Адрес -->
<input type="hidden" name="Filce" value="Warsaw"> <!-- Город получателя счета -->
<input type="hidden" name="Fil" value="mystate"> <!-- Штат/область получателя счета -->
<input type="hidden" name="Fpostakodu" value="12345"> <!-- Почтовый индекс получателя счета -->
<input type="hidden" name="Fulkekodu" value="400"> <!-- Код страны получателя счета -->
                
<input type="submit" value="Continue" />
</form>          
</center>
		
</body>
</html>