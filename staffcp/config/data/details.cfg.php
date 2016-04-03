<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['details'] = array(
	'table'         =>  DB_PREFIX.'details',
	'title'         =>  $translates['admin.details.name'],
	'fields'        =>  array(
		'ID'        	=>  'index',
		'IMPORT_ID'			=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'name',
			'cross_index'	=> 'id',
			'cross_table'	=> DB_PREFIX.'importers',
			'first'	=>	array(
				'0' => '',
			),
			'ordered' => 'name',
		),
		'BRAND_ID'			=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'BRA_BRAND',
			'ordered'		=> 'BRA_BRAND',
			'cross_index'	=> 'BRA_ID',
			'cross_table'	=> DB_PREFIX.'brands',
			'first'	=>	array(
				'0' => 'нет',
			),
			'ordered' => 'BRA_BRAND',
		),
		'BRAND_NAME'		=> 	'brand',
		'ARTICLE'			=> 	'input',
		#'PRICE'			=> 	'price',
		'PRICE'				=> 	'input',
		'DESCR'				=> 	array('type'=>'input','class'=>'see-data'),
		'BOX'				=> 	'input',
		'DELIVERY'			=> 	'input',
		'WEIGHT'			=> 	'input',
		'IMG_URL'			=> 	'input',
		
		'ONLY_FOR_SHOP'		=>	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'MIN'				=> 	'input',
		'ARTICLE_DEFAULT'	=> 	'input',
	),
	'generator' => array(
		#'disabled' => array('add','delete'),
		'fields' => array(
			'BRA_ID'		=>  'ID',
			'IMPORT_ID'		=>	$translates['admin.details.importer'],
			'BRAND_ID'		=>  $translates['admin.details.brandid'],
			'BRAND_NAME'	=> 	$translates['admin.details.brand'],
			'ARTICLE'		=>  $translates['admin.details.art'],
			'PRICE'			=>  $translates['admin.details.price'],
			'DESCR'			=> 	$translates['admin.details.descr'],
			'BOX'			=> 	$translates['admin.details.box'],
			'DELIVERY'		=> 	$translates['admin.details.delivery'],
			'WEIGHT'		=> 	'Вес',
			'IMG_URL'		=> 	'Адрес изображения',
			
			'ONLY_FOR_SHOP'	=>	'Использовать только для раздела магазин',
			'MIN'			=> 	'Кратность',
			'ARTICLE_DEFAULT'	=> 	'Исходный артикул',
		),
		'list' => array(
			'fields' => array('ID','IMPORT_ID','BRAND_NAME','ARTICLE','PRICE','DESCR','BOX','DELIVERY','WEIGHT','ONLY_FOR_SHOP','MIN','ARTICLE_DEFAULT'),
			'title'	 => $translates['admin.details.name'],		
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'IMPORT_ID','BRAND_ID','BRAND_NAME','ARTICLE','PRICE','DESCR','BOX','DELIVERY','WEIGHT','IMG_URL','ONLY_FOR_SHOP','MIN','ARTICLE_DEFAULT'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'IMPORT_ID','BRAND_ID','BRAND_NAME','ARTICLE','PRICE','DESCR','BOX','DELIVERY','WEIGHT','IMG_URL','ONLY_FOR_SHOP','MIN','ARTICLE_DEFAULT'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);