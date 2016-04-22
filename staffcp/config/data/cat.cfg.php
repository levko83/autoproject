<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['cat'] = array(
	'table'         =>  DB_PREFIX.'cat',
	'title'         =>  $translates['admin.cat.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'STR_ID'            =>  'readonly2',
		'STR_ID_PARENT'            =>  'readonly2',
		'STR_DES_TEXT_RU'		  	=> 	'input',
		'STR_DES_TEXT_DE'		  	=> 	'input',
		'STR_DES_TEXT_FR'		  	=> 	'input',
		'STR_DES_TEXT_ES'		  	=> 	'input',
		'STR_DES_TEXT_EN'		  	=> 	'input',
		'STR_DES_TEXT_GR'		  	=> 	'input',
		'STR_DES_TEXT_DE'		  	=> 	'input',
		'STR_DES_TEXT_IT'		  	=> 	'input',
		'STR_DES_TEXT_NO'		  	=> 	'input',
		'STR_DES_TEXT_DA'		  	=> 	'input',
		'DESCENDANTS'            	=>  'readonly2',
		'img'		  	=> 	array(
			'type'	=> 'imageResize',
			'base_dir'		=> 'products/',
			'images' => array(
				'normal'	=>	'50x50',
			),
		),
	),
	'generator' => array(
		'fields' => array(
			'id'            =>  'ID',
			'is_active'		=> 	$translates['admin.cat.viewsite'],
			'STR_ID'		=> 	'ID категорий',
			'STR_ID_PARENT'		=> 	$translates['admin.cat.parent'],
			'STR_DES_TEXT_RU' => 'Название Russian',
			'STR_DES_TEXT_DE' => 'Название German',
			'STR_DES_TEXT_FR' => 'Название French',
			'STR_DES_TEXT_ES' => 'Название Spanish',
			'STR_DES_TEXT_EN' => 'Название English',
			'STR_DES_TEXT_GR' => 'Название Greek',
			'STR_DES_TEXT_IT' => 'Название Italian',
			'STR_DES_TEXT_NO' => 'Название Novrwegian',
			'STR_DES_TEXT_DA' => 'Название Danish',
			'DESCENDANTS' => 'Главная категория',
			'img'			=>	$translates['admin.cat.icon']
		),
		'list' => array(
			'fields' => array('id','STR_ID','STR_DES_TEXT_RU','is_active','DESCENDANTS'),
			'title'         =>  $translates['admin.cat.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'is_active','STR_ID','STR_ID_PARENT','STR_DES_TEXT_RU','STR_DES_TEXT_DE','STR_DES_TEXT_FR','STR_DES_TEXT_ES','STR_DES_TEXT_EN','STR_DES_TEXT_GR','STR_DES_TEXT_IT','STR_DES_TEXT_NO','STR_DES_TEXT_DA','DESCENDANTS','img'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'is_active','STR_ID','STR_ID_PARENT','STR_DES_TEXT_RU','STR_DES_TEXT_DE','STR_DES_TEXT_FR','STR_DES_TEXT_ES','STR_DES_TEXT_EN','STR_DES_TEXT_GR','STR_DES_TEXT_IT','STR_DES_TEXT_NO','STR_DES_TEXT_DA','DESCENDANTS','img'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);