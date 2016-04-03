<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['logs'] = array(
	'table'         =>  DB_PREFIX.'logs',
	'title'         =>  'Логи действий',
	'fields'        =>  array(
		'id'        =>  'index',
		'dt'        =>  'date',
		'user'		=> 	'input',
		'descr'		=> 	'input',
		'url'		=> 	'input',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		'fields' => array(
			'id'        =>  'ID',
			'dt'        =>  'Дата',
			'user'		=> 	'Пользователь',
			'descr'		=> 	'Действие',
			'url'		=> 	'Адрес действия',
		),
		'list' => array(
			'fields' => array('id','dt','user','descr','url'),
			'title'	 => 'Логи действий',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'dt','user','descr','url'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'dt','user','descr','url'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);