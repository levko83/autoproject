<?php


class Mail {

	private $code = '';
	private $template;
	private $sourceEncoding = 'utf-8';
	private $assignedValues;

	public function  __construct($code, $assignedValues = null) {
		$this->code = $code;
		$this->assignedValues = $assignedValues;
		$this->loadByCode($code);
	}

	public function assign($assignedValues)
	{
		$this->assignedValues = $assignedValues;
	}

	public function send()
	{
		$this->parseTemplate();
		if ($this->template['encoding'] != $this->sourceEncoding)
		{
			$this->changeTemplateEncoding();
		}
		$headers = $this->getHeaders();
		$to = $this->template['to'];
		$subject = $this->template['subject'];
		$message = $this->template['content'];
		
		return mail($to, $subject, $message);
	}

	private function parseTemplate()
	{
		$assing = array();
		foreach ($this->assignedValues as $key=>$value) {

			$assing['{'.$key.'}'] = $value;
		}
		foreach($this->template as $key=>$value) {

			$this->template[$key] = str_replace(array_keys($assing), $assing, $value);
		}
	}

	private function getHeaders()
	{
		$headers = '';
		if ($this->template['format'] == 'html'){
			$headers .= 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset="'. $this->template['encoding'] . "\"\r\n";
		} else {
			$headers .= 'Content-type: text/plain; charset="'. $this->template['encoding'] . "\"\r\n";
		}

		// Additional headers
		$headers .= 'To: ' . $this->template['to'] . "\r\n";
		$headers .= 'From: ' . $this->template['from'] . "\r\n";
		return $headers;
	}

	private function changeTemplateEncoding()
	{
		foreach($this->template as $key=>$value)
			$this->template[$key] = iconv($this->sourceEncoding, $this->template['encoding'], $value);
	}

	private function loadByCode($code)
	{
		$db = Register::get('db');
		$template = $db->get('SELECT * FROM `'.DB_PREFIX.'email` WHERE `code`=\''.$code.'\'');
		if (empty($template))
			throw new Exception('Unknow email template '.$code);
		$this->template = $template;
	}
}