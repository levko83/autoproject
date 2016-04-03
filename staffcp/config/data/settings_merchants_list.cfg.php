<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['settings_merchants_list'] = array(
	'table'         =>  DB_PREFIX.'settings_merchants_list',
	'title'         =>  'Описание платежных систем',
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'content'		=> 	'htmlarea',
		'sort'			=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'mcode'		  	=> 	'input',
			'price'		  	=> 	'input',
		'img'		  	=> 	array(
			'type'	=> 'imageResize',
			// 'base_dir'		=> 'data/',
			'base_dir'		=> '../../static/images/data/payments/',
			'images' => array(
					'normal'	=>	'130x100',
			),
		),
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'content'		=> 	$translates['admin.main.content'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'is_active'		=>  $translates['admin.main.view'],
			'mcode'		  	=> 	'Код системы',
			'price'		  	=> 	'Дополнительная Цена ',
			'img'		  	=> 	'Логотип',
		),
		'list' => array(
			'fields' => array('id','name','is_active','sort','mcode'),
			'title'         =>  'Описание платежных систем',
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','content','is_active','price','sort','img'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'name','content','is_active','price','sort','mcode','img'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);