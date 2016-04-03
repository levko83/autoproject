<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['vin'] = array(
	'table'         =>  DB_PREFIX.'vin',
	'title'         =>  'Запрос по Vin',
	'fields'        =>  array(
		'id'        	=>  'index',
		'account_id'    =>  array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'accounts',
			'first' => array(0=>''),
		),
		'account_car_id'    =>  array(
			'type'			=> 'category',
			'cross_name'	=> 'car_name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'accounts_cars',
			'first' => array(0=>''),
		),
		'dt' => 'date',
		'name' => 'input',
		'mark' => array('type'=>'htmlarea','small'=>true),
		'vin' => 'input',
		'message' => array('type'=>'htmlarea','small'=>true),
		'contacts' => array('type'=>'htmlarea','small'=>true),
		'email' => 'input',
		'answer' => 'htmlarea',
		'isset'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'send_mail'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'account_id'    =>  'Пользователь',
			'account_car_id'    =>  'Автомобиль клиента',
			'dt' => 'Дата запроса',
			'name' => 'Имя',
			'mark' => 'Марка',
			'vin' => 'Vin',
			'message' => 'Список деталий',
			'contacts' => 'Контакты',
			'email' => 'E-mail',
			'answer' => 'Ответ',
			'isset'   	=>  'Обработан',
			'send_mail'   	=>  '<b>Отправить ответ на указанный E-mail</b>',
		),
		'list' => array(
			'fields' => array('id','dt','name','mark','vin','message','contacts','email','answer','isset'),
			'title'	 => 'Запрос по Vin',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'account_id','account_car_id','dt','name','mark','vin','message','contacts','email','answer','isset','send_mail'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> 'Отправить ответ',
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'account_id','account_car_id','dt','name','mark','vin','message','contacts','email','answer','isset','send_mail'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);