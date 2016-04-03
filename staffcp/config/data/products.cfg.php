<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['products'] = array(
	'table'         =>  DB_PREFIX.'products',
	'title'         =>  $translates['admin.products.name'],
	'descr'         =>  'List of products',
	'fields'        =>  array(
		'id'            =>  'index',
		'tecdoc_id' 			=> 	'readonly_shop',
		'art_nr' 			=> 	'readonly_shop',
		'name_ru'		  	=> 	'input',
		'name_en'		  	=> 	'input',
		'name_de'		  	=> 	'input',
		'name_fr'		  	=> 	'input',
		'name_it'		  	=> 	'input',
		'name_gr'		  	=> 	'input',
		'name_no'		  	=> 	'input',
		'name_da'		  	=> 	'input',
		'name_es'		  	=> 	'input',
		'descr_ru'		  	=> 	'htmlarea',
		'descr_en'		  	=> 	'htmlarea',
		'descr_de'		  	=> 	'htmlarea',
		'descr_fr'		  	=> 	'htmlarea',
		'descr_it'		  	=> 	'htmlarea',
		'descr_gr'		  	=> 	'htmlarea',
		'descr_no'		  	=> 	'htmlarea',
		'descr_da'		  	=> 	'htmlarea',
		'descr_es'		  	=> 	'htmlarea',
		'img'		  	=> 	'input',
		'price'			=> 	'input',
		'price_angro'			=> 	'input',
		'price_uvp'			=> 	'input',
		'brand_name'	=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'BRA_BRAND',
			'cross_index'	=> 'BRA_BRAND',
			'cross_table'	=> DB_PREFIX.'brands',
			'first'	=>	array(
				'' => '',
			),
		),
		'brand'	=> 	array(
			'type'			=> 'category',
			'cross_name'	=> 'BRA_ID',
			'cross_index'	=> 'BRA_ID',
			'cross_table'	=> DB_PREFIX.'brands',
			'first'	=>	array(
				'' => '',
			),
		),
		'supplier_name'			=> 	'input',
		'supplier'			=> 	'input',
		'active' => array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
	),
	'generator' => array(
		'fields' => array(
			'id'            =>  'ID',
			'tecdoc_id' 			=> 	'TecDoc ID',
			'art_nr' 			=> 	'Article Nr',
			'name_ru'		  	=> 	'Name Russian',
			'name_en'		  	=> 	'Name English',
			'name_de'		  	=> 	'Name German',
			'name_fr'		  	=> 	'Name French',
			'name_it'		  	=> 	'Name Italian',
			'name_gr'		  	=> 	'Name Greek',
			'name_no'		  	=> 	'Name Norweign',
			'name_da'		  	=> 	'Name Danish',
			'name_es'		  	=> 	'Name Spanish',
			'descr_ru'		  	=> 	'Descr Russian',
			'descr_en'		  	=> 	'Descr English',
			'descr_de'		  	=> 	'Descr German',
			'descr_fr'		  	=> 	'Descr French',
			'descr_it'		  	=> 	'Descr Italian',
			'descr_gr'		  	=> 	'Descr Greek',
			'descr_no'		  	=> 	'Descr Norweign',
			'descr_da'		  	=> 	'Descr Danish',
			'descr_es'		  	=> 	'Descr Spanish',
			'img'		  	=> 	"Image",
			'price'			=> 	'Price',
			'price_angro'			=> 	'Price Angro',
			'price_uvp'			=> 	'Price UVP',
			'brand_name'	=> 	"Brand",
			'brand'	=> 	"Brand ID",
			'supplier_name'	=> 	"Supplier",
			'supplier'	=> 	"Supplier ID",
			'active' => "Active",
		),
		'list' => array(
			'fields' => array('id','tecdoc_id','art_nr','name_ru', 'supplier_name'),
			'title' => $translates['admin.products.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					// 'fk','name','article','content','is_product_view','price','currency','sort','set_index','set_isset','filter_id','url',
					// 'yandexmarket_model_id'
					'id', 'tecdoc_id', 'art_nr', 'name_ru', 'price', 'price_angro', 'price_uvp', 'supplier_name', 'active',
				),
				$translates['admin.products.img'] => array(
					'img'
				),
				'Title' => array(
					'name_en', 'name_de', 'name_fr', 'name_it', 'name_gr', 'name_no', 'name_da', 'name_es', 
				),
				'Description' => array(
					'descr_ru', 'descr_en', 'descr_de', 'descr_fr', 'descr_it', 'descr_gr', 'descr_no', 'descr_da', 'descr_es',
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					'id', 'tecdoc_id', 'art_nr', 'name_ru', 'price', 'price_angro', 'price_uvp', 'brand', 'supplier', 'active',
				),
				$translates['admin.products.img'] => array(
					'img'
				),
				'Description' => array(
					'descr_ru', 'descr_en', 'descr_de', 'descr_fr', 'descr_it', 'descr_gr', 'descr_no', 'descr_da', 'descr_es',
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);