<?php
global $cmsGenerator;

$translates = Register::get('translates');

$cmsGenerator['deliveries'] = array(
	'table'         =>  DB_PREFIX.'deliveries',
	'title'         =>  $translates['admin.deliveries.name'],
	'fields'        =>  array(
		'id'            =>  'index',
		'name'		  	=> 	'input',
		'content'		=> 	array('type'=>'htmlarea','small'=>true),
		'price'		  	=> 	'input',
		'sort'			=> 	'input',
		'is_active'		=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'is_default'	=> 	array('type' => 'checkbox','label' => $translates['admin.main.yesno']),
		'img'		  	=> 	array(
			'type'	=> 'imageResize',
			// 'base_dir'		=> 'data/',
			'base_dir'		=> '../../static/images/data/deliveries/',
			'images' => array(
					'normal'	=>	'130x100',
			),
		),
		'view_if_price_from'	=> 	'input',
		'view_if_price_to'		=> 	'input',
		'free_from'		=> 	'input',
			
		/*'fk' => array(
			'type' => 'multiple',
			'foreign' => array(
				'table' => DB_PREFIX.'offices',
				'id' => 'id',
				'name' => 'name',
				'foreign_id' => 'id',
			),
			'link' => array(
				'table' => DB_PREFIX.'delivery2office',
				'source_id' => 'fk_delivery',
				'foreign_id' => 'fk_office',
			),
		),*/
	),
	'generator' => array(
		//'disabled' => array('add','delete'),
		//'required' => array('name'=>'exist','code'=>'exist'),
		'fields' => array(
			'id'            =>  'ID',
			'name'		  	=> 	$translates['admin.main.name'],
			'content'		=> 	$translates['admin.main.content'],
			'price'		  	=> 	$translates['admin.details.price'],
			'sort'		  	=> 	$translates['admin.main.sort'],
			'is_active'		=>  $translates['admin.main.view'],
			'is_default'	=> 	'Выбрать по умолчанию',
			
			'view_if_price_from'	=> 	'Выбрать по умолчанию если сумма покупки от (EUR)',
			'view_if_price_to'		=> 	'Выбрать по умолчанию если сумма покупки до (EUR)',
			'free_from'		=> 	'Бесплатная доставка от (EUR)',
			'img'		  	=> 	'Логотип',
			'fk' => 'Доставка для офиса (привязать только к офису)'
		),
		'list' => array(
			'fields' => array('id','name','price','sort','is_active','is_default','view_if_price_from','view_if_price_to','free_from','img'),
			'title'         =>  $translates['admin.deliveries.name'],
		), 
		'edit'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					// 'name','content','price','sort','is_active','is_default','view_if_price_from','view_if_price_to','free_from','fk'
					'name','content','price','sort','is_active','is_default','view_if_price_from','view_if_price_to','free_from','img'
				),
			),
			'title'	=> $translates['admin.main.editing'],
			'submit'=> $translates['admin.main.edit'],
		),
		'add'	=> array(
			'fields' => array(
				$translates['admin.main.dataname'] => array(
					// 'name','content','price','sort','is_active','is_default','view_if_price_from','view_if_price_to','free_from','fk'
					'name','content','price','sort','is_active','is_default','view_if_price_from','view_if_price_to','free_from','img'
				),
			),
			'title'	=> $translates['admin.main.adding'],
			'submit'=> $translates['admin.main.add'],
		),
	)	
);