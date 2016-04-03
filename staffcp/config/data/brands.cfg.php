<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['brands'] = array(
	'table'         =>  DB_PREFIX.'brands',
	'title'         =>  $translates['admin.brand.name'],
	'fields'        =>  array(
		'BRA_ID'        	=>  'index',
		'BRA_ID_GET' 		=> array(
			'type'			=> 'category',
			'cross_name'	=> 'BRA_BRAND',
			'ordered'		=> 'BRA_BRAND',
			'cross_index'	=> 'BRA_ID',
			'cross_table'	=> DB_PREFIX.'brands',
			'first' => array(
				0 => '',
			)
		),
		'BRA_MFC_CODE'		=> 	'input',
		'BRA_BRAND'			=> 	'input',
		'BRA_MF_NR'			=> 	'input',
		
		'BRA_CONTENT'		=> 	'htmlarea',
		'BRA_IMG'			=> 	array(
			'type'	=> 'imageResize',
			'base_dir'		=> 'brands/',
			'images' => array(
				'brand-normal'	=>	'130x100',
			),
		),
		'BRA_ACTIVE'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno'], 'index' => 'BRA_ID'),
		
		'title'		  	=> 	'input',
		'kwords'	  	=> 	'input',
		'descr'			=> 	'input',
			
	),
	'generator' => array(
		#'disabled' => array('delete'),
		'fields' => array(
			'BRA_ID'		=>  'ID',
			'BRA_ID_GET' 	=>	$translates['admin.brand.connect'],
			'BRA_MFC_CODE'	=>  $translates['admin.brand.alias'],
			'BRA_BRAND'		=>  $translates['admin.brand.fname'],
			'BRA_MF_NR'		=>  'BRA_MF_NR',
			
			'BRA_CONTENT'		=> 	$translates['admin.details.descr'],
			'BRA_IMG'			=> 	$translates['admin.banners.img'],
			'BRA_ACTIVE'		=> 	'Выводить на сайте',
			
			'title'			=>  $translates['admin.main.seo_title'],
			'kwords'		=>  $translates['admin.main.seo_kwords'],
			'descr'			=>  $translates['admin.main.seo_descr'],
				
		),
		'list' => array(
			'fields' => array('BRA_ID','BRA_ID_GET','BRA_MFC_CODE','BRA_BRAND','BRA_ACTIVE'),
			'title'	 => $translates['admin.brand.name'],
			
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'BRA_ID_GET','BRA_BRAND','BRA_MFC_CODE',
					'BRA_CONTENT','BRA_IMG','BRA_ACTIVE',
					'title','kwords','descr'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'BRA_ID_GET','BRA_BRAND','BRA_MFC_CODE',
					'BRA_CONTENT','BRA_IMG','BRA_ACTIVE',
					'title','kwords','descr'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);