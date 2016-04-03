<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['dic_cities'] = array(
	'table'         =>  DB_PREFIX.'dic_cities',
	'title'         =>  $translates['admin.title.cities'],
	'fields'        =>  array(
		'id'        	=>  'index',
		'name'  		=>  'input',
		'is_active'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'sort'  		=>  'input',
		'is_default'   	=>  array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'id'			=>  'ID',
			'name'    		=>  $translates['admin.main.name'],
			'is_active'    	=>  $translates['admin.main.view'],
			'sort'    		=>  $translates['admin.main.sort'],
			'is_default'   	=>  'Город по умолчанию',
		),
		'list' => array(
			'fields' => array('id','name','is_active','sort','is_default'),
			'title'	 => $translates['admin.title.cities'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','is_active','sort','is_default'
					),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','is_active','sort','is_default'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);