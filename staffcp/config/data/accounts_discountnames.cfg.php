<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['accounts_discountnames'] = array(
	'table'         =>  DB_PREFIX.'accounts_discountnames',
	'title'         =>  'Группы клиентов, Названия скидок(групп скидок) для клиента',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'is_limit_active' =>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'is_limit_active' =>  'Ограничить доступ к поставщикам (включить и выбрать поставщиков к которым доступ открыт)',
		),
		'list' => array(
			'fields' => array('id','name','is_limit_active',),
			'title'	 => 'Группы клиентов, Названия скидок(групп скидок) для клиента',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','is_limit_active',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','is_limit_active',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);