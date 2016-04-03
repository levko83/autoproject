<?php

class ImportersModel extends Orm {
	
	var $accounts = array();
	var $accounts_price = array();
	
	public function __construct() {
		parent::__construct(DB_PREFIX.'importers');
	}
	
	public static function getUnsignedAccounts($field='id'){
		$db = Register::get('db');
		$sql = "SELECT ".mysql_real_escape_string($field)." FROM ".DB_PREFIX."importers WHERE disable_unsigned_accounts = 0;";
		$res = $db->query($sql);
		$ret = array(0);
		if (isset($res) && count($res)>0){
			foreach ($res as $dd){
				$ret []= $dd[$field];
			}
		}
		return $ret;
	}
	
	public static function getById($id,$price=array()) {
		
		$importers = new ImportersModel();
		
		if (isset($price['IS_ACCOUNT']) && $price['IS_ACCOUNT']) {
			if(isset($importers->accounts_price[$price['IS_ACCOUNT']]))
				return $importers->accounts_price[$price['IS_ACCOUNT']];
			$account = AccountsModel::getById($price['IS_ACCOUNT']);
			$res = array("id"=>$account['id'],"code"=>"account","name"=>$account['name'],"name_price"=>"u".$account['id'],"discount"=>$account['warehouse_extra'],"money_type"=>0,"rate"=>0,"delivery"=>0,"email"=>$account['email']);
			$importers->accounts_price[$price['IS_ACCOUNT']]=$res;
			return $res;
		}
		else {
				
			if ($id){
				if(isset($importers->accounts[$id]))
					return $importers->accounts[$id];
				
				$model = new ImportersModel();
				$res = $model->
					select()->
					fields(
						DB_PREFIX.'importers.id,'.
						DB_PREFIX.'importers.code,'.
						DB_PREFIX.'importers.name,'.
						DB_PREFIX.'importers.name_price,'.
						DB_PREFIX.'importers.discount,'.
						DB_PREFIX.'importers.money_type,'.
						DB_PREFIX.'importers.currency,'.
						'IF('.DB_PREFIX.'importers_offices_params.delivery,('.DB_PREFIX.'importers_offices_params.delivery+'.DB_PREFIX.'importers.delivery),'.DB_PREFIX.'importers.delivery) as delivery,'.
						DB_PREFIX.'importers.email,'.
						DB_PREFIX.'importers.color,'.
						DB_PREFIX.'importers.margin_id,'.
						DB_PREFIX.'importers.info,'.
						DB_PREFIX.'importers.ONLY_FOR_SHOP,'.
						DB_PREFIX.'importers.price_date_update,'.
						DB_PREFIX.'importers.sort,'.
						DB_PREFIX.'importers.currency_id,'.
						DB_PREFIX.'importers.only_preorder,'.
						DB_PREFIX.'importers.country,'.
						DB_PREFIX.'importers.disable_unsigned_accounts,'.
						DB_PREFIX.'currencies.`currency` currecyName,'.
						DB_PREFIX.'currencies.`view` money_type,'.
						DB_PREFIX.'currencies.`rate` currency,'.
						DB_PREFIX.'currencies.`round`,'.
						DB_PREFIX.'importers.`only_preorder` only_preorder'
					)->
					join(DB_PREFIX.'currencies',DB_PREFIX.'currencies.id='.DB_PREFIX.'importers.currency_id')->
					join(DB_PREFIX.'importers_offices_params',DB_PREFIX.'importers_offices_params.imp_id='.DB_PREFIX.'importers.id AND '.DB_PREFIX.'importers_offices_params.office_id = "'.(int)Register::get('getOfficeIdParam').'"')->
					where(DB_PREFIX."importers.id=? or ".DB_PREFIX."importers.code=?",(int)$id,mysql_real_escape_string($id))->
					fetchOne();
				return $res;
			}
			else {
				return array();
			}
		}
	}
	
	public static function getAll(){
		$model = new ImportersModel();
		$res = $model->select()->
			fields(
				DB_PREFIX.'importers.id,'.
				DB_PREFIX.'importers.code,'.
				DB_PREFIX.'importers.name,'.
				DB_PREFIX.'importers.name_price,'.
				DB_PREFIX.'importers.discount,'.
				DB_PREFIX.'importers.money_type,'.
				DB_PREFIX.'importers.currency,'.
				'IF('.DB_PREFIX.'importers_offices_params.delivery,('.DB_PREFIX.'importers_offices_params.delivery+'.DB_PREFIX.'importers.delivery),'.DB_PREFIX.'importers.delivery) as delivery,'.
				DB_PREFIX.'importers.email,'.
				DB_PREFIX.'importers.color,'.
				DB_PREFIX.'importers.margin_id,'.
				DB_PREFIX.'importers.info,'.
				DB_PREFIX.'importers.ONLY_FOR_SHOP,'.
				DB_PREFIX.'importers.price_date_update,'.
				DB_PREFIX.'importers.sort,'.
				DB_PREFIX.'importers.currency_id,'.
				DB_PREFIX.'importers.only_preorder,'.
				DB_PREFIX.'importers.country,'.
				DB_PREFIX.'importers.disable_unsigned_accounts,'.
				DB_PREFIX.'currencies.`currency` currecyName,'.
				DB_PREFIX.'currencies.`view` money_type,'.
				DB_PREFIX.'currencies.`rate` currency,'.
				DB_PREFIX.'currencies.`round`,'.
				DB_PREFIX.'importers.`only_preorder` only_preorder'
			)->
			join(DB_PREFIX.'currencies',DB_PREFIX.'currencies.id='.DB_PREFIX.'importers.currency_id')->
			join(DB_PREFIX.'importers_offices_params',DB_PREFIX.'importers_offices_params.imp_id='.DB_PREFIX.'importers.id AND '.DB_PREFIX.'importers_offices_params.office_id = "'.(int)Register::get('getOfficeIdParam').'"')->
			fetchAll();
		return $res;
	}
}