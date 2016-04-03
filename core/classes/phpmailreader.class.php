<?php 

// так подрубаемся к яндексу $host="{imap.yandex.ru:143/imap/notls}"; $login="yandexuser"; $password="somepwd";
// так к gmail в том числе и googleapps $host="{imap.gmail.com:993/imap/ssl}"; $login="test@test.ru"; $password="somepwd";
/* использование не сложное. $msg->mail возвращается массив  таких массивов
 array(8) {
 ["from"]
 ["to"]
 ["name"]
 ["subject"]
 // все заголовки выше уже находятся в utf-8
 ["charset"] - в этой кодировка находятся 2 параметра ниже
 ["plain"]  - сообщение в формате plaintext
 ["html"] - - сообщение в формате HTML ,один из них может быть [так как правило и есть] равен пустой строке.
 ["attach"] - массив с атачментами вида 'имяфайла'=>содержание файла
 array(1) {
 ["someattachname.txt"]=> 'какое-то его содержание';
 }
 }
 Работа с IMAP: получение непрочитанных писем с атачментами и приведением заголовков к UTF-8
 Что главное: заголоки приведены к UTF8, а ['plain'] и ['html'] необходимо привести из ["charset"] к чему-либо самим. Напр: $decoded=mb_convert_encoding($letter['html'],'UTF-8',$letter['charset'])
 */

class Phpmailreader {
	
	private $mbox='';
	private $htmlmsg = '';
	private $plainmsg = '';
	private $charset = '';
	private $attachments = array();
	private $unread;

	public function __get($name){
		if ($name=='mail')
			return $this->unread;
		else
			return null;
	}
	
	public function getmail(){
		return $this->unread;
	}
	
	public function __construct($host, $login, $pwd, $imap_search) { /* backwards compatibility for php 4, __constructor*/
	
		try {
			
			$messages = array();
			$folder = "INBOX";
	
			//$folder="[Gmail]/&BCEEPwQwBDw-";
			// если вам захочется почитать спам на гугломыле.
			//var_dump("{$host}{$folder}", $login, $pwd);
			
			$this->mbox = @imap_open("{"."{$host}"."}"."{$folder}", $login, $pwd) or die(imap_last_error());
			$arr = imap_search($this->mbox, 'UNSEEN FROM "'.$imap_search.'"');
			if ($arr !== false) {
					
				foreach ($arr as $i){
	
					$headerArr = imap_headerinfo($this->mbox, $i);
					$mailArr =
					array(
							'sender' => $headerArr->sender[0]->mailbox . "@" . $headerArr->sender[0]->host,
							'to' => $headerArr->to[0]->mailbox . "@" . $headerArr->to[0]->host,
							'date' => $headerArr->date,
							'size' => $headerArr->Size,
							'subject' => $headerArr->subject,
					);
						
					$this->getmsg($i);
	
					imap_setflag_full($this->mbox, $i, "\\Seen");
	
// 					var_dump($mailArr);
// 					exit();
					
					$messages []=
					array(
							'from'=> $mailArr['sender'],
							'to'=> $mailArr['to'],
							'name'=> $this->decode($headerArr->sender[0]->personal),
							'subject'=>$this->decode($mailArr['subject']),
							'charset'=>$this->charset,
							'plain'=>$this->plainmsg,
							'html'=>$this->htmlmsg,
							'attach'=>$this->attachments
					);
				}
					
				$this->unread=$messages;
				unset($messages);
			}
			else {
				$this->unread=false;
			}
			imap_close($this->mbox);
			
		}
		catch (Exception $exception){
			echo '<html><head><style> * { font-family: Verdana, Tahoma, Arial; font-size: 13px; } body {} .error {width: 860px;text-align: left;} .error h1 {font-size: 18px;margin-left: 36px;} .error-num {float: left;width: 30px;background-color: #fff;padding: 3px;text-align: left;} .error-descr {float: left;background-color: #f5f5f5;margin-bottom: 10px;padding: 3px;width: 800px;text-align: left;} .error-descr span {font-weight: bold;}</style></head></body>';
			echo '<center><div class="error"><h1>'.$exception->getMessage().'</h1>';
				foreach ($exception->getTrace() as $key=>$item) {
					echo '<div class="error-item"><div class="error-num">'.($key + 1).'.</div>';
					echo '<div class="error-descr">';
					echo '<span>'.$item['class'].' '.$item['type'].' '.$item['function'].' '.'( '.join(', ',$item['args']).' )'.'</span><br>';
					echo $item['file'].' (Line: '.$item['line'].')<br>';
					echo '</div><br clear="all" /></div>';
				}
			echo '</div></center>';
			echo '</body></html>';
			exit();
		}
	}
	
	private function decode($enc){
		$parts = imap_mime_header_decode($enc);
		$str='';
		for ($p=0; $p<count($parts); $p++) {
			$ch=$parts[$p]->charset;
			$part=$parts[$p]->text;
			if ($ch!=='default')
				$str.=mb_convert_encoding($part,'UTF-8',$ch);
			else
				$str.=$part;
		}
		return $str;
	}
	
	private function getmsg($mid) {
		$this->htmlmsg = $this->plainmsg = $this->charset = '';
		$this->attachments = array();

		$s = imap_fetchstructure($this->mbox,$mid);
		if (!$s->parts)
			$this->getpart($mid,$s,0);
		else {
			foreach ($s->parts as $partno0=>$p)
				$this->getpart($mid,$p,$partno0+1);
		}
	}
	
	private function getpart($mid,$p,$partno) {
	
		$data = ($partno)? imap_fetchbody($this->mbox,$mid,$partno): imap_body($this->mbox,$mid);
		if ($p->encoding==4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding==3)
		$data = base64_decode($data);

		$params = array();
		if (isset($p->parameters) && $p->parameters)
			foreach ($p->parameters as $x)
				$params[ strtolower( $x->attribute ) ] = $x->value;

		if (isset($p->dparameters) && $p->dparameters)
			foreach ($p->dparameters as $x)
				$params[ strtolower( $x->attribute ) ] = $x->value;

		if ((isset($params['filename']) && $params['filename']) || (isset($params['name']) && $params['name'])) {
			$filename = ($params['filename'])? $params['filename'] : $params['name'];
			$this->attachments[$filename] = $data;  // если 2 файла с одним именем - тут баг. TODO
		}
		elseif ($p->type==0 && $data) {

			if (strtolower($p->subtype)=='plain')
				$this->plainmsg .= trim($data) ."\n\n";
			else
				$this->htmlmsg .= $data ."<br><br>";
			$this->charset = $params['charset'];
		}
		elseif ($p->type==2 && $data) {
			$this->plainmsg .= trim($data) ."\n\n";
		}

		if (isset($p->parts) && $p->parts) {
			foreach ($p->parts as $partno0=>$p2)
				$this->getpart($mid,$p2,$partno.'.'.($partno0+1));
		}
	}
}

?>