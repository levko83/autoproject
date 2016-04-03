<?php

require 'account.php';

class DocumentsController extends AccountController {
	
	public $layout = 'home';
	
	public function index() {
		
		require './staffcp/library/helpers/price.helper.php';
		require './staffcp/models/bills.model.php';
		require './staffcp/models/users.model.php';
		require './staffcp/library/classes/doc.class.php';
		
		if (!$this->verification()) {
			$this->redirectUrl('/account/signin/');
		}
		
		$billId = $this->request("billId",false);
		$f = $this->request("f",false);
		
		if (!$billId || !$f){
			$this->error404();
		}
		
		$Doc = new Doc();
		if (function_exists($Doc->$f($billId))){
			$this->error404();
		}
		exit();
	}
}

?>