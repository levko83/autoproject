<html>
<head>
<title>3D Pay Hosting</title>
<meta http-equiv="Content-Language" content="tr">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
</head>
<body>

<h1>3D Payment Page - 3D Pay Hosting Model</h1>
<h3>3D Return Parameters and Values</h3>
<table border="1">
<tr>
	<td><b>Parameter Name</b></td>
	<td><b>Parameter Value</b></td>
</tr>
<?php
$odemeparametreleri = array("AuthCode","Response","HostRefNum","ProcReturnCode","TransId","ErrMsg"); 
foreach($_POST as $key => $value){
	$check=1;
	for($i=0;$i<6;$i++){
		if($key == $odemeparametreleri[$i]){
			$check=0;
			break;
		}	
	}
	if($check == 1){
		echo "<tr><td>".$key."</td><td>".$value."</td></tr>";
	}
}
?>
</table>

<?php

$hashparams = $_POST["HASHPARAMS"];
$hashparamsval = $_POST["HASHPARAMSVAL"];
$hashparam = $_POST["HASH"];
$storekey="123456";
$paramsval="";
$index1=0;
$index2=0;

while($index1 < strlen($hashparams)){

	$index2 = strpos($hashparams,":",$index1);
	$vl = $_POST[substr($hashparams,$index1,$index2 - $index1)];
	if($vl == null)
		$vl = "";
	$paramsval = $paramsval . $vl; 
	$index1 = $index2 + 1;
}
	
$storekey = "123456";
$hashval = $paramsval.$storekey;
$hash = base64_encode(pack('H*',sha1($hashval)));
	
if($paramsval != $hashparamsval || $hashparam != $hash) 	
	echo "<h4>Security Warning. Digital Signature is NOT Valid !</h4>";
		
$mdStatus = $_POST["mdStatus"];
$ErrMsg = $_POST["ErrMsg"];


if($mdStatus == 1 || $mdStatus == 2 || $mdStatus == 3 || $mdStatus == 4){
	echo "<h5>3D Auth is Successful.</h5><br/>";
?>
<h3>Payment Result</h3>
<table border="1">
<tr>
	<td><b>Parameter Name</b></td>
	<td><b>Parameter Value</b></td>
</tr>
<?php
for($i=0;$i<6;$i++){
	$param = $odemeparametreleri[$i];
	echo "<tr><td>".$param."</td><td>".$_POST[$param]."</td></tr>";
}
?>
</table>

<?php
$response = $_POST["Response"];
	if($response == "Approved"){
		echo "Payment is Successful.";
	}
	else{
		echo "Payment is NOT Successful. Error Message : ".$ErrMsg;
	}	
}
else{
	echo "<h5>3D Authentication is NOT Successful !</h5>";
}
?>

</body>
</html>