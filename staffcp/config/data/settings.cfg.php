<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['settings'] = array(
	'table'         =>  DB_PREFIX.'settings',
	'title'         =>  $translates['admin.settings.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'code'		  	=> 	'input',
		'value'			=> 	'text',
		'group'			=> 	'input',
		'type'			=> 	'input',
	),
	'generator' => array(
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'code'			=> 	$translates['admin.main.code'],
			'value'			=> 	$translates['admin.main.value'],
			'group'			=> 	$translates['admin.settings.group'],
			'type'			=> 	$translates['admin.settings.type'],

		),
		'list' => array(
			'fields' => array('name','order'),
			'title'	 => $translates['admin.settings.name'],
			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','order','content'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','order','content'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)
);