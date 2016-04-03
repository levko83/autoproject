<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['pricelists'] = array(
	'table'         =>  DB_PREFIX.'pricelists',
	'title'         =>  'Прайс-листы',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'file'		  	=> 	array(
			'type' => 'file',
			'base_dir' => 'prices/',
			'name' => 'date'
		),
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'dt'		  	=> 	'date',
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	'Название файла',
			'file'		  	=> 	'Файл',
			'is_active'		=> 	'Выводить на сайте',
			'dt'			=> 	'Дата',
		),
		'list' => array(
			'fields' => array('id','name','file','is_active','dt'),
			'title'         =>  'Прайс-листы',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','file','is_active','dt'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','file','is_active','dt'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);