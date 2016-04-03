<?php 

class Doc {

	public $values = array();
	public $debbug = false;
	
	function __construct(){
		$this->getValues();
	}
	
	function getValues(){
		$db = Register::get('db');
		$sql = "SELECT * FROM ".DB_PREFIX."documents_params;";
		$res = $db->query($sql);
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$this->values [$dd['code']]=$dd['value']; 
			}
		}
	}
	
	function getInfo($numberBill=false){

		$bill = BillsModel::fetchByIdBill($numberBill);
		$items = BillsModel::getHistory(array("scSID"=>$bill['scSID']));
		$account = AccountsModel::getById($bill['account_id']);

		return array("bill"=>$bill,"items"=>$items,"account"=>$account);
	}

	function bill($numberBill=false){

		$getInfo = $this->getInfo($numberBill);
		$bill = $getInfo['bill'];
		$items = $getInfo['items'];
		$accountD = $getInfo['account'];
		$platelshik = (($accountD['is_firm'] == 1)?($accountD['firm_name'].' '.$accountD['firm_inn'].' '.$accountD['firm_kpp'].', Банк '.$accountD['firm_bank'].', Р/С '.$accountD['firm_pc'].' '.$accountD['firm_kc'].' '.$accountD['firm_bnk'].' '.$accountD['firm_ogrn'].' '.$accountD['firm_okpo']):($bill['f1'].',  тел.: '.$bill['f2'].', e-mail: '.$bill['f3']));
		
		$i=$sum=$s1=0;
		if (isset($items) && count($items)>0){
			foreach ($items as $item){ $i++;
			$s1 = ($item['cc'] * $item['price']);
			$sum += $s1;
			}
		}

		if (!$this->debbug){
		header("Content-type: application/msword");
		header("Content-Disposition: attachment;Filename=doc№".$bill['number']."_Bill-".date("d.m.Y-H.i").".doc");
		}
		
		$html = '';
		$html .= '
		<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:st1="urn:schemas microsoft-com:office:smarttags" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<meta http-equiv=Content-Type content="text/html; charset=utf-8">
		<meta name=ProgId content=Word.Document>
		<meta name=Generator content="Microsoft Word 11">
		<meta name=Originator content="Microsoft Word 11">
		<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
		 name="metricconverter"/>
		<!--[if gte mso 9]><xml>
		 <w:WordDocument>
		  <w:View>Print</w:View>
		  <w:Zoom>105</w:Zoom>
		  <w:DoNotHyphenateCaps/>
		  <w:PunctuationKerning/>
		  <w:DrawingGridHorizontalSpacing>0 пт</w:DrawingGridHorizontalSpacing>
		  <w:DrawingGridVerticalSpacing>0 пт</w:DrawingGridVerticalSpacing>
		  <w:ValidateAgainstSchemas/>
		  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
		  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
		  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
		  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
		 </w:WordDocument>
		</xml><![endif]--><!--[if gte mso 9]><xml>
		 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
		 </w:LatentStyles>
		</xml><![endif]--><!--[if !mso]><object
		 classid="clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D" id=ieooui></object>
		<style>
		st1\:*{behavior:url(#ieooui) }
		</style>
		<![endif]-->
		<style>
		<!--
		 /* Font Definitions */
		
		 body {
		 	font-size: 80%
		 }
		
		 table {
		 	font-size: 100%
		 }
		
		 @font-face
			{font-family:Verdana;
			panose-1:2 11 6 4 3 5 4 4 2 4;
			mso-font-charset:204;
			mso-generic-font-family:swiss;
			mso-font-pitch:variable;
			mso-font-signature:536871559 0 0 0 415 0;}
		 /* Style Definitions */
		 p.MsoNormal, li.MsoNormal, div.MsoNormal
			{mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:7.5pt;
			mso-bidi-font-size:8.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		p.small, li.small, div.small
			{mso-style-name:small;
			mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:1.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		@page Section1
			{size:595.3pt 841.9pt;
			margin: 2.0cm 2.0cm 42.55pt 2.0cm;
			mso-header-margin:35.45pt;
			mso-footer-margin:35.45pt;
			mso-paper-source:0;}
		div.Section1
			{page:Section1;}
		-->
		</style>
		<!--[if gte mso 10]>
		<style>
		 /* Style Definitions */
		 table.MsoNormalTable
			{mso-style-name:"Обычная таблица";
			mso-tstyle-rowband-size:0;
			mso-tstyle-colband-size:0;
			mso-style-noshow:yes;
			mso-style-parent:"";
			mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
			mso-para-margin:0cm;
			mso-para-margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:10.0pt;
			font-family:"Times New Roman";
			mso-ansi-language:#0400;
			mso-fareast-language:#0400;
			mso-bidi-language:#0400;}
		
			nostyle 	{mso-style-parent:style0;
			text-align:right;
			border:.5pt solid black;
			white-space:normal;}
		
		</style>
		<![endif]--><!--[if gte mso 9]><xml>
		 <o:shapedefaults v:ext="edit" spidmax="3074">
		  <o:colormenu v:ext="edit" strokecolor="none"/>
		 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
		 <o:shapelayout v:ext="edit">
		  <o:idmap v:ext="edit" data="1"/>
		 </o:shapelayout></xml><![endif]-->
		</head>

		<body lang=RU style=\'tab-interval:35.4pt\'>
		<div class=Section1>

		<table width="100%" align=center border=0>
		<tr>
			<td>
				<p>
					<strong>'.$this->values['bill.contacts'].'</strong>
				</p>

				<table border="1" cellpadding="5" cellspacing="0" bordercolor="#000000" width="100%">
				<tr>
					<td width="200px">
						Получатель<br>ИНН
					</td>
					<td>'.$this->values['bill.inn'].'</td>
					<td width="200px">Сч. №<br>КПП:</td>
					<td>'.$this->values['bill.sch.kpp'].'</td>
				</tr>
				<tr>
					<td>Банк получателя<br></td>
					<td>'.$this->values['bill.bank.poluch'].'</td>
					<td>БИК<br>Сч. № </td>
					<td>'.$this->values['bill.bank.bik'].'</td>
				</tr>
				</table>

				<h3 align="center">СЧЕТ №'.$bill['number'].' от '.date("d.m.Y",$bill['dt']).'</h3>
				<p><strong>Плательщик:</strong> '.$platelshik.'</p>

				<table border="1" cellpadding="5" cellspacing="0" bordercolor="#000000" width="100%">
				<tr>
					<th>№</th>
					<th>Наименование<br/>номенклатуры</th>
					<th>Единица<br/>измерения</th>
					<th>Коли-<br/>чество</th>
					<th>Цена, р.</th>
					<th>Сумма, р.</th>
				</tr>';

		if (isset($items) && count($items)>0){
			$i=$sum=$s1=0; foreach ($items as $item){ $i++;

			$s1 = ($item['cc'] * $item['price']);
			$sum += $s1;
				
			$html .= '<tr>
						<td>'.$i.'</td>
						<td>'.$item['article'].' '.$item['brand'].' '.$item['descr_tecdoc'].'</td>
						<td align="center">шт.</td>
						<td align="center">'.$item['cc'].'</td>
						<td align="right">'.PriceHelper::numberDoc($item['price']).'</td>
						<td align="right">'.PriceHelper::numberDoc($s1).'</td>
					</tr>';
			}
		}
			
		$html .= '<tr>
					<td colspan="5" align="right">
						<strong>Всего к оплате БЕЗ НДС:</strong>
					</td>
					<td align="right">'.PriceHelper::numberDoc($sum).'</td>
				</tr>
				</table>

				<br/><br/>

				<table border="0" style="font-size: 80%" align="center">
				<tr>
					<td valign="top" align="center">
					Руководитель организации _________________
					<br/><br/><br/><br/>
					/  /
					</td>
					<td width="20"></td>
					<td valign="top" align="center">
					Главный бухгалтер _____________________
					<br/><br/><br/><br/>
					/  /
				</td>
				</tr>
				</table>

				<table border="0" align="center">
				<tr>
					<td valign="top">
					М.П.
					</td>
				</tr>
				</table>

			</td>
		</tr>
		</table>

		</div>
		</body>
		</html>
		';
		echo($html);
		exit();
	}

	function AnnouncementBill($numberBill=false){

		$getInfo = $this->getInfo($numberBill);
		$bill = $getInfo['bill'];
		$items = $getInfo['items'];
		$accountD = $getInfo['account'];
		$platelshik = (($accountD['is_firm'] == 1)?($accountD['firm_name'].' '.$accountD['firm_inn'].' '.$accountD['firm_kpp'].', Банк '.$accountD['firm_bank'].', Р/С '.$accountD['firm_pc'].' '.$accountD['firm_kc'].' '.$accountD['firm_bnk'].' '.$accountD['firm_ogrn'].' '.$accountD['firm_okpo']):($bill['f1'].',  тел.: '.$bill['f2'].', e-mail: '.$bill['f3']));

		$i=$sum=$s1=0;
		if (isset($items) && count($items)>0){
			foreach ($items as $item){ $i++;
			$s1 = ($item['cc'] * $item['price']);
			$sum += $s1;
			}
		}

		if (!$this->debbug){
		header("Content-type: application/msword");
		header("Content-Disposition: attachment;Filename=doc№".$bill['number']."_AnnouncementBill-".date("d.m.Y-H.i").".doc");
		}
		
		$html = '';
		$html .= '
		<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:st1="urn:schemas-microsoft-com:office:smarttags" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<meta http-equiv=Content-Type content="text/html; charset=utf-8">
		<meta name=ProgId content=Word.Document>
		<meta name=Generator content="Microsoft Word 11">
		<meta name=Originator content="Microsoft Word 11">
		<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
		 name="metricconverter"/>
		<!--[if gte mso 9]><xml>
		 <w:WordDocument>
		  <w:View>Print</w:View>
		  <w:Zoom>105</w:Zoom>
		  <w:DoNotHyphenateCaps/>
		  <w:PunctuationKerning/>
		  <w:DrawingGridHorizontalSpacing>0 пт</w:DrawingGridHorizontalSpacing>
		  <w:DrawingGridVerticalSpacing>0 пт</w:DrawingGridVerticalSpacing>
		  <w:ValidateAgainstSchemas/>
		  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
		  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
		  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
		  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
		 </w:WordDocument>
		</xml><![endif]--><!--[if gte mso 9]><xml>
		 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
		 </w:LatentStyles>
		</xml><![endif]--><!--[if !mso]><object
		 classid="clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D" id=ieooui></object>
		<style>
		st1\:*{behavior:url(#ieooui) }
		</style>
		<![endif]-->
		<style>
		<!--
		 /* Font Definitions */
		 body {
		 	font-size: 80%
		 }
		 table {
		 	font-size: 100%
		 }
		 @font-face
			{font-family:Verdana;
			panose-1:2 11 6 4 3 5 4 4 2 4;
			mso-font-charset:204;
			mso-generic-font-family:swiss;
			mso-font-pitch:variable;
			mso-font-signature:536871559 0 0 0 415 0;}
		 /* Style Definitions */
		 p.MsoNormal, li.MsoNormal, div.MsoNormal
			{mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:7.5pt;
			mso-bidi-font-size:8.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		p.small, li.small, div.small
			{mso-style-name:small;
			mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:1.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		@page Section1
			{size:595.3pt 841.9pt;
			margin: 2.0cm 2.0cm 42.55pt 2.0cm;
			mso-header-margin:35.45pt;
			mso-footer-margin:35.45pt;
			mso-paper-source:0;}
		div.Section1
			{page:Section1;}
		-->
		</style>
		<!--[if gte mso 10]>
		<style>
		 /* Style Definitions */
		 table.MsoNormalTable
			{mso-style-name:"Обычная таблица";
			mso-tstyle-rowband-size:0;
			mso-tstyle-colband-size:0;
			mso-style-noshow:yes;
			mso-style-parent:"";
			mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
			mso-para-margin:0cm;
			mso-para-margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:10.0pt;
			font-family:"Times New Roman";
			mso-ansi-language:#0400;
			mso-fareast-language:#0400;
			mso-bidi-language:#0400;}
		
			nostyle 	{mso-style-parent:style0;
			text-align:right;
			border:.5pt solid black;
			white-space:normal;}
		
		</style>
		<![endif]--><!--[if gte mso 9]><xml>
		 <o:shapedefaults v:ext="edit" spidmax="3074">
		  <o:colormenu v:ext="edit" strokecolor="none"/>
		 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
		 <o:shapelayout v:ext="edit">
		  <o:idmap v:ext="edit" data="1"/>
		 </o:shapelayout></xml><![endif]-->
		</head>

		<body lang=RU style=\'tab-interval:35.4pt\'>

		<div class=Section1>
		<style type="text/css">
		@media print {
			.noprint { display: none; }
			.normal { display: none; }
		}
		a { text-decoration: none; color:blue; }
		td { font-family: Verdana, Arial; font-size: 10px; }
		.normal { font-family: Verdana, Arial; font-size: 10px; }
		.small { font-family: Verdana, Arial; font-size: 10px; }
		.big { font-family: Verdana, Arial; font-size: 12px; }
		.verybig { font-family: Verdana, Arial; font-size: 14px; }

		table.real { border-right: solid 1px black;	border-bottom: solid 1px black;	}
		td.real { border-top: solid 1px black; border-left: solid 1px black; padding: 5px; font-size: 12px; }

		table.sreal { border-right: solid 1px black;	border-bottom: solid 1px black;	}
		td.sreal { border-top: solid 1px black; border-left: solid 1px black; padding: 1px 2px 1px 2px; }
		</style>

		<table border="0" cellpadding="6" cellspacing="0" width="560" align="center" style="border: dotted 1px black; page-break-before:always;">
		<tr>
			<td align="center" width="200" valign="top" style="border-right: solid 1px black; border-bottom: solid 1px black;">
			<br>
				<span class="big"><b>ИЗВЕЩЕНИЕ</b></span>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				Кассир
			</td>
			<td valign="top" style="border-bottom: solid 1px black;">

				<table border="0" cellpadding="1" cellspacing="0">
				<tr><td style="border-bottom: solid 1px black;">'.$this->values['AnnouncementBill.name.bank'].'</td></tr>
				<tr><td align="center" class="small">получатель платежа</td></tr>
				<tr><td style="border-bottom: solid 1px black;">'.$this->values['AnnouncementBill.uch.bank'].'</td></tr>
				<tr><td align="center" class="small">учреждение банка</td></tr>
				<tr>
					<td>
					<br>

					<table border="0" cellpadding="0" cellspacing="0" class="sreal">
					<tr>
						<td class="sreal">Расчетный<br>счет №</td>
						<td class="sreal">'.$this->values['AnnouncementBill.raschet'].'</td>
						<td class="sreal" align="center">БИК</td>
						<td class="sreal">'.$this->values['AnnouncementBill.bik'].'</td>
					</tr>
					<tr><td colspan="4" class="sreal" align="center">Кор. счет: '.$this->values['AnnouncementBill.kopp.bill'].'</td></tr>
					<tr>
						<td colspan="4" class="sreal" align="center">
							<div style="border-bottom: black 1px solid; width: 90%">'.$platelshik.'</div>
							<div class="small" style="width: 90%; padding-bottom:4px;">фамилия, и. о., адрес</div>
							<div style="border-bottom: black 1px solid; width: 90%">&nbsp;</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="sreal" align="center">Вид платежа</td>
						<td class="sreal" align="center">Дата</td>
						<td class="sreal" align="center">Сумма</td>
					</tr>
					<tr>
						<td colspan="2" class="sreal" align="center">
							оплата за заказ №'.$bill['number'].'от '.date("d.m.Y",$bill['dt']).'
						</td>
						<td class="sreal" align="center">&nbsp;</td>
						<td class="sreal" align="center">'.PriceHelper::numberDoc($sum).'</td>
					</tr>
					<tr>
						<td class="sreal" align="center">Номер:</td>
						<td class="sreal" colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td class="sreal" colspan="2" rowspan="2">Плательщик:</td>
						<td class="sreal" align="center">Пеня:</td>
						<td class="sreal">&nbsp;</td>
					</tr>
					<tr>
						<td class="sreal" align="center">Всего:</td>
						<td class="sreal">'.PriceHelper::numberDoc($sum).'</td>
					</tr>
					</table>

					</td>
				</tr>
				</table>

			</td>
		</tr>
		<tr>
			<td align="center" width="200" valign="top" style="border-right: solid 1px black; border-bottom: solid 1px black;">
				<br>
				<span class="big"><b>ИЗВЕЩЕНИЕ</b></span>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				Кассир
			</td>
			<td valign="top" style="border-bottom: solid 1px black;">

				<table border="0" cellpadding="1" cellspacing="0">
				<tr><td style="border-bottom: solid 1px black;">'.$this->values['AnnouncementBill.name.bank'].'</td></tr>
				<tr><td align="center" class="small">получатель платежа</td></tr>
				<tr><td style="border-bottom: solid 1px black;">'.$this->values['AnnouncementBill.uch.bank'].'</td></tr>
				<tr><td align="center" class="small">учреждение банка</td></tr>
				<tr>
					<td>
					<br>

					<table border="0" cellpadding="0" cellspacing="0" class="sreal">
					<tr>
						<td class="sreal">Расчетный<br>счет №</td>
						<td class="sreal">'.$this->values['AnnouncementBill.raschet'].'</td>
						<td class="sreal" align="center">БИК</td>
						<td class="sreal">'.$this->values['AnnouncementBill.bik'].'</td>
					</tr>
					<tr><td colspan="4" class="sreal" align="center">Кор. счет: '.$this->values['AnnouncementBill.kopp.bill'].'</td></tr>
					<tr>
						<td colspan="4" class="sreal" align="center">
							<div style="border-bottom: black 1px solid; width: 90%">'.$platelshik.'</div>
							<div class="small" style="width: 90%; padding-bottom:4px;">фамилия, и. о., адрес</div>
							<div style="border-bottom: black 1px solid; width: 90%">&nbsp;</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="sreal" align="center">Вид платежа</td>
						<td class="sreal" align="center">Дата</td>
						<td class="sreal" align="center">Сумма</td>
					</tr>
					<tr>
						<td colspan="2" class="sreal" align="center">
							оплата за заказ №'.$bill['number'].'от '.date("d.m.Y",$bill['dt']).'
						</td>
						<td class="sreal" align="center">&nbsp;</td>
						<td class="sreal" align="center">'.PriceHelper::numberDoc($sum).'</td>
					</tr>
					<tr>
						<td class="sreal" align="center">Номер:</td>
						<td class="sreal" colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td class="sreal" colspan="2" rowspan="2">Плательщик:</td>
						<td class="sreal" align="center">Пеня:</td>
						<td class="sreal">&nbsp;</td>
					</tr>
					<tr>
						<td class="sreal" align="center">Всего:</td>
						<td class="sreal">'.PriceHelper::numberDoc($sum).'</td>
					</tr>
					</table>

					</td>
				</tr>
				</table>

			</td>
		</tr>
		</table>

		</div>
		</body>
		</html>
		';
		echo($html);
		exit();
	}

	function AnnexBill($numberBill=false){

		$getInfo = $this->getInfo($numberBill);
		$bill = $getInfo['bill'];
		$items = $getInfo['items'];
		$accountD = $getInfo['account'];
		$platelshik = (($accountD['is_firm'] == 1)?($accountD['firm_name'].' '.$accountD['firm_inn'].' '.$accountD['firm_kpp'].', Банк '.$accountD['firm_bank'].', Р/С '.$accountD['firm_pc'].' '.$accountD['firm_kc'].' '.$accountD['firm_bnk'].' '.$accountD['firm_ogrn'].' '.$accountD['firm_okpo']):($bill['f1'].',  тел.: '.$bill['f2'].', e-mail: '.$bill['f3']));
		
		$user = UsersModel::getById($bill['manager_id']);

		if (!$this->debbug){
		header("Content-type: application/msword");
		header("Content-Disposition: attachment;Filename=doc№".$bill['number']."_AnnexBill-".date("d.m.Y-H.i").".doc");
		}
		
		$html = '';
		$html .= '
		<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:st1="urn:schemas-microsoft-com:office:smarttags" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<meta http-equiv=Content-Type content="text/html; charset=utf-8">
		<meta name=ProgId content=Word.Document>
		<meta name=Generator content="Microsoft Word 11">
		<meta name=Originator content="Microsoft Word 11">
		<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
		 name="metricconverter"/>
		<!--[if gte mso 9]><xml>
		 <w:WordDocument>
		  <w:View>Print</w:View>
		  <w:Zoom>105</w:Zoom>
		  <w:DoNotHyphenateCaps/>
		  <w:PunctuationKerning/>
		  <w:DrawingGridHorizontalSpacing>0 пт</w:DrawingGridHorizontalSpacing>
		  <w:DrawingGridVerticalSpacing>0 пт</w:DrawingGridVerticalSpacing>
		  <w:ValidateAgainstSchemas/>
		  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
		  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
		  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
		  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
		 </w:WordDocument>
		</xml><![endif]--><!--[if gte mso 9]><xml>
		 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
		 </w:LatentStyles>
		</xml><![endif]--><!--[if !mso]><object classid="clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D" id=ieooui></object>
		<style>
		st1\:*{behavior:url(#ieooui) }
		</style>
		<![endif]-->
		<style>
		<!--
		 /* Font Definitions */
		 body {
		 	font-size: 80%
		 }
		 table {
		 	font-size: 100%
		 }
		 @font-face
			{font-family:Verdana;
			panose-1:2 11 6 4 3 5 4 4 2 4;
			mso-font-charset:204;
			mso-generic-font-family:swiss;
			mso-font-pitch:variable;
			mso-font-signature:536871559 0 0 0 415 0;}
		 /* Style Definitions */
		 p.MsoNormal, li.MsoNormal, div.MsoNormal
			{mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:7.5pt;
			mso-bidi-font-size:8.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		p.small, li.small, div.small
			{mso-style-name:small;
			mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:1.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		@page Section1
			{size:595.3pt 841.9pt;
			margin: 2.0cm 2.0cm 42.55pt 2.0cm;
			mso-header-margin:35.45pt;
			mso-footer-margin:35.45pt;
			mso-paper-source:0;}
		div.Section1
			{page:Section1;}
		-->
		</style>
		<!--[if gte mso 10]>
		<style>
		 /* Style Definitions */
		 table.MsoNormalTable
			{mso-style-name:"Обычная таблица";
			mso-tstyle-rowband-size:0;
			mso-tstyle-colband-size:0;
			mso-style-noshow:yes;
			mso-style-parent:"";
			mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
			mso-para-margin:0cm;
			mso-para-margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:10.0pt;
			font-family:"Times New Roman";
			mso-ansi-language:#0400;
			mso-fareast-language:#0400;
			mso-bidi-language:#0400;}
			nostyle 	{mso-style-parent:style0;
			text-align:right;
			border:.5pt solid black;
			white-space:normal;}
		</style>
		<![endif]--><!--[if gte mso 9]><xml>
		 <o:shapedefaults v:ext="edit" spidmax="3074">
		  <o:colormenu v:ext="edit" strokecolor="none"/>
		 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
		 <o:shapelayout v:ext="edit">
		  <o:idmap v:ext="edit" data="1"/>
		 </o:shapelayout></xml><![endif]-->
		<style>
		* { font-family:arial; font-size:12px; }
		h1 { font-size:16px; }
		.STB td,.STB th { border:solid 2px #000; }
		.td { padding:8px 0px; }
		</style>
		</head>

		<body lang=RU style=\'tab-interval:35.4pt\'>
		<div class=Section1>


		<table width="800px" align="center" class="MsoNormalTable">
		<tr>
			<td></td>
			<td class="td" valign="top" align="right">
			Приложение №1<br>
			к договору купли-продажи №'.$bill['number'].'<br>
			от '.date("d.m.Y",$bill['dt']).'
			</td>
		</tr>
		<tr>
			<td class="td" align="center" colspan="2"><h1>Заказ №'.$bill['number'].'</h1></td>
		</tr>
		<tr>
			<td>'.$this->values['AnnexBill.name.firm'].'</td>
			<td class="td" valign="top" align="right">Ваш менеджер<br>'.$user['name'].'</td>
		</tr>
		<tr>
			<td class="td">'.$this->values['AnnexBill.contacts'].'</td>
			<td class="td"></td>
		</tr>
		<tr>
			<td class="td" colspan="2">
				<hr style="border:solid 2px #000;">
			</td>
		</tr>
		<tr>
			<td class="td" colspan="2"><h1>Перечень заказанных запчастей</h1></td>
		</tr>
		<tr>
			<td class="td" colspan="2">
		
				<table border="1" cellpadding="5" cellspacing="0" bordercolor="#000000" width="100%">
				<tr>
					<th>№</th>
					<th>№ заказа</th>
					<th>Артикул</th>
					<th>Производитель</th>

					<th>Наименование</th>
					<th>Единица измерения</th>
					<th>Количество</th>
		
					<th>Цена</th>
					<th>Срок, дней</th>
					<th>Сумма</th>
				</tr>';

		if (isset($items) && count($items)>0){
			$i=$sum=$s1=$payed=0; foreach ($items as $item){ $i++;

			$s1 = ($item['cc'] * $item['price']);
			$sum += $s1;
				
			if ($item['balance_minus'])
				$payed += $s1;

			$html .= '<tr>
						<td>'.$i.'&nbsp;</td>
						<td>'.$item['bill_number'].'&nbsp;</td>
						<td>'.$item['article'].'&nbsp;</td>

						<td>'.$item['brand'].'&nbsp;</td>
						<td>'.$item['descr_tecdoc'].'&nbsp;</td>
						<td>шт.&nbsp;</td>

						<td>'.$item['cc'].'&nbsp;</td>
						<td>'.PriceHelper::numberDoc($item['price']).'&nbsp;</td>
						<td>'.$item['time_delivery_descr'].'&nbsp;</td>
						<td>'.PriceHelper::numberDoc($s1).'&nbsp;</td>
					</tr>';
			}
		}

		$html .= '
				<tr>
					<th colspan="9" align="right">Доставка:</th>
					<td>'.PriceHelper::numberDoc($bill['delivery_price']).'</td>
				</tr>
				<tr>
					<th colspan="9" align="right">Всего к оплате:</th>
					<td>'.PriceHelper::numberDoc($sum+$bill['delivery_price']).'&nbsp;</td>
				</tr>
				<tr>
					<td colspan="10">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="5">Предоплата: '.PriceHelper::numberDoc($bill['prepayment']).'</td>
					<td colspan="5">Долг по оплате: '.PriceHelper::numberDoc($sum-$bill['prepayment']).'</td>
				</tr>
				</table>
		
			</td>
		</tr>
		<tr>
			<td class="td" colspan="2">
				Способ оплаты: '.$bill['payment_name'].'<br>
				Доставка: '.$bill['delivery'].'
			</td>
		</tr>
		<tr>
			<td class="td" colspan="2" style="font-size:10px;">
				<center><h1>Выписка из договора по основным условиям поставки:</h1></center>
				<ul>
					<li>ДАННАЯ ПОЗИЦИЯ ЯВЛЯЕТСЯ ЗАКАЗНОЙ И НЕ ПОДЛЕЖИТ ВОЗВРАТУ</li>
					<li>Общие условия поставки автозапчастей под индивидуальный заказ.</li>
					<li>1. Частное предприятие предоставляет Покупателям - юридическим лицам и индивидуальным предпринимателям, в рамках ранее заключенных и действующих между сторонами договоров поставки товара, возможность приобретения автозапчастей под индивидуальный заказ.</li>
					<li>2. Выбор наименования (производителя), количества товара в заказе осуществляется Покупателем самостоятельно, в электронной базе Поставщика. Иные формы заказа оговариваются дополнительно посредством телефонной либо электронной связи. Ответственность за количество и применимость автозапчастей к конкретному автомобилю возлагается на Покупателя. Любые корректировки заказа допускаются в срок до его отправки в работу (т.е. до изменения статуса заказа в кабинете пользователя на «В работе»).</li>
					<li>3. Срок выполнения заказа составляет от 1 до 60 рабочих дней, в зависимости от наличия товара на складе Поставщика/Производителя. В случае увеличения указанного срока с Покупателем согласовывается иной срок исполнения заказа. При отказе в согласовании нового срока, сумма предоплаты возвращается Покупателю в течение 7 рабочих дней либо зачитывается в счет поставок товаров не под индивидуальный заказ.</li>
					<li>4. При оформлении заказа объявленная цена поставки товаров под заказ является ориентировочной и уточняется, в случае изменения стоимостных показателей к моменту доставки товара.</li>
					<li>5. При отказе Покупателя от получения товаров по исполненному в согласованный срок заказу, за исключением наличия претензий к качеству товара, возврат уплаченных денежных средств производится Поставщиком только после реализации доставленных под заказ товаров третьим лицам, с удержанием с Покупателя документально подтвержденных расходов, понесенных Поставщиком в связи с выполнением обязательств по исполнению заказа.</li>
					<li>6. Размещая заказ в электронной базе Поставщика, Покупатель соглашается со всеми условиями, перечисленными в настоящем разделе без каких либо оговорок. Все иные условия поставки товаров под заказ, которые не установлены настоящим разделом, предусмотрены основным договором поставки, заключенным между сторонами.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<td class="td">
				Данные верны, с условиями согласен:<br/>
				----------------------------------------------------<br/>
				Тел.: -------------------------<br/>
				(подпись покупателя)________________ 
			</td>
			<td class="td" align="right">
				Менеджер:<br/>
				'.$user['name'].'<br/><br/>
				(подпись менеджера)________________
			</td>
		</tr>
		</table>
			
		</div>
		</body>
		</html>
		';
		echo($html);
		exit();
	}

	function AcceptanceCertificateBill($numberBill=false){

		$getInfo = $this->getInfo($numberBill);
		$bill = $getInfo['bill'];
		$items = $getInfo['items'];
		$accountD = $getInfo['account'];
		$platelshik = (($accountD['is_firm'] == 1)?($accountD['firm_name'].' '.$accountD['firm_inn'].' '.$accountD['firm_kpp'].', Банк '.$accountD['firm_bank'].', Р/С '.$accountD['firm_pc'].' '.$accountD['firm_kc'].' '.$accountD['firm_bnk'].' '.$accountD['firm_ogrn'].' '.$accountD['firm_okpo']):($bill['f1'].',  тел.: '.$bill['f2'].', e-mail: '.$bill['f3']));

		if (!$this->debbug){
		header("Content-type: application/msword");
		header("Content-Disposition: attachment;Filename=doc№".$bill['number']."_AcceptanceCertificateBill-".date("d.m.Y-H.i").".doc");
		}
		
		$logo = SettingsModel::get('logo');
		$logoInfo = getimagesize(HTTP_ROOT.'/media/files/settings/'.$logo);
		$html = '';
		$html .= '
		<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:st1="urn:schemas-microsoft-com:office:smarttags" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<meta http-equiv=Content-Type content="text/html; charset=utf-8">
		<meta name=ProgId content=Word.Document>
		<meta name=Generator content="Microsoft Word 11">
		<meta name=Originator content="Microsoft Word 11">
		<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
		 name="metricconverter"/>
		<!--[if gte mso 9]><xml>
		 <w:WordDocument>
		  <w:View>Print</w:View>
		  <w:Zoom>105</w:Zoom>
		  <w:DoNotHyphenateCaps/>
		  <w:PunctuationKerning/>
		  <w:DrawingGridHorizontalSpacing>0 пт</w:DrawingGridHorizontalSpacing>
		  <w:DrawingGridVerticalSpacing>0 пт</w:DrawingGridVerticalSpacing>
		  <w:ValidateAgainstSchemas/>
		  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
		  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
		  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
		  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
		 </w:WordDocument>
		</xml><![endif]--><!--[if gte mso 9]><xml>
		 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
		 </w:LatentStyles>
		</xml><![endif]--><!--[if !mso]><object classid="clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D" id=ieooui></object>
		<style>
		st1\:*{behavior:url(#ieooui) }
		</style>
		<![endif]-->
		<style>
		<!--
		 /* Font Definitions */
		 body {
		 	font-size: 80%
		 }
		 table {
		 	font-size: 100%
		 }
		 @font-face
			{font-family:Verdana;
			panose-1:2 11 6 4 3 5 4 4 2 4;
			mso-font-charset:204;
			mso-generic-font-family:swiss;
			mso-font-pitch:variable;
			mso-font-signature:536871559 0 0 0 415 0;}
		 /* Style Definitions */
		 p.MsoNormal, li.MsoNormal, div.MsoNormal
			{mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:7.5pt;
			mso-bidi-font-size:8.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		p.small, li.small, div.small
			{mso-style-name:small;
			mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:1.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		@page Section1
			{size:595.3pt 841.9pt;
			margin: 2.0cm 2.0cm 42.55pt 2.0cm;
			mso-header-margin:35.45pt;
			mso-footer-margin:35.45pt;
			mso-paper-source:0;}
		div.Section1
			{page:Section1;}
		-->
		</style>
		<!--[if gte mso 10]>
		<style>
		 /* Style Definitions */
		 table.MsoNormalTable
			{mso-style-name:"Обычная таблица";
			mso-tstyle-rowband-size:0;
			mso-tstyle-colband-size:0;
			mso-style-noshow:yes;
			mso-style-parent:"";
			mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
			mso-para-margin:0cm;
			mso-para-margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:10.0pt;
			font-family:"Times New Roman";
			mso-ansi-language:#0400;
			mso-fareast-language:#0400;
			mso-bidi-language:#0400;}
			nostyle 	{mso-style-parent:style0;
			text-align:right;
			border:.5pt solid black;
			white-space:normal;}
		</style>
		<![endif]--><!--[if gte mso 9]><xml>
		 <o:shapedefaults v:ext="edit" spidmax="3074">
		  <o:colormenu v:ext="edit" strokecolor="none"/>
		 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
		 <o:shapelayout v:ext="edit">
		  <o:idmap v:ext="edit" data="1"/>
		 </o:shapelayout></xml><![endif]-->
		<style>
		* { font-family:arial; font-size:12px; }
		h1 { font-size:16px; }
		.STB td,.STB th { border:solid 2px #000; }
		.td { padding:8px 0px; }
		</style>
		</head>

		<body lang=RU style=\'tab-interval:35.4pt\'>
		<div class=Section1>


		<table width="800px" align="center" class="MsoNormalTable">
		<tr>
			<td>'.$this->values['AcceptanceCertificateBill.name.firm'].'</td>
			<td class="td" valign="top" align="right"><img src="'.HTTP_ROOT.'/media/files/settings/'.$logo.'" alt=""/></td>
		</tr>
		<tr>
			<td class="td">'.$this->values['AcceptanceCertificateBill.contacts'].'</td>
			<td class="td"></td>
		</tr>
		<tr>
			<td class="td" colspan="2">Грузополучатель: '.$platelshik.'</td>
		</tr>
		<tr>
			<td class="td" align="center" colspan="2"><h1>Акт приема-передачи №'.$bill['number'].' от '.date("d.m.Y",$bill['dt']).' года</h1></td>
		</tr>
		<tr>
			<td class="td" colspan="2">
			
				<table border="1" cellpadding="5" cellspacing="0" bordercolor="#000000" width="100%">
				<tr>
					<th>№</th>
					<th>№ заказа</th>
					<th>Артикул</th>
					<th>Наименование</th>
			
					<th>Производитель</th>
					<th>Единица измерения</th>
					<th>Количество</th>
			
					<th width="100px">Цена</th>
					<th width="100px">Сумма</th>
				</tr>';

		if (isset($items) && count($items)>0){
			$i=$sum=$s1=0; foreach ($items as $item){ $i++;

			$s1 = ($item['cc'] * $item['price']);
			$sum += $s1;

			$html .= '<tr>
						<td>'.$i.'&nbsp;</td>
						<td>'.$item['bill_number'].'&nbsp;</td>
						<td>'.$item['article'].'&nbsp;</td>

						<td>'.$item['descr_tecdoc'].'&nbsp;</td>
						<td>'.$item['brand'].'&nbsp;</td>
						<td>шт.&nbsp;</td>

						<td>'.$bill['cc'].'&nbsp;</td>
						<td>'.PriceHelper::numberDoc($item['price']).'&nbsp;</td>
						<td>'.PriceHelper::numberDoc($s1).'&nbsp;</td>
					</tr>';
			}
		}

		$html .= '<tr>
					<th colspan="8" align="right">Итого сумма:</th>
					<td>'.PriceHelper::numberDoc($sum).'&nbsp;</td>
				</tr>
				<tr>
					<th colspan="8" align="right">Итого доплата:</th>
					<td>&nbsp;</td>
				</tr>
				</table>
			
			</td>
		</tr>
		<tr>
			<td class="td" colspan="2">
				Всего наименований в шт. '.count($items).' на сумму: '.Num2strViewHelper::num2str($sum).'
			</td>
		</tr>
		<tr>
			<td class="td" colspan="2" align="center">
				<table border="1" cellpadding="5" cellspacing="0" bordercolor="#000000" width="500px">
				<tr><td align="center">
					Данные позиции согласно спецификации принял, внешний осмотр произвел,<br>претензий к внешнему виду и состоянию не имею.
				</td></tr></table>
			</td>
		</tr>
		<tr>
			<td class="td">Отпустил:______________________________</td>
			<td class="td" align="right">Принял:______________________________</td>
		</tr>
		</table>
			
		</div>
		</body>
		</html>
		';
		echo($html);
		exit();
	}

	function OrderBill($numberBill=false){

		$getInfo = $this->getInfo($numberBill);
		$bill = $getInfo['bill'];
		$items = $getInfo['items'];
		$accountD = $getInfo['account'];
		$platelshik = (($accountD['is_firm'] == 1)?($accountD['firm_name'].' '.$accountD['firm_inn'].' '.$accountD['firm_kpp'].', Банк '.$accountD['firm_bank'].', Р/С '.$accountD['firm_pc'].' '.$accountD['firm_kc'].' '.$accountD['firm_bnk'].' '.$accountD['firm_ogrn'].' '.$accountD['firm_okpo']):($bill['f1'].',  тел.: '.$bill['f2'].', e-mail: '.$bill['f3']));

		if (!$this->debbug){
		header("Content-type: application/msword");
		header("Content-Disposition: attachment;Filename=doc№".$bill['number']."_OrderBill-".date("d.m.Y-H.i").".doc");
		}
		
		$logo = SettingsModel::get('logo');
		$logoInfo = getimagesize(HTTP_ROOT.'/media/files/settings/'.$logo);
		$html = '';
		$html .= '
		<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:st1="urn:schemas-microsoft-com:office:smarttags" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<meta http-equiv=Content-Type content="text/html; charset=utf-8">
		<meta name=ProgId content=Word.Document>
		<meta name=Generator content="Microsoft Word 11">
		<meta name=Originator content="Microsoft Word 11">
		<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
		 name="metricconverter"/>
		<!--[if gte mso 9]><xml>
		 <w:WordDocument>
		  <w:View>Print</w:View>
		  <w:Zoom>105</w:Zoom>
		  <w:DoNotHyphenateCaps/>
		  <w:PunctuationKerning/>
		  <w:DrawingGridHorizontalSpacing>0 пт</w:DrawingGridHorizontalSpacing>
		  <w:DrawingGridVerticalSpacing>0 пт</w:DrawingGridVerticalSpacing>
		  <w:ValidateAgainstSchemas/>
		  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
		  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
		  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
		  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
		 </w:WordDocument>
		</xml><![endif]--><!--[if gte mso 9]><xml>
		 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
		 </w:LatentStyles>
		</xml><![endif]--><!--[if !mso]><object classid="clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D" id=ieooui></object>
		<style>
		st1\:*{behavior:url(#ieooui) }
		</style>
		<![endif]-->
		<style>
		<!--
		 /* Font Definitions */
		 body {
		 	font-size: 80%
		 }
		 table {
		 	font-size: 100%
		 }
		 @font-face
			{font-family:Verdana;
			panose-1:2 11 6 4 3 5 4 4 2 4;
			mso-font-charset:204;
			mso-generic-font-family:swiss;
			mso-font-pitch:variable;
			mso-font-signature:536871559 0 0 0 415 0;}
		 /* Style Definitions */
		 p.MsoNormal, li.MsoNormal, div.MsoNormal
			{mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:7.5pt;
			mso-bidi-font-size:8.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		p.small, li.small, div.small
			{mso-style-name:small;
			mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:1.0pt;
			font-family:Verdana;
			mso-fareast-font-family:Verdana;
			mso-bidi-font-family:"Times New Roman";}
		@page Section1
			{size:595.3pt 841.9pt;
			margin: 2.0cm 2.0cm 42.55pt 2.0cm;
			mso-header-margin:35.45pt;
			mso-footer-margin:35.45pt;
			mso-paper-source:0;}
		div.Section1
			{page:Section1;}
		-->
		</style>
		<!--[if gte mso 10]>
		<style>
		 /* Style Definitions */
		 table.MsoNormalTable
			{mso-style-name:"Обычная таблица";
			mso-tstyle-rowband-size:0;
			mso-tstyle-colband-size:0;
			mso-style-noshow:yes;
			mso-style-parent:"";
			mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
			mso-para-margin:0cm;
			mso-para-margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:10.0pt;
			font-family:"Times New Roman";
			mso-ansi-language:#0400;
			mso-fareast-language:#0400;
			mso-bidi-language:#0400;}
			nostyle 	{mso-style-parent:style0;
			text-align:right;
			border:.5pt solid black;
			white-space:normal;}
		</style>
		<![endif]--><!--[if gte mso 9]><xml>
		 <o:shapedefaults v:ext="edit" spidmax="3074">
		  <o:colormenu v:ext="edit" strokecolor="none"/>
		 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
		 <o:shapelayout v:ext="edit">
		  <o:idmap v:ext="edit" data="1"/>
		 </o:shapelayout></xml><![endif]-->
		<style>
		* { font-family:arial; font-size:12px; }
		h1 { font-size:16px; }
		</style>
		</head>

		<body lang=RU style=\'tab-interval:35.4pt\'>
		<div class=Section1>


		<table width="800px" align="center" class="MsoNormalTable">
		<tr>
			<td>'.$this->values['OrderBill.name.firm'].'</td>
			<td style="padding:8px 0px;" valign="top" align="right"><img src="'.HTTP_ROOT.'/media/files/settings/'.$logo.'" alt=""/></td>
		</tr>
		<tr>
			<td style="padding:8px 0px;">'.$this->values['OrderBill.contacts'].'</td>
			<td style="padding:8px 0px;"></td>
		</tr>
		<tr>
			<td style="padding:8px 0px;" align="center" colspan="2"><h1>Товарный чек №'.$bill['number'].' от '.date("d.m.Y",$bill['dt']).' года</h1></td>
		</tr>
		<tr>
			<td style="padding:8px 0px;" colspan="2">Покупатель: '.$platelshik.'</td>
		</tr>
		<tr>
			<td style="padding:8px 0px;" colspan="2">
			
				<table border="1" cellpadding="5" cellspacing="0" bordercolor="#000000" width="100%">
				<tr>
					<th>№</th>
					<th>№ заказа</th>
					<th>Артикул</th>
					<th>Наименование</th>
					<th>Производитель</th>
					<th>Единица измерения</th>
					<th>Количество</th>
					<th width="100px">Цена</th>
					<th width="100px">Сумма</th>
				</tr>';

		if (isset($items) && count($items)>0){
			$i=$sum=$s1=0; foreach ($items as $item){ $i++;

			$s1 = ($item['cc'] * $item['price']);
			$sum += $s1;
				
			$html .= '<tr>
						<td>'.$i.'&nbsp;</td>
						<td>'.$item['bill_number'].'&nbsp;</td>
						<td>'.$item['article'].'&nbsp;</td>
						<td>'.$item['descr_tecdoc'].'&nbsp;</td>
						<td>'.$item['brand'].'&nbsp;</td>
						<td>шт.&nbsp;</td>
						<td>'.$item['cc'].'&nbsp;</td>
						<td>'.PriceHelper::numberDoc($item['price']).'&nbsp;</td>
						<td>'.PriceHelper::numberDoc($s1).'&nbsp;</td>
					</tr>';
			}
		}

		$html .= '<tr>
					<th colspan="8" align="right">Итого сумма:</th>
					<td>'.PriceHelper::numberDoc($sum).'&nbsp;</td>
				</tr>
				</table>
			
			</td>
		</tr>
		<tr>
			<td colspan="2">
				Получено:<br><br>
				<hr hoshade="noshade" size="1"/>
			</td>
		</tr>
		<tr>
			<td>Отпустил:______________________________</td>
			<td align="right">Принял:______________________________</td>
		</tr>
		</table>
			
		</div>
		</body>
		</html>
		';
		echo($html);
		exit();
	}
	
	function BillFacture($numberBill=false){
	
		$getInfo = $this->getInfo($numberBill);
		$bill = $getInfo['bill'];
		$items = $getInfo['items'];
		$accountD = $getInfo['account'];
		$platelshik = (($accountD['is_firm'] == 1)?($accountD['firm_name'].' '.$accountD['firm_inn'].' '.$accountD['firm_kpp'].', Банк '.$accountD['firm_bank'].', Р/С '.$accountD['firm_pc'].' '.$accountD['firm_kc'].' '.$accountD['firm_bnk'].' '.$accountD['firm_ogrn'].' '.$accountD['firm_okpo']):($bill['f1'].',  тел.: '.$bill['f2'].', e-mail: '.$bill['f3']));
		
		$val1 = date("d",$bill['dt']);
		$val2 = date("m.Y",$bill['dt']);
		$BillFacture_name = $this->values['BillFacture.name'];
		$BillFacture_address = $this->values['BillFacture.address'];
		$BillFacture_innkpp = $this->values['BillFacture.inn.kpp'];
		$BillFacture_gruzaddress = $this->values['BillFacture.gruz.address'];
		$BillFacture_director = $this->values['BillFacture.director'];
		$BillFacture_buhalka = $this->values['BillFacture.buhalka'];
		
		$listHtml = '';
		if (isset($items) && count($items)>0){
			$i=$sum=$s1=0; foreach ($items as $item){ $i++;
			
			$s1 = ($item['cc'] * $item['price']);
			$sum += $s1;
			
$listHtml .= '
<tr class=xl35 height=31 style=\'mso-height-source:userset;height:31px\'>
	<td colspan=6 class=xl81 width=195 style=\'height:31px; border-right:.5pt solid black;width:148pt\'>'.$item['descr_tecdoc'].' '.$item['article'].' '.$item['brand'].'</td>
	<td class=xl43>796</td>
	<td colspan=3 class=xl85 style=\'border-right:.5pt solid black;border-left:none\'>шт</td>
	<td colspan=2 class=xl87 style=\'border-right:.5pt solid black;border-left:none\' x:num u2:num>'.$item['cc'].'</td>
	<td colspan=3 class=xl89 style=\'border-right:.5pt solid black;border-left: none\' x:num="'.PriceHelper::numberDoc($item['price']).'">'.PriceHelper::numberDoc($item['price']).'</td>
	<td colspan=4 class=xl89 style=\'border-right:.5pt solid black;border-left: none\' x:num="'.PriceHelper::numberDoc($item['cc']*$item['price']).'">'.PriceHelper::numberDoc($item['cc']*$item['price']).'</td>
	<td colspan=2 class=xl89 style=\'border-right:.5pt solid black;border-left:none\'>без акциза</td>
	<td colspan=2 class=xl91 style=\'border-right:.5pt solid black;border-left:none\'>0</td>
	<td colspan=2 class=xl89 style=\'border-right:.5pt solid black;border-left:none\' x:num="0">-</td>
	<td colspan=4 class=xl94 style=\'border-right:.5pt solid black;border-left:none\' x:num="'.PriceHelper::numberDoc($item['cc']*$item['price']).'">'.PriceHelper::numberDoc($item['cc']*$item['price']).'</td>
	<td class=xl43></td>
	<td colspan=2 class=xl96 style=\'border-right:.5pt solid black;border-left:none\'></td>
	<td colspan=2 class=xl87 style=\'border-right:.5pt solid black;border-left:none\'></td>
</tr>';
			}
			$listHtml .= '
<tr class=xl35 height=16 style=\'mso-height-source:userset;height:12.0pt\'>
	<td colspan=15 height=16 class=xl98 style=\'border-right:.5pt solid black;height:12.0pt\'>Всего к оплате</td>
	<td colspan=4 class=xl89 style=\'border-right:.5pt solid black;border-left:none\' x:num="'.PriceHelper::numberDoc($sum).'">'.PriceHelper::numberDoc($sum).'</td>
	<td colspan=4 class=xl80 style=\'border-right:.5pt solid black;border-left:none\'>X</td>
	<td colspan=2 class=xl89 style=\'border-right:.5pt solid black;border-left:none\' >0</td>
	<td colspan=4 class=xl94 style=\'border-right:.5pt solid black;border-left:none\'  x:num="'.PriceHelper::numberDoc($sum).'">'.PriceHelper::numberDoc($sum).'</td>
	<td colspan=5 class=xl36 style=\'mso-ignore:colspan\'>&nbsp;</td>
</tr>';
		}
	
		if (!$this->debbug){
 		header("Content-type: application/msexcel");
 		header("Content-Disposition: attachment;Filename=doc№".$bill['number']."BillFacture-".date("d.m.Y-H.i").".xls");
		}
		
		$html = '';
		$html .=<<<END

<html xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Excel.Sheet>
<meta name=Generator content="Microsoft Excel 11">



<!--[if gte mso 9]><xml>
	<o:DocumentProperties>
		<o:LastAuthor>user</o:LastAuthor>
		<o:LastPrinted>2012-01-25T08:29:28Z</o:LastPrinted>
		<o:LastSaved>2012-01-25T08:29:29Z</o:LastSaved>
		<o:Version>11.9999</o:Version>
	</o:DocumentProperties>
</xml><![endif]-->
<style>
<!--@page SECTION1
{margin-bottom:1cm;
margin-left:1cm;
margin-right:1cm;
margin-top:1cm;
mso-footer-margin:35.45pt;
mso-header-margin:35.45pt;
mso-page-orientation:landscape;
mso-paper-source-first-page:0;
mso-paper-source-other-pages:0;
size:841.9pt 595.3pt;}
table
{mso-displayed-decimal-separator:"\,";
mso-displayed-thousand-separator:" ";}
@page
{margin:.39in .39in .39in .39in;
mso-header-margin:.51in;
mso-footer-margin:.51in;
mso-page-orientation:landscape;}
tr
{mso-height-source:auto;}
col
{mso-width-source:auto;}
br
{mso-data-placement:same-cell;}
.style0
{mso-number-format:General;
text-align:general;
vertical-align:bottom;
white-space:nowrap;
mso-rotate:0;
mso-background-source:auto;
mso-pattern:auto;
color:windowtext;
font-size:10.0pt;
font-weight:400;
font-style:normal;
text-decoration:none;
font-family:"Arial Cyr";
mso-generic-font-family:auto;
mso-font-charset:204;
border:none;
mso-protection:locked visible;
mso-style-name:Обычный;
mso-style-id:0;}
td
{mso-style-parent:style0;
padding-top:1px;
padding-right:1px;
padding-left:1px;
mso-ignore:padding;
color:windowtext;
font-size:10.0pt;
font-weight:400;
font-style:normal;
text-decoration:none;
font-family:"Arial Cyr";
mso-generic-font-family:auto;
mso-font-charset:204;
mso-number-format:General;
text-align:general;
vertical-align:bottom;
border:none;
mso-background-source:auto;
mso-pattern:auto;
mso-protection:locked visible;
white-space:nowrap;
mso-rotate:0;}
.xl24
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-background-source:auto;
mso-pattern:auto none;}
.xl25
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;}
.xl26
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
background:white;
mso-pattern:auto none;}
.xl27
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:right;
background:white;
mso-pattern:auto none;}
.xl28
{mso-style-parent:style0;
font-size:12.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;}
.xl29
{mso-style-parent:style0;
font-size:12.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
background:white;
mso-pattern:auto none;}
.xl30
{mso-style-parent:style0;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
background:white;
mso-pattern:auto none;}
.xl31
{mso-style-parent:style0;
font-size:12.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;}
.xl32
{mso-style-parent:style0;
font-size:12.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
background:white;
mso-pattern:auto none;}
.xl33
{mso-style-parent:style0;
font-size:12.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl34
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:left;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl35
{mso-style-parent:style0;
font-size:8.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;}
.xl36
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
background:white;
mso-pattern:auto none;}
.xl37
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl38
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:.5pt solid windowtext;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl39
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:.5pt solid windowtext;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl40
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:none;
border-bottom:none;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl41
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl42
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:.5pt solid windowtext;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl43
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:.5pt solid windowtext;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl44
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:.5pt solid windowtext;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl45
{mso-style-parent:style0;
font-size:8.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
background:white;
mso-pattern:auto none;}
.xl46
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
background:white;
mso-pattern:auto none;}
.xl47
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl48
{mso-style-parent:style0;
font-size:8.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
background:white;
mso-pattern:auto none;}
.xl49
{mso-style-parent:style0;
font-size:12.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl50
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
vertical-align:middle;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;
white-space:normal;}
.xl51
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
vertical-align:middle;
border-top:none;
border-right:none;
border-bottom:.5pt solid black;
border-left:none;
background:white;
mso-pattern:auto none;
white-space:normal;}
.xl52
{mso-style-parent:style0;
font-size:12.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl53
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:left;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl54
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl55
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl56
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl57
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl58
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl59
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl60
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl61
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
vertical-align:middle;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl62
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
vertical-align:middle;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl63
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
vertical-align:middle;
border-top:none;
border-right:none;
border-bottom:none;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl64
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
vertical-align:middle;
border-top:none;
border-right:.5pt solid black;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl65
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:none;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl66
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
background:white;
mso-pattern:auto none;}
.xl67
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:.5pt solid black;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl68
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl69
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl70
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl71
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:none;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl72
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:none;
border-bottom:none;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl73
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:.5pt solid black;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl74
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl75
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
border-top:none;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl76
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl77
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl78
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl79
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl80
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl81
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:left;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;
white-space:normal;
mso-text-control:shrinktofit;}
.xl82
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:left;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;
white-space:normal;
mso-text-control:shrinktofit;}
.xl83
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:left;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;
white-space:normal;
mso-text-control:shrinktofit;}
.xl84
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl85
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl86
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl87
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl88
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:Fixed;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl89
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:Fixed;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl90
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:Fixed;
text-align:center;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl91
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl92
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl93
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:Fixed;
text-align:right;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl94
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:Fixed;
text-align:right;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid black;
background:white;
mso-pattern:auto none;}
.xl95
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:Fixed;
text-align:right;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl96
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;
white-space:nowrap;
mso-text-control:shrinktofit;}
.xl97
{mso-style-parent:style0;
font-size:9.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
mso-number-format:"\@";
text-align:center;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;
white-space:nowrap;
mso-text-control:shrinktofit;}
.xl98
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:left;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:.5pt solid windowtext;
background:white;
mso-pattern:auto none;}
.xl99
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:left;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl100
{mso-style-parent:style0;
font-size:9.0pt;
font-weight:700;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:left;
border-top:.5pt solid windowtext;
border-right:.5pt solid black;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl101
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:none;
border-right:none;
border-bottom:.5pt solid windowtext;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl102
{mso-style-parent:style0;
font-size:8.0pt;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
.xl103
{mso-style-parent:style0;
font-family:"Times New Roman", serif;
mso-font-charset:204;
text-align:center;
border-top:.5pt solid windowtext;
border-right:none;
border-bottom:none;
border-left:none;
background:white;
mso-pattern:auto none;}
-->
</style>
<!--[if gte mso 9]><xml>
	<x:ExcelWorkbook>
		<x:ExcelWorksheets>
			<x:ExcelWorksheet>
				<x:Name>Bill Facture</x:Name>
				<x:WorksheetOptions>
					<x:Print>
						<x:ValidPrinterInfo/>
						<x:PaperSizeIndex>9</x:PaperSizeIndex>
						<x:Scale>96</x:Scale>
						<x:HorizontalResolution>300</x:HorizontalResolution>
						<x:VerticalResolution>300</x:VerticalResolution>
					</x:Print>
					<x:ShowPageBreakZoom/>
					<x:PageBreakZoom>100</x:PageBreakZoom>
					<x:Selected/>
					<x:DoNotDisplayGridlines/>
					<x:Panes>
						<x:Pane>
							<x:Number>3</x:Number>
							<x:ActiveRow>45</x:ActiveRow>
							<x:ActiveCol>34</x:ActiveCol>
						</x:Pane>
					</x:Panes>
					<x:ProtectContents>False</x:ProtectContents>
					<x:ProtectObjects>False</x:ProtectObjects>
					<x:ProtectScenarios>False</x:ProtectScenarios>
				</x:WorksheetOptions>
			</x:ExcelWorksheet>
		</x:ExcelWorksheets>
		<x:WindowHeight>13170</x:WindowHeight>
		<x:WindowWidth>24780</x:WindowWidth>
		<x:WindowTopX>240</x:WindowTopX>
		<x:WindowTopY>75</x:WindowTopY>
		<x:ProtectStructure>False</x:ProtectStructure>
		<x:ProtectWindows>False</x:ProtectWindows>
	</x:ExcelWorkbook>
</xml><![endif]--><!--[if gte mso 9]><xml>
	<o:shapedefaults v:ext="edit" spidmax="1025" u1:ext="edit">
		<o:colormenu v:ext="edit" strokecolor="none"/>
	</o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
	<u3:WordDocument>
		<u3:View>Print</u3:View>
		<u3:Zoom>100</u3:Zoom>
		<u3:DoNotHyphenateCaps/>
		<u3:PunctuationKerning/>
		<u3:DrawingGridHorizontalSpacing>0 пт</u3:DrawingGridHorizontalSpacing>
		<u3:DrawingGridVerticalSpacing>0 пт</u3:DrawingGridVerticalSpacing>
		<u3:ValidateAgainstSchemas/>
		<u3:SaveIfXMLInvalid>false</u3:SaveIfXMLInvalid>
		<u3:IgnoreMixedContent>false</u3:IgnoreMixedContent>
		<u3:AlwaysShowPlaceholderText>false</u3:AlwaysShowPlaceholderText>
		<u3:BrowserLevel>MicrosoftInternetExplorer4</u3:BrowserLevel>
	</u3:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
	<u4:LatentStyles DefLockedState="false" LatentStyleCount="156">  </u4:LatentStyles>
</xml><![endif]-->
</head>

<body link=blue vlink=purple>

<table x:str border=0 cellpadding=0 cellspacing=0 width=1026 style='border-collapse:collapse;table-layout:fixed;width:779pt'>
<col class=xl24 width=26 span=2 style='mso-width-source:userset;mso-width-alt:950;width:20pt'>
<col class=xl24 width=53 style='mso-width-source:userset;mso-width-alt:1938;width:40pt'>
<col class=xl24 width=31 style='mso-width-source:userset;mso-width-alt:1133;width:23pt'>
<col class=xl24 width=33 style='mso-width-source:userset;mso-width-alt:1206;width:25pt'>
<col class=xl24 width=26 style='mso-width-source:userset;mso-width-alt:950;width:20pt'>
<col class=xl24 width=31 style='mso-width-source:userset;mso-width-alt:1133;width:23pt'>
<col class=xl24 width=26 style='mso-width-source:userset;mso-width-alt:950;width:20pt'>
<col class=xl24 width=31 style='mso-width-source:userset;mso-width-alt:1133;width:23pt'>
<col class=xl24 width=26 span=12 style='mso-width-source:userset;mso-width-alt:950;width:20pt'>
<col class=xl24 width=31 style='mso-width-source:userset;mso-width-alt:1133;width:23pt'>
<col class=xl24 width=37 style='mso-width-source:userset;mso-width-alt:1353;width:28pt'>
<col class=xl24 width=26 style='mso-width-source:userset;mso-width-alt:950;width:20pt'>
<col class=xl24 width=47 style='mso-width-source:userset;mso-width-alt:1718;width:35pt'>
<col class=xl24 width=26 span=4 style='mso-width-source:userset;mso-width-alt:950;width:20pt'>
<col class=xl24 width=44 style='mso-width-source:userset;mso-width-alt:1609;width:33pt'>
<col class=xl24 width=47 style='mso-width-source:userset;mso-width-alt:1718;width:35pt'>
<col class=xl24 width=41 style='mso-width-source:userset;mso-width-alt:1499;width:31pt'>
<col class=xl24 width=27 span=2 style='mso-width-source:userset;mso-width-alt:987;width:20pt'>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 class=xl26 width=26 style='height:12.75pt;width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=53 style='width:40pt'>&nbsp;</td>
	<td class=xl26 width=31 style='width:23pt'>&nbsp;</td>
	<td class=xl26 width=33 style='width:25pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=31 style='width:23pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=31 style='width:23pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=31 style='width:23pt'>&nbsp;</td>
	<td class=xl26 width=37 style='width:28pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=47 style='width:35pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=26 style='width:20pt'>&nbsp;</td>
	<td class=xl26 width=44 style='width:33pt'>&nbsp;</td>
	<td class=xl26 width=47 style='width:35pt'>&nbsp;</td>
	<td class=xl26 width=41 style='width:31pt'>&nbsp;</td>
	<td class=xl26 width=27 style='width:20pt'>&nbsp;</td>
	<td class=xl27 width=27 style='width:20pt'>Приложение № 1</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=33 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
	<td class=xl27>к постановлению Правительства</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=33 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
	<td class=xl27>Российской Федерации</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=33 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
	<td class=xl27>от 26 декабря 2011 г. № 1137</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=34 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl28 height=21 style='mso-height-source:userset;height:15.75pt'>
	<td height=21 colspan=4 class=xl29 style='height:15.75pt;mso-ignore:colspan'>&nbsp;</td>
	<td colspan=6 class=xl29>СЧЕТ-ФАКТУРА №</td>
	<td colspan=4 class=xl49>{$bill['number']}</td>
	<td class=xl29>от</td>
	<td class=xl29 style="text-align:right;">&quot;</td>
	<td colspan=2 class=xl49>{$val1}</td>
	<td class=xl29>&quot;</td>
	<td colspan=6 class=xl49>{$val2}</td>
	<td class=xl29 x:str="'(1)">(1)</td>
	<td colspan=4 class=xl29 style='mso-ignore:colspan'>&nbsp;</td>
	<td class=xl30>&nbsp;</td>
	<td colspan=3 class=xl29 style='mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=8 style='mso-height-source:userset;height:6.0pt'>
	<td height=8 colspan=10 class=xl26 style='height:6.0pt;mso-ignore:colspan'>&nbsp;</td>
	<td colspan=4 rowspan=2 class=xl50 width=104 style='border-bottom:.5pt solid black;width:80pt' x:str="'_">_</td>
	<td colspan=6 class=xl26 style='mso-ignore:colspan'>&nbsp;</td>
	<td colspan=4 rowspan=2 class=xl50 width=120 style='border-bottom:.5pt solid black;width:91pt' x:str="'_">_</td>
	<td colspan=10 class=xl26 style='mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl31 height=22 style='mso-height-source:userset;height:16.5pt'>
	<td height=22 colspan=4 class=xl32 style='height:16.5pt;mso-ignore:colspan'>&nbsp;</td>
	<td colspan=5 class=xl32>ИСПРАВЛЕНИЕ №</td>
	<td class=xl32>&nbsp;</td>
	<td class=xl32>от</td>
	<td class=xl32 style="text-align:right;">&quot;</td>
	<td colspan=2 class=xl52>&nbsp;</td>
	<td class=xl32>&quot;</td>
	<td class=xl33>&nbsp;</td>
	<td class=xl33>&nbsp;</td>
	<td colspan=2 class=xl32 x:str="'(1а)">(1а)</td>
	<td colspan=3 class=xl32 style='mso-ignore:colspan'>&nbsp;</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=3 class=xl32 style='mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=34 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=3 height=17 class=xl26 style='height:12.75pt'>Продавец</td>
	<td colspan=30 class=xl34>{$BillFacture_name}</td>
	<td class=xl26 x:str="'(2)">(2)</td>
</tr>
<tr class=xl25 height=7 style='mso-height-source:userset;height:5.25pt'>
	<td height=7 colspan=34 class=xl26 style='height:5.25pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=2 height=17 class=xl26 style='height:12.75pt'>Адрес</td>
	<td colspan=31 class=xl34>{$BillFacture_address}</td>
	<td class=xl26 x:str="'(2а)">(2а)</td>
</tr>
<tr class=xl25 height=7 style='mso-height-source:userset;height:5.25pt'>
	<td height=7 colspan=34 class=xl26 style='height:5.25pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=4 height=17 class=xl26 style='height:12.75pt'>ИНН/ КПП продавца</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=28 class=xl34>{$BillFacture_innkpp}</td>
	<td class=xl26 x:str="'(2б)">(2б)</td>
</tr>
<tr class=xl25 height=5 style='mso-height-source:userset;height:3.75pt'>
	<td height=5 colspan=34 class=xl26 style='height:3.75pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=6 height=17 class=xl26 style='height:12.75pt'>Грузоотправитель и его адрес</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=26 class=xl34>{$BillFacture_gruzaddress}</td>
	<td class=xl26 x:str="'(3)">(3)</td>
</tr>
<tr class=xl25 height=6 style='mso-height-source:userset;height:4.5pt'>
	<td height=6 colspan=34 class=xl26 style='height:4.5pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=5 height=17 class=xl26 style='height:12.75pt'>Грузополучатель и его адрес</td>
	<td colspan=2 class=xl26 style='mso-ignore:colspan'>&nbsp;</td>
	<td colspan=26 class=xl34>{$platelshik}</td>
	<td class=xl26 x:str="'(4)">(4)</td>
</tr>
<tr class=xl25 height=7 style='mso-height-source:userset;height:5.25pt'>
	<td height=7 colspan=34 class=xl26 style='height:5.25pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=7 height=17 class=xl26 style='height:12.75pt'>К платежно-расчетному документу №</td>
	<td class=xl34 colspan="4"></td>
	<td class=xl34>&nbsp;</td>
	<td class=xl34>&nbsp;</td>
	<td class=xl34>&nbsp;</td>
	<td class=xl26>от</td>
	<td colspan=18 class=xl34>&nbsp;</td>
	<td class=xl26 x:str="'(5)">(5)</td>
</tr>
<tr class=xl25 height=6 style='mso-height-source:userset;height:4.5pt'>
	<td height=6 colspan=34 class=xl26 style='height:4.5pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=3 height=17 class=xl26 style='height:12.75pt'>Покупатель</td>
	<td colspan=30 class=xl34>{$platelshik}</td>
	<td class=xl26 x:str="'(6)">(6)</td>
</tr>
<tr class=xl25 height=5 style='mso-height-source:userset;height:3.75pt'>
	<td height=5 colspan=34 class=xl26 style='height:3.75pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=2 height=17 class=xl26 style='height:12.75pt'>Адрес</td>
	<td class=xl26>{$bill['delivery_addess']}</td>
	<td colspan=30 class=xl34>&nbsp;</td>
	<td class=xl26 x:str="'(6а)">(6а)</td>
</tr>
<tr class=xl25 height=24 style='mso-height-source:userset;height:18.0pt'>
	<td colspan=4 height=24 class=xl26 style='height:18.0pt'>ИНН/ КПП покупателя</td>
	<td colspan=29 class=xl53>&nbsp;</td>
	<td class=xl26>(6б)</td>
</tr>
<tr class=xl25 height=23 style='mso-height-source:userset;height:17.25pt'>
	<td colspan=5 height=23 class=xl26 style='height:17.25pt'>Валюта: наименование, код</td>
	<td colspan=28 class=xl53>российский рубль, 643</td>
	<td class=xl26 x:str="'(7)">(7)</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=34 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=34 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl35 height=16 style='mso-height-source:userset;height:12.0pt'>
	<td colspan=6 height=16 class=xl54 style='border-right:.5pt solid black;height:12.0pt'>Наименование товара (описание</td>
	<td colspan=4 class=xl57 style='border-right:.5pt solid black;border-left:none'>Единица</td>
	<td colspan=2 class=xl57 style='border-right:.5pt solid black;border-left:none'>Коли-</td>
	<td colspan=3 class=xl57 style='border-right:.5pt solid black;border-left:none'>Цена (тариф)</td>
	<td colspan=4 class=xl59 style='border-right:.5pt solid black;border-left:none'>Стоимость товаров</td>
	<td colspan=2 class=xl57 style='border-right:.5pt solid black;border-left:none'>В том</td>
	<td colspan=2 rowspan=2 class=xl61 style='border-right:.5pt solid black' x:str="Налоговая ">Налоговая<span style='mso-spacerun:yes'> </span></td>
	<td colspan=2 class=xl57 style='border-right:.5pt solid black;border-left:none'>Сумма</td>
	<td colspan=4 class=xl59 style='border-right:.5pt solid black;border-left:none'>Стоимость товаров</td>
	<td colspan=3 class=xl57 style='border-right:.5pt solid black;border-left:none'>Страна</td>
	<td colspan=2 class=xl57 style='border-right:.5pt solid black;border-left:none'>Номер</td>
</tr>
<tr class=xl35 height=16 style='mso-height-source:userset;height:12.0pt'>
	<td colspan=6 height=16 class=xl65 style='border-right:.5pt solid black;height:12.0pt'>выполенных работ, оказанных</td>
	<td colspan=4 class=xl69 style='border-right:.5pt solid black;border-left:none'>измерения</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>чество</td>
	<td colspan=3 class=xl71 style='border-right:.5pt solid black;border-left:none'>за единицу</td>
	<td colspan=4 class=xl72 style='border-right:.5pt solid black;border-left:none'>(работ, услуг), иму-</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>числе</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>налога,</td>
	<td colspan=4 class=xl71 style='border-right:.5pt solid black;border-left:none'>(работ, услуг),</td>
	<td colspan=3 class=xl74 style='border-right:.5pt solid black;border-left:none' x:str="происхождения товара ">происхождения товара<span style='mso-spacerun:yes'> </span></td>
	<td colspan=2 class=xl72 style='border-right:.5pt solid black;border-left:none'>таможен-</td>
</tr>
<tr class=xl35 height=16 style='mso-height-source:userset;height:12.0pt'>
	<td colspan=6 height=16 class=xl65 style='border-right:.5pt solid black;height:12.0pt'>услуг), имущественного права</td>
	<td class=xl38>Код</td>
	<td colspan=3 class=xl54 style='border-right:.5pt solid black;border-left:none'>Условное</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>(объем)</td>
	<td colspan=3 class=xl71 style='border-right:.5pt solid black;border-left:none'>измерения</td>
	<td colspan=4 class=xl72 style='border-right:.5pt solid black;border-left:none'>щественных прав</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>сумма</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>ставка</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>предъяв-</td>
	<td colspan=4 class=xl71 style='border-right:.5pt solid black;border-left:none'>имущественных</td>
	<td class=xl39>Цифр</td>
	<td colspan=2 class=xl54 style='border-right:.5pt solid black;border-left:none'>Краткое</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>ной дек-</td>
</tr>
<tr class=xl35 height=16 style='mso-height-source:userset;height:12.0pt'>
	<td height=16 class=xl40 style='height:12.0pt'>&nbsp;</td>
	<td colspan=4 class=xl36 style='mso-ignore:colspan'>&nbsp;</td>
	<td class=xl38>&nbsp;</td>
	<td class=xl38>&nbsp;</td>
	<td colspan=3 class=xl65 style='border-right:.5pt solid black;border-left:none'>обозначение</td>
	<td class=xl36>&nbsp;</td>
	<td class=xl38>&nbsp;</td>
	<td colspan=2 class=xl36 style='mso-ignore:colspan'>&nbsp;</td>
	<td class=xl38>&nbsp;</td>
	<td colspan=4 class=xl40 style='border-right:.5pt solid black;border-left:none'>без налога - всего</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>акциза</td>
	<td class=xl36>&nbsp;</td>
	<td class=xl38>&nbsp;</td>
	<td colspan=2 class=xl65 style='border-right:.5pt solid black;border-left:none' x:str="ляемая ">ляемая<span style='mso-spacerun:yes'> </span></td>
	<td colspan=4 class=xl71 style='border-right:.5pt solid black;border-left:none'>прав с налогом -</td>
	<td class=xl39>овой</td>
	<td colspan=2 class=xl65 style='border-right:.5pt solid black;border-left:none'>наимено-</td>
	<td colspan=2 class=xl71 style='border-right:.5pt solid black;border-left:none'>ларации</td>
</tr>
<tr class=xl35 height=16 style='mso-height-source:userset;height:12.0pt'>
	<td height=16 class=xl41 style='height:12.0pt'>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
	<td colspan=3 class=xl41 style='border-right:.5pt solid black;border-left:none'>(национальное)</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
	<td colspan=2 class=xl76 style='border-right:.5pt solid black;border-left:none'>покупателю</td>
	<td colspan=4 class=xl69 style='border-right:.5pt solid black;border-left:none'>всего</td>
	<td class=xl43>код</td>
	<td colspan=2 class=xl76 style='border-right:.5pt solid black;border-left:none'>вание</td>
	<td class=xl37>&nbsp;</td>
	<td class=xl42>&nbsp;</td>
</tr>
<tr class=xl35 height=16 style='mso-height-source:userset;height:12.0pt'>
	<td colspan=6 height=16 class=xl77 style='border-right:.5pt solid black;
  height:12.0pt' x:num>1</td>
	<td class=xl44 x:num>2</td>
	<td colspan=3 class=xl77 style='border-right:.5pt solid black;border-left:none'>2а</td>
	<td colspan=2 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>3</td>
	<td colspan=3 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>4</td>
	<td colspan=4 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>5</td>
	<td colspan=2 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>6</td>
	<td colspan=2 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>7</td>
	<td colspan=2 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>8</td>
	<td colspan=4 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>9</td>
	<td class=xl44 x:num>10</td>
	<td colspan=2 class=xl77 style='border-right:.5pt solid black;border-left:none'>10а</td>
	<td colspan=2 class=xl80 style='border-right:.5pt solid black;border-left:none' x:num>11</td>
</tr>
		
{$listHtml}

<tr class=xl35 height=15 style='mso-height-source:userset;height:11.25pt'>
	<td height=15 colspan=34 class=xl45 style='height:11.25pt;mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=5 height=17 class=xl26 style='height:12.75pt'>Руководитель организации</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=4 class=xl101>&nbsp;</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=6 class=xl101>{$BillFacture_director}</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=6 class=xl27>Главный бухгалтер</td>
	<td class=xl47>&nbsp;</td>
	<td class=xl47>&nbsp;</td>
	<td class=xl47>&nbsp;</td>
	<td class=xl47>&nbsp;</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=5 class=xl101>{$BillFacture_buhalka}</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=6 height=17 class=xl26 style='height:12.75pt'>или иное уполномоченное лицо</td>
	<td colspan=4 class=xl102>(подпись)</td>
	<td class=xl26></td>
	<td colspan=6 class=xl103>(ФИО)</td>
	<td colspan=7 class=xl27>или иное уполномоченное лицо</td>
	<td colspan=4 class=xl102>(подпись)</td>
	<td colspan=6 class=xl46>(ФИО)</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=6 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
	<td colspan=4 class=xl48 style='mso-ignore:colspan'>&nbsp;</td>
	<td class=xl26></td>
	<td colspan=7 class=xl46 style='mso-ignore:colspan'>&nbsp;</td>
	<td colspan=5 class=xl26 style='mso-ignore:colspan'>&nbsp;</td>
	<td colspan=4 class=xl48 style='mso-ignore:colspan'>&nbsp;</td>
	<td class=xl45>&nbsp;</td>
	<td colspan=6 class=xl46 style='mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=7 height=17 class=xl26 style='height:12.75pt'>Индивидуальный предприниматель</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=3 class=xl101>&nbsp;</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=6 class=xl101 style="text-align:left;"></td>
	<td class=xl26>&nbsp;</td>
	<td colspan=15 class=xl101></td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=8 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
	<td colspan=3 class=xl102>(подпись)</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=6 class=xl103>(ФИО)</td>
	<td class=xl26>&nbsp;</td>
	<td colspan=15 class=xl103>(реквизиты свидетельства о государственной регистрации</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td height=17 colspan=19 class=xl26 style='height:12.75pt;mso-ignore:colspan'>&nbsp;</td>
	<td colspan=15 class=xl46>индивидуального предпринимателя)</td>
</tr>
<tr class=xl25 height=17 style='mso-height-source:userset;height:12.75pt'>
	<td colspan=23 height=17 class=xl45 style='height:12.75pt'>Примечание 1.
		Первый экземпляр счета-фактуры, составленного на бумажном носителе -
		покупателю, второй экземпляр - продавцу.</td>
	<td colspan=11 class=xl26 style='mso-ignore:colspan'>&nbsp;</td>
</tr>
<tr class=xl35 height=15 style='mso-height-source:userset;height:11.25pt'>
	<td colspan=24 height=15 class=xl45 style='height:11.25pt'>2. При составлении
		организацией счета-фактуры в электронном виде показатель &quot;Главный
		бухгалтер (подпись) (ФИО)&quot; не формируется.</td>
	<td colspan=10 class=xl45 style='mso-ignore:colspan'>&nbsp;</td>
</tr>
<![if supportMisalignedColumns]>
<tr height=0 style='display:none'>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=53 style='width:40pt'></td>
	<td width=31 style='width:23pt'></td>
	<td width=33 style='width:25pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=31 style='width:23pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=31 style='width:23pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=31 style='width:23pt'></td>
	<td width=37 style='width:28pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=47 style='width:35pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=26 style='width:20pt'></td>
	<td width=44 style='width:33pt'></td>
	<td width=47 style='width:35pt'></td>
	<td width=41 style='width:31pt'></td>
	<td width=27 style='width:20pt'></td>
	<td width=27 style='width:20pt'></td>
</tr>
<![endif]>
</table>
</body>
</html>


END;
	
		
	
		echo($html);
		exit();
	}
}

?>