<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['dic_sms'] = array(
	'table'         =>  DB_PREFIX.'dic_sms',
	'title'         =>  'Шаблоны sms уведомлений',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'content'		=> 	'htmlarea',
	),
	'generator' => array(
		'disabled' => array('add','delete'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'content'		=> 	$translates['admin.main.content'],
		),
		'list' => array(
			'fields' => array('id','name','content'),
			'title'	 => 'Шаблоны sms уведомлений',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','content',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','content',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);