<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['emails'] = array(
	'table'         =>  DB_PREFIX.'emails',
	'title'         =>  $translates['admin.email.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'code'		  	=> 	'input',
		'name'		  	=> 	'input',
		'value'			=> 	'htmlarea',
	),
	'generator' => array(
		'disabled' => array('add','delete'),
		'fields' => array(
			'id'            =>  'ID',
			'code'			=> 	$translates['admin.main.code'],
			'name'		  	=> 	$translates['admin.main.name'],
			'value'			=> 	$translates['admin.main.value'],
		),
		'list' => array(
			'fields' => array('code','name'),
			'title'	 => $translates['admin.email.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'code','name','value'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'code','name','value'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)
);