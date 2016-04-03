<?php 

class NotifyController  extends BaseController {
	public $layout = 'ajax';
	public function index() {
		$this->layout = "ajax";
		echo "notify";
	}
	public function paypal() {
		$this->layout = "ajax";
		
		/*$sandbox = SettingsmerchantsModel::get('paypal_mode');
		if ($sandbox=='1') {
			$path = "sandbox.paypal";
		} else {
			$path = "paypal";
		}
		
		// $postdata=""; 
		// foreach ($_POST as $key=>$value) $postdata.=$key."=".urlencode($value)."&"; 
		
		$postdata = "mc_gross=79.97&invoice=163+-+Dannik+Dan&protection_eligibility=Ineligible&item_number1=10001&payer_id=QZGH2MCW25SEA&tax=0.00&payment_date=14%3A28%3A57+Jan+27%2C+2016+PST&payment_status=Pending&charset=windows-1252&mc_shipping=0.00&mc_handling=0.00&first_name=Dan&notify_version=3.8&custom=163&payer_status=verified&num_cart_items=1&mc_handling1=0.00&verify_sign=A0asK6oiIZmtBFrRCq-Iiqf8lrk.AQaoCuVZ.u-z18gBMBnxEoHjGuf2&payer_email=danlapteacru%40gmail.com&mc_shipping1=0.00&tax1=0.00&txn_id=2XM33368906547727&payment_type=instant&payer_business_name=Dan+asd%27s+Test+Store&last_name=asd&item_name1=MAPCO+Kupplungssatz&receiver_email=pkwlkwteile%40yahoo.de&quantity1=1&pending_reason=unilateral&txn_type=cart&mc_gross_1=79.97&mc_currency=EUR&residence_country=US&test_ipn=1&transaction_subject=163&payment_gross=&ipn_track_id=ed7ff96f4f13e"; 
		$postdata .= "&cmd=_notify-validate"; 
		// $curl = curl_init("https://www.".$path.".com/cgi-bin/webscr"); 
		// curl_setopt ($curl, CURLOPT_HEADER, 0); 
		// curl_setopt ($curl, CURLOPT_POST, 1); 
		// curl_setopt ($curl, CURLOPT_POSTFIELDS, $postdata); 
		// curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false); 
		// curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1); 
		// curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, false); 
		// $response = curl_exec ($curl); 
		// $info = curl_getinfo($curl);
		// curl_close ($curl);  
// print_r($info);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://www.sandbox.paypal.com/cgi-bin/webscr');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.sandbox.paypal.com'));
		$response = curl_exec($ch);
		echo $response; 
		$info = curl_getinfo($ch);
		curl_close ($ch);  
		print_r($info);
		$arr = array();
		parse_str($postdata, $arr);
		
		if (isset($arr['custom'])) {
			$order_id = (int) $arr['custom'];
		} else {
			$order_id = 0;
		}
		$f = fopen(dirname(__file__)."/../payments/logs/paypal/".$order_id.".log", "w+") or die("Unable to open file!");
		fwrite($f, $postdata);
		fclose($f);
		
		if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0)) {
			
		} else {
			
		} */
		
		// $sandbox = SettingsmerchantsModel::get('paypal_mode');
		// if ($sandbox=='1') {
			// $path = "sandbox.paypal";
		// } else {
			// $path = "paypal";
		// }
		
		// $raw_post_data = file_get_contents('php://input');
		// $raw_post_array = explode('&', $raw_post_data);
		// $myPost = array();
		// foreach ($raw_post_array as $keyval) {
		  // $keyval = explode ('=', $keyval);
		  // if (count($keyval) == 2)
			 // $myPost[$keyval[0]] = urldecode($keyval[1]);
		// }
		// $req = 'cmd=_notify-validate';
		// if(function_exists('get_magic_quotes_gpc')) {
		   // $get_magic_quotes_exists = true;
		// } 
		// foreach ($myPost as $key => $value) {        
		   // if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
				// $value = urlencode(stripslashes($value)); 
		   // } else {
				// $value = urlencode($value);
		   // }
		   // $req .= "&$key=$value";
		// }
	/*	
		$postdata = "mc_gross=79.97&invoice=163+-+Dannik+Dan&protection_eligibility=Ineligible&item_number1=10001&payer_id=QZGH2MCW25SEA&tax=0.00&payment_date=14%3A28%3A57+Jan+27%2C+2016+PST&payment_status=Pending&charset=windows-1252&mc_shipping=0.00&mc_handling=0.00&first_name=Dan&notify_version=3.8&custom=163&payer_status=verified&num_cart_items=1&mc_handling1=0.00&verify_sign=A0asK6oiIZmtBFrRCq-Iiqf8lrk.AQaoCuVZ.u-z18gBMBnxEoHjGuf2&payer_email=danlapteacru%40gmail.com&mc_shipping1=0.00&tax1=0.00&txn_id=2XM33368906547727&payment_type=instant&payer_business_name=Dan+asd%27s+Test+Store&last_name=asd&item_name1=MAPCO+Kupplungssatz&receiver_email=pkwlkwteile%40yahoo.de&quantity1=1&pending_reason=unilateral&txn_type=cart&mc_gross_1=79.97&mc_currency=EUR&residence_country=US&test_ipn=1&transaction_subject=163&payment_gross=&ipn_track_id=ed7ff96f4f13e"; 
		$postdata .= "&cmd=_notify-validate"; 
		 
		 function myPOST($url, $postdata) {
			  $c = curl_init($url);
			  curl_setopt($c, CURLOPT_POST, true);
			  curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
			  curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
			  curl_setopt($c, CURLOPT_TIMEOUT, 15);
			  curl_setopt($c, CURLOPT_PORT, 443);
			  curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			  $output = curl_exec($c);
			  return $output;
			}
echo myPOST("https://www.sandbox.paypal.com/cgi-bin/webscr", $request);*/

		// $ch = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
		// curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		// curl_setopt($ch, CURLOPT_POST, 1);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		// curl_setopt($ch, CURLOPT_SSLVERSION , 3);
		// curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		// curl_setopt($ch, CURLOPT_CAINFO, '/home/admin/web/autoresurs.de/public_html/application/controllers/cacert.pem');
		// if( !($res = curl_exec($ch)) ) {
			// error_log("Got " . curl_error($ch) . " when processing IPN data");
			// error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL);
			// error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $postdata" . PHP_EOL);
			// error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL);
		
			// curl_close($ch);
			// exit;
		// }
		// curl_close($ch);
		
		/*$f = fopen(dirname(__file__)."/../payments/logs/paypal/Last-res.log", "w+") or die("Unable to open file!");
			fwrite($f, $res);
			fclose($f);
		
		if (strcmp ($res, "VERIFIED") == 0) {
			echo "VERIFIED";
			$f = fopen(dirname(__file__)."/../payments/logs/paypal/".$_POST['custom'].".log", "w+") or die("Unable to open file!");
			fwrite($f, "VERIFIED");
			fclose($f);
		} else if (strcmp ($res, "INVALID") == 0) {
			echo "INVALID";
			$f = fopen(dirname(__file__)."/../payments/logs/paypal/".$_POST['custom'].".log", "w+") or die("Unable to open file!");
			fwrite($f, "INVALID");
			fclose($f);
		}*/

		$postdata=""; 
		foreach ($_POST as $key=>$value) $postdata.=$key."=".urlencode($value)."&"; 
		$postdata .= "cmd=_notify-validate"; 
		
		// $postdata = "mc_gross=79.97&invoice=163+-+Dannik+Dan&protection_eligibility=Ineligible&item_number1=10001&payer_id=QZGH2MCW25SEA&tax=0.00&payment_date=14%3A28%3A57+Jan+27%2C+2016+PST&payment_status=Pending&charset=windows-1252&mc_shipping=0.00&mc_handling=0.00&first_name=Dan&notify_version=3.8&custom=163&payer_status=verified&num_cart_items=1&mc_handling1=0.00&verify_sign=A0asK6oiIZmtBFrRCq-Iiqf8lrk.AQaoCuVZ.u-z18gBMBnxEoHjGuf2&payer_email=danlapteacru%40gmail.com&mc_shipping1=0.00&tax1=0.00&txn_id=2XM33368906547727&payment_type=instant&payer_business_name=Dan+asd%27s+Test+Store&last_name=asd&item_name1=MAPCO+Kupplungssatz&receiver_email=pkwlkwteile%40yahoo.de&quantity1=1&pending_reason=unilateral&txn_type=cart&mc_gross_1=79.97&mc_currency=EUR&residence_country=US&test_ipn=1&transaction_subject=163&payment_gross=&ipn_track_id=ed7ff96f4f13e"; 
		// $postdata .= "&cmd=_notify-validate"; 
		$arr = array();
		parse_str($postdata, $arr);
		$order_id = $arr['custom'];
		$db = Register::get('db');
		$db->post("UPDATE ".DB_PREFIX."settings_merchants_result SET status='Операция прошла успешно', paid='1', check_dt='".time()."' WHERE merchant='PAYPAL' AND orderid_bill='".(int)$order_id."';");
		$db->post("UPDATE ".DB_PREFIX."cart_bills SET is_paid='1' WHERE id='".(int)$order_id."';");
							
		$f = fopen(dirname(__file__)."/../payments/logs/paypal/{$order_id}.log", "w+") or die("Unable to open file!");
		fwrite($f, $postdata);
		fclose($f);
		echo "VERIFIED";
				
	}
	
	public function sofort() {
		$this->layout = "ajax";
		$db = Register::get('db');
		// http://autoresurs.de/notify/sofort/?transaction=-TRANSACTION-&security_criteria=-SECURITY_CRITERIA-&order_id=-USER_VARIABLE_0-&pid=-PROJECT_ID-&inputhash=-USER_VARIABLE_0_MD5_PASS-&status=-STATUS-&amount=-AMOUNT-
		// http://autoresurs.de/notify/sofort/?transaction=-TRANSACTION-&security_criteria=-SECURITY_CRITERIA-&order_id=23&pid=-PROJECT_ID-&inputhash=-USER_VARIABLE_0_MD5_PASS-&status=-STATUS-&amount=-AMOUNT-
		// http://input24.de/index.php?route=payment/sofort/callback&transaction=-TRANSACTION-&security_criteria=-SECURITY_CRITERIA-&order_id=-USER_VARIABLE_0-&pid=-PROJECT_ID-&inputhash=-USER_VARIABLE_0_MD5_PASS-&status=-STATUS-&amount=-AMOUNT-
		
		$order_id = (int)$this->request("order_id",false);
		
		if (!isset($order_id)) {
			$order_id = 0;
		}
		
		$order_info = CartModel::getCartBillID($order_id);
		
		if ($order_info) {
			$response = $this->request("status",false);
			if ((strcmp($response, 'pending') == 0 || strcmp($response, 'received') == 0 ) && isset($response)) {
				switch($response) {
					case 'received':
						$project_id = SettingsmerchantsModel::get('sofort_project_id');
						$get_prd_id = $this->request("pid",false);
						$amount = $this->request("amount",false);
						$inputhash = $this->request("inputhash",false);
						$receiver_match = (strtolower($get_prd_id) == strtolower($project_id));
						$final_id = ((int)$inputhash == md5((int)$order_id));
						
						$itemsinfo = CartModel::getCartItemsScSID($bill);
						$sum = $itemsinfo['price'];
						
						$total_paid_match = ((float)$amount == $sum);
						
						if ($receiver_match && $total_paid_match && $final_id) {
							// $order_status_id = $this->config->get('sofort_completed_status_id');
							$db->post("UPDATE ".DB_PREFIX."settings_merchants_result SET status='Операция прошла успешно', paid='1', check_dt='".time()."' WHERE merchant='SOFORT' AND orderid_bill='".(int)$order_id."';");
							$db->post("UPDATE ".DB_PREFIX."cart_bills SET is_paid='1' WHERE id='".(int)$order_id."';");
							echo "succes";
						}
						
						if (!$receiver_match) {
							$db->post("UPDATE ".DB_PREFIX."settings_merchants_result SET status='SOFORT :: RECEIVER PROJECT ID MISMATCH! ".mysql_real_escape_string(strtolower($get_prd_id))."', check_dt='".time()."' WHERE  merchant='SOFORT' AND orderid_bill='".(int)$order_id."';");
							echo("Invalid PROJECT ID");
						}
						
						if (!$final_id) {
							$db->post("UPDATE ".DB_PREFIX."settings_merchants_result SET status='SOFORT :: ORDER ID MISMATCH! ".mysql_real_escape_string(strtolower($order_id))." - ".mysql_real_escape_string(strtolower($inputhash))."', check_dt='".time()."' WHERE  merchant='SOFORT' AND orderid_bill='".(int)$order_id."';");
							echo("Invalid ID");
						}
						
						if (!$total_paid_match) {
							$db->post("UPDATE ".DB_PREFIX."settings_merchants_result SET status='SOFORT :: TOTAL PAID MISMATCH! ".mysql_real_escape_string(strtolower($amount))."', check_dt='".time()."' WHERE  merchant='SOFORT' AND orderid_bill='".(int)$order_id."';");
							echo("Invalid amount");
							
						}
					break;
						
					case 'pending':
						$db->post("UPDATE ".DB_PREFIX."settings_merchants_result SET status='SOFORT :: Status pending!', check_dt='".time()."' WHERE  merchant='SOFORT' AND orderid_bill='".(int)$order_id."';");
						echo("status pending");
					break;
						
				}
			} else {
				$db->post("UPDATE  ".DB_PREFIX."settings_merchants_result SET status='Отказались от оплаты', check_dt='".time()."' WHERE  merchant='SOFORT' AND orderid_bill='".(int)$order_id."';");
			}
		}
		
		$var_str = var_export($_GET, true);
		
		file_put_contents('file.txt', $var_str);
		 
		// echo "sofort ".$order_id;
	}
	
}
?>