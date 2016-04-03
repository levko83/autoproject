<?php

error_reporting (0);

class MerchantsModel extends Orm {
	
	public static function HG($params=array()) {
		
		$incomeData = $params['incomeData'];
		$sum 		= $params['sum'];
		$cart 		= $params['cart'];
		$delivery	= $params['delivery_sum'];
		
		$user 		= $params['auth']['user'];
		$pwd 		= $params['auth']['pwd'];
		$eripId 	= $params['auth']['eripId'];
		
//		echo('<pre>');
//		var_dump($incomeData,$sum,$cart,$user,$pwd,$eripId);		
		
		$merchants = new MerchantsModel();
		
		try {
		
			$url = "https://www.hutkigrosh.by/API/v1/Security/LogIn";
			$req = new HTTPRequest($url);
			$dataXML = 
				"<Credentials xmlns=\"http://www.hutkigrosh.by/api\">".
					"<user>".$user."</user>".
					"<pwd>".$pwd."</pwd>".
				"</Credentials>";
			$res =& $merchants->sendToHG( $req, $url, $dataXML, null, HTTP_METH_POST );
			
			$cookies =& $req->getCookies();
			$url = "https://www.hutkigrosh.by/API/v1/Invoicing/Bill";
			$dataXML =
				"<Bill xmlns=\"http://www.hutkigrosh.by/api/invoicing\">".
					//"<billID />".
					"<eripId>".$eripId."</eripId>".
					"<invId>".$incomeData['order']."</invId>".
					"<dueDt>".(date("Y-m-d",strtotime("+3 days",$incomeData['date_mktime']))."T".date("H:i:s",strtotime("+3 days",$incomeData['date_mktime'])))."</dueDt>".
					"<addedDt>".(date("Y-m-d",strtotime($incomeData['date']))."T".date("H:i:s",strtotime($incomeData['date'])))."</addedDt>".
					#"<dueDt>".(date("Y-m-d",$incomeData['date'])."T".date("H:i:s",$incomeData['date']))."</dueDt>".
					#"<addedDt>".(date("Y-m-d",$incomeData['date'])."T".date("H:i:s",$incomeData['date']))."</addedDt>".
					//"<payedDt />".
					"<fullName>".$incomeData['name']."</fullName>".
					"<mobilePhone>".$incomeData['phone']."</mobilePhone>".
					"<notifyByMobilePhone>true</notifyByMobilePhone>".
					//"<email />".
					"<notifyByEMail>true</notifyByEMail>".
					"<fullAddress>".strip_tags($incomeData['address'])."</fullAddress>".
					"<amt>".$sum."</amt>".
					"<curr>BYR</curr>".
					"<statusEnum>NotSet</statusEnum>".
					//"<info />".
					"<products>";
						if (isset($cart) && count($cart)>0){
							foreach ($cart as $dd){
							$dataXML .= "<ProductInfo>".
							"<invItemId>".strip_tags($dd['name']." ".$dd['brand']." ".$dd['descr'])."</invItemId>".
							"<desc>товар</desc>".
							"<count>".$dd['cc']."</count>".
							"<amt>".($dd['cc']*$dd['price'])."</amt>".
							"</ProductInfo>";
							}
						}
					$dataXML .= "<ProductInfo>".
							"<invItemId>Доставка</invItemId>".
							"<desc>Доставка</desc>".
							"<count>1</count>".
							"<amt>".((int)$delivery)."</amt>".
							"</ProductInfo>";
					$dataXML .= "</products>".
				"</Bill>";
			
//			var_dump($dataXML);
						
			$BILL = $merchants->sendToHG( $req, $url, $dataXML, $cookies, HTTP_METH_POST );
			
//			var_dump($BILL);
//			exit();
						
			$url = "https://www.hutkigrosh.by/API/v1/Security/LogOut";
			$res = $merchants->sendToHG( $req, $url, "", $cookies, HTTP_METH_POST );
			
			return $BILL;
			
		} catch (HttpException $ex) {  
		    if (isset($ex->innerException)){  
		        echo $ex->innerException->getMessage();  
		        exit;  
		    } else {  
		        echo $ex;  
		        exit;  
		    }  
		} 
	}
	
	function sendToHG( $req, $url, $data, $cookies, $method ){
		$req->setUrl($url);
		$req->setHeaders(
			array("Content-Type" => "application/xml",
				"Content-Length" => strlen($data))
		);
		$req->setMethod( $method );
		$req->setRawPostData( $data );
		$req->enableCookies();
		if($cookies!=null)
			$req->setCookies($cookies);
		$req->send();
		return $req->getResponseBody();
	}
	
	/* ************************************************************************************ */
	/* ************************************************************************************ */
}
?>