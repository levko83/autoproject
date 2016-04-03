<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['accounts'] = array(
	'layout'         =>  'screen',
	'table'         =>  DB_PREFIX.'accounts',
	'title'         =>  $translates['admin.accounts.name'],
	'fields'        =>  array(
		'id'        	=>  'index',
		'email'    		=>  'input',
		'pass'    		=>  'password',
		'account_code' 	=>  'input',
		'name'	    	=>  'input',
		'phones'    	=>  'input',
		'country'    	=>  'input',
		'city'    		=>  'input',
		'address'    	=>  array('type'=>'text','style'=>'width:99%;height:50px;'),
		'info'	    	=>  array('type'=>'text','style'=>'width:99%;height:50px;','view'=>'full_text','class'=>'see-data'),
		'discount'    	=>  array('type'=>'input','settings_code'=>'extramargin_account'),
		'dt'	    	=>  'date',
		'is_active'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'is_firm'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		
		'firm_name'    		=>  'input',
		'firm_inn'    		=>  'input',
		'firm_kpp'    		=>  'input',
		'firm_bank'    		=>  'input',
		'firm_pc'    		=>  'input',
		'firm_kc'    		=>  'input',
		'firm_bnk'    		=>  'input',
		'firm_ogrn'    		=>  'input',
		'firm_okpo'    		=>  'input',
		'firm_discount'    	=>  array('type'=>'input','settings_code'=>'extramargin_account_firm'),
		
		'balance'  			=>  'readonly',
		'balance_plus'		=>  'input',
		'balance_comment'	=>  'input',
		
		'is_active_warehouse'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'warehouse_extra'  			=>  'input',
		'office_id'			=> 	array(
			'type'			=> 'category2',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'offices',
			
			'cross_join_index'	=> 'city_id',
			
			'join_name'		=> 'name',
			'join_index'	=> 'id',
			'join_table'	=> DB_PREFIX . 'dic_cities',
			
			'first'	=>	array(
				'0' => '',
			),
		),
		'set_manager_id'	=> 	array(
			'type'			=> 'category2',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'_user',
			
			'cross_join_index'	=> 'office_id',
			
			'join_name'		=> 'name',
			'join_index'	=> 'id',
			'join_table'	=> DB_PREFIX . 'offices',
			
			'first'	=>	array(
				'0' => '',
			),
		),
		
		'web_service' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'web_service_login'	=>  'input',
		'web_service_pass' =>  'input',
		'web_service_tecdoc' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'web_service_db' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'web_service_wbs' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'web_service_fk'		  	=> 	array(
			'type'	=>	'multiple',
			'foreign'	=> array(
				'table'	=>	DB_PREFIX.'importers',
				'id'			=>	'id',
				'name'			=>	'name',
				'foreign_id'	=>	'id',
			),
			'link'	=> array(
				'table'			=>	DB_PREFIX.'a2i',
				'source_id'		=>	'fk_account',
				'foreign_id'	=>	'fk_importer',
			),
		),
		'purchase_active'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'discountname_id'    	=>  array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'accounts_discountnames',
			'first'	=>	array(
					'0' => '',
			),
		),
		'is_limit_active' =>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'datetime_set_discount_programm' => 'date',
		'document_active' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'saldo_active' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'out_price_currency'		=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'currency',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'currencies',
			'first'			=> array(
				'0'	=>	'',
			),
		),
			
		'fields_importer'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'email'    		=>  '<b>'.$translates['admin.accounts.email'].' (Логин)</b>',
			'phones'    	=>  '<b>'.$translates['admin.accounts.phone'].' (Логин)</b>',
			'pass'    		=>  '<b>'.$translates['admin.accounts.pass'].'</b>',
			'account_code' 	=>  'Код клиента (номер карты клиента)',
			'name'	    	=>  '<b>'.$translates['admin.accounts.fullname'].'</b>',
			'country'    	=>  $translates['admin.accounts.country'],
			'city'    		=>  $translates['admin.accounts.city'],
			'address'    	=>  $translates['admin.accounts.address'],
			'info'	    	=>  $translates['admin.accounts.info'],
			'discount'    	=>  '<b>Наценка/Скидка по умолчанию</b>',
			'dt'	    	=>  $translates['admin.accounts.dtreg'],
			'is_active'   	=>  $translates['admin.accounts.active'],
			'is_firm'   	=>  $translates['admin.accounts.lowaddress'],
			
			'firm_name'    		=>  $translates['admin.accounts.firm'],
			'firm_inn'    		=>  $translates['admin.accounts.inn'],
			'firm_kpp'    		=>  $translates['admin.accounts.kpp'],
			'firm_bank'    		=>  $translates['admin.accounts.bank'],
			'firm_pc'    		=>  $translates['admin.accounts.rs'],
			'firm_kc'    		=>  $translates['admin.accounts.ks'],
			'firm_bnk'    		=>  $translates['admin.accounts.bnk'],
			'firm_ogrn'    		=>  $translates['admin.accounts.ogrn'],
			'firm_okpo'    		=>  $translates['admin.accounts.okpo'],
			'firm_discount'    	=>  '<b>Наценка/Скидка по умолчанию на юр.лицо</b>',
			
			'balance'  			=>  $translates['admin.accounts.balance'],
			'balance_plus'		=>  $translates['admin.accounts.balance.plus'],
			'balance_comment'	=>  'Комментарий с зачисление/снятию средств',
			
			'is_active_warehouse'	=>  $translates['admin.main.yesno.warehouse'],
			'warehouse_extra'  		=>  $translates['admin.main.warehouse.extra'],
			'office_id'				=>	'Представительство (Офис)',
			'set_manager_id'		=>	'Персональный менеджер',
			
			'web_service' => 'Разрешить доступ к веб-сервису',
			'web_service_login'	=>  'Логин доступа',
			'web_service_pass' =>  'Пароль доступа',
			'web_service_tecdoc' => 'Разрешить получать цены основных поставщиков',
			'web_service_wbs' => 'Разрешить получать цены от веб-сервисов',
			'web_service_fk' => 'Доступ к поставщикам',
			'purchase_active'   	=>  'Доступ к закупочным ценам',
			'discountname_id' => '<b>Название системы скидок</b>',
			'is_limit_active' =>  'Ограничить доступ к поставщикам (включить и выбрать поставщиков к которым доступ открыт)',
			'datetime_set_discount_programm'	=>  'Дата установки скидки (по дисконту или скидки по умолчанию для зарег.)',
			'document_active' => 'Доступ к документам в ЛК',
			'saldo_active' => 'Доступ к состоянию баланса в ЛК',
			'out_price_currency' => 'Показывать конечные цены в пересчете по курсу в валюте (Каталог, Корзина, Личный кабинет)',
				
			'fields_importer'   	=>  'Выводить колонку - Направление',
		),
		'list' => array(
			'fields' => array('id','email','name','phones','city','discount','discountname_id','info','is_firm','is_active','document_active','saldo_active','dt'),
			'title'	 => $translates['admin.accounts.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'email','phones','pass','account_code','name','country','city','address','info','dt','is_active','office_id','set_manager_id','purchase_active','document_active','saldo_active','out_price_currency',
				),
				$translates['admin.accounts.lowaddress'] => array(
					'is_firm','firm_name','firm_inn','firm_kpp','firm_bank','firm_pc','firm_kc','firm_bnk','firm_ogrn','firm_okpo','firm_discount',
				),
				$translates['admin.warehouse'] => array(
					'is_active_warehouse','warehouse_extra'
				),
				'Доступ к веб-сервису' => array(
					'web_service','web_service_login','web_service_pass','web_service_tecdoc','web_service_wbs','web_service_fk',
				),
				'Баланс' => array(
					'balance','balance_plus','balance_comment',
				),
				'Скидка (Скидочная система)' => array(
					'datetime_set_discount_programm','discount','discountname_id','is_limit_active',
				),
				'Формирование колонок поиска' => array(
					'fields_importer',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'email','phones','pass','account_code','name','country','city','address','info','dt','is_active','office_id','set_manager_id','purchase_active','document_active','saldo_active','out_price_currency',
				),
				$translates['admin.accounts.lowaddress'] => array(
					'is_firm','firm_name','firm_inn','firm_kpp','firm_bank','firm_pc','firm_kc','firm_bnk','firm_ogrn','firm_okpo','firm_discount',
				),
				$translates['admin.warehouse'] => array(
					'is_active_warehouse','warehouse_extra'
				),
				'Доступ к веб-сервису' => array(
					'web_service','web_service_login','web_service_pass','web_service_tecdoc','web_service_wbs','web_service_fk',
				),
				'Баланс' => array(
					'balance','balance_plus','balance_comment',
				),
				'Скидка (Скидочная система)' => array(
					'datetime_set_discount_programm','discount','discountname_id','is_limit_active',
				),
				'Формирование колонок поиска' => array(
					'fields_importer',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);