<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['manufacturers'] = array(
	'table'         =>  DB_PREFIX.'manufacturers',
	'title'         =>  $translates['admin.cars.name'],
	'fields'        =>  array(
		'MFA_ID'        	=>  'index',
		'MFA_MFC_CODE'		=> 	'input',
		'MFA_BRAND'			=> 	'input',
		'MY_SORT'			=> 	'input',
		'MY_ACTIVE'			=> 	array('type'=>'checkbox','label'=>$translates['admin.main.yesno'], 'index' => 'MFA_ID'),
		'lkw'			=> 	array('type'=>'checkbox','label'=>$translates['admin.main.yesno']),
		'withlogo'			=> 	array('type'=>'checkbox','label'=>$translates['admin.main.yesno']),
		'inindex'			=> 	array('type'=>'checkbox','label'=>$translates['admin.main.yesno']),
		'MY_IMG'			=> 	array(
			'type' => 'imageResize','base_dir' => 'logos/',
			'images' => array('small' => '100x100', 'normal' => '150x150'),
		),
		'MY_IMG_HOVER'			=> 	array(
			'type' => 'imageResize','base_dir' => 'logos/',
			'images' => array('small' => '100x100', 'normal' => '150x150'),
		),
		'MY_DEFAULT'		=> 	array('type'=>'checkbox','label'=>$translates['admin.main.yesno']),
		
		'name'		  	=> 	'input',
		'content'		=> 	'htmlarea',
		'title'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
		'h1'			=> 	'input',
		'original_url'	=> 	'input',
		'country'		=> 	'input',
		'autotover_link'		=> 	'input',
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'MFA_ID'        	=>  'ID',
			'MFA_MFC_CODE'		=> 	$translates['admin.main.alias'],
			'MFA_BRAND'			=> 	$translates['admin.main.name'],
			'MY_SORT'			=> 	$translates['admin.main.sort'],
			'MY_ACTIVE'			=> 	$translates['admin.main.view'],
			'MY_IMG'			=> 	$translates['admin.main.logo'],
			'MY_IMG_HOVER'		=> 	'Логотип: инверсия (hover)',
			'MY_DEFAULT'		=> 	'Выводить на главную',
			'lkw'				=> 	' Коммерческий транспорт ?',
			'inindex'			=>	'Выводить на главную как LKW ?',
			'withlogo'			=>	'Выводить на главную с Лого ?',
			
			'name'		  	=> 	$translates['admin.main.name'],
			'content'		=> 	$translates['admin.main.content'],
			'title'			=>  $translates['admin.main.seo_title'],
			'kwords'		=>  $translates['admin.main.seo_kwords'],
			'descr'			=>  $translates['admin.main.seo_descr'],
			'h1'			=> 	'H1',
			'original_url'	=> 	'Ссылка на оригинальный каталог',
			'country'		=> 	'Страна производителя',
			'autotover_link'		=> 	'Ссылка на каталог Автотовар',
		),
		'list' => array(
			'fields' => array('MFA_ID','MFA_BRAND','MY_SORT','MY_ACTIVE','lkw','inindex','withlogo','MY_DEFAULT','country','MY_IMG','MY_IMG_HOVER','original_url',(INSTALL_AVTOTOVAR_API?'autotover_link':'')),
			'title'	 => $translates['admin.cars.name'],
			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'MFA_BRAND','MY_SORT','MY_ACTIVE','lkw','inindex','withlogo','MY_DEFAULT','country','MY_IMG','MY_IMG_HOVER','original_url',(INSTALL_AVTOTOVAR_API?'autotover_link':'')
				),
				'Описание' => array(
					'name','content','title','kwords','descr','h1'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'MFA_BRAND','MY_SORT','MY_ACTIVE','lkw','inindex','withlogo','MY_DEFAULT','country','MY_IMG','MY_IMG_HOVER','original_url',(INSTALL_AVTOTOVAR_API?'autotover_link':'')
				),
				'Описание' => array(
					'name','content','title','kwords','descr','h1'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);