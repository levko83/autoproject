<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['callme'] = array(
	'table'         =>  DB_PREFIX.'callme',
	'title'         =>  'Обратный звонок',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'readonly',
		'code'			=> 	'readonly',
		'value'			=> 	'code',
	),
	'generator' => array(
		'disabled' => array('add','delete'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	'Название',
			'code'			=> 	'Код',
			'value'			=> 	'Значение',
		),
		'list' => array(
			'fields' => array('id','name','code','value'),
			'title'         =>  'Обратный звонок',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','code','value'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','code','value'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);